<?php
// FILE: app/Http/Controllers/SuperAdmin/UserManagementController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Filter by role
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(20);

        // Get role statistics
        $stats = [
            'total' => User::count(),
            'superadmins' => User::where('role', 'superadmin')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'teachers' => User::where('role', 'teacher')->count(),
            'parents' => User::where('role', 'parent')->count(),
        ];

        return view('superadmin.users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        
        return view('superadmin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:superadmin,admin,teacher,parent',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle profile photo upload
        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'profile_photo' => $profilePhotoPath,
            'email_verified_at' => now(), // Auto-verify for admin-created accounts
        ]);

        // Assign role using Spatie
        $user->assignRole($request->role);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'created_user',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => "Created user: {$user->name} ({$user->role})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load('roles', 'permissions', 'activityLogs');

        // Get user statistics based on role
        $userStats = [];
        
        if ($user->isTeacher()) {
            $userStats = [
                'classes' => $user->teachingClasses()->count(),
                'students' => $user->teachingClasses()->withCount('enrollments')->get()->sum('enrollments_count'),
                'resources' => $user->uploadedResources()->count(),
            ];
        } elseif ($user->isParent()) {
            $userStats = [
                'children' => $user->children()->count(),
            ];
        }

        // Recent activity
        $recentActivity = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('superadmin.users.show', compact('user', 'userStats', 'recentActivity'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        // Prevent editing yourself (use profile instead)
        if ($user->id === auth()->id()) {
            return redirect()->route('superadmin.users.show', $user)
                ->with('warning', 'Use your profile page to edit your own account.');
        }

        $roles = Role::all();
        
        return view('superadmin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        // Prevent editing yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'You cannot edit your own account from here. Use your profile page.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => 'required|in:superadmin,admin,teacher,parent',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Prepare update data
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
        ];

        // Update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $updateData['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        // Update user
        $user->update($updateData);

        // Update role if changed
        if ($user->role !== $request->role) {
            $user->syncRoles([$request->role]);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated_user',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => "Updated user: {$user->name} ({$user->role})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting the last superadmin
        if ($user->role === 'superadmin') {
            $superadminCount = User::where('role', 'superadmin')->count();
            if ($superadminCount <= 1) {
                return redirect()->route('superadmin.users.index')
                    ->with('error', 'Cannot delete the last superadmin account.');
            }
        }

        $userName = $user->name;
        $userRole = $user->role;
        $userId = $user->id;

        // Delete profile photo if exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Delete user
        $user->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted_user',
            'model_type' => 'User',
            'model_id' => $userId,
            'description' => "Deleted user: {$userName} ({$userRole})",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update user role
        $user->update(['role' => $request->role]);
        $user->syncRoles([$request->role]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'assigned_role',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => "Assigned role '{$request->role}' to user: {$user->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully!'
        ]);
    }

    /**
     * Assign permissions to user
     */
    public function assignPermissions(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->syncPermissions($request->permissions);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'assigned_permissions',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => "Assigned " . count($request->permissions) . " permissions to user: {$user->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned successfully!'
        ]);
    }

    /**
     * Suspend/Activate user account
     */
    public function toggleStatus(User $user)
    {
        // Prevent suspending yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'You cannot suspend your own account.');
        }

        // Prevent suspending the last superadmin
        if ($user->role === 'superadmin') {
            $superadminCount = User::where('role', 'superadmin')->count();
            if ($superadminCount <= 1) {
                return redirect()->route('superadmin.users.index')
                    ->with('error', 'Cannot suspend the last superadmin account.');
            }
        }

        // Toggle email_verified_at as a way to suspend (or add a 'status' column)
        $newStatus = $user->email_verified_at ? null : now();
        $user->update(['email_verified_at' => $newStatus]);

        $status = $newStatus ? 'activated' : 'suspended';

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_status_changed',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => "User {$status}: {$user->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('superadmin.users.index')
            ->with('success', "User {$status} successfully!");
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role (exclude superadmin from admin view)
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        } else {
            // Admin cannot see superadmins
            $query->whereIn('role', ['admin', 'teacher', 'parent']);
        }

        // Search by name, email, or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get role statistics (excluding superadmin)
        $stats = [
            'total' => User::whereIn('role', ['admin', 'teacher', 'parent'])->count(),
            'admins' => User::where('role', 'admin')->count(),
            'teachers' => User::where('role', 'teacher')->count(),
            'parents' => User::where('role', 'parent')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,teacher,parent', // Cannot create superadmin
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now(); // Auto-verify admin-created accounts

        $user = User::create($validated);

        // Assign role using Spatie
        $user->assignRole($validated['role']);

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

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        // Admin cannot view superadmin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $user->load(['children', 'teachingClasses', 'activityLogs']);

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

        return view('admin.users.show', compact('user', 'userStats'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        // Admin cannot edit superadmin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Prevent editing yourself (use profile instead)
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.show', $user)
                ->with('warning', 'Use your profile page to edit your own account.');
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        // Admin cannot edit superadmin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Prevent editing yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot edit your own account from here. Use your profile page.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,teacher,parent', // Cannot assign superadmin
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        // Update role if changed
        if ($user->role !== $validated['role']) {
            $user->syncRoles([$validated['role']]);
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

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Admin cannot delete superadmin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        // Prevent deleting the last admin
        if ($user->isAdmin()) {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Cannot delete the last admin account!');
            }
        }

        // Check if user has dependent records
        if ($user->isParent() && $user->children()->count() > 0) {
            return back()->with('error', 'Cannot delete parent with active students!');
        }

        if ($user->isTeacher() && $user->teachingClasses()->count() > 0) {
            return back()->with('error', 'Cannot delete teacher with assigned classes!');
        }

        $userName = $user->name;
        $userRole = $user->role;
        $userId = $user->id;

        // Delete profile photo if exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

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

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }
}
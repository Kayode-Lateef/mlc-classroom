<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users
     * Admin can only see admin, teacher, and parent roles (NOT superadmin)
     */
    public function index(Request $request)
    {
        $query = User::query();

        // CRITICAL: Exclude superadmins from admin view
        $query->whereIn('role', ['admin', 'teacher', 'parent']);

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by verification status
        if ($request->filled('verified')) {
            if ($request->verified == '1') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->verified == '0') {
                $query->whereNull('email_verified_at');
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(20);

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
     * Admin can create: admin, teacher, parent (NOT superadmin)
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user
     * Admin can create: admin, teacher, parent (NOT superadmin)
     */
    public function store(Request $request)
    {
        // Custom validation messages
        $messages = [
            'name.required' => 'User name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'role.required' => 'Please select a user role.',
            'role.in' => 'Invalid user role selected.',
            'phone.regex' => 'Please enter a valid phone number (e.g., +44 1234 567890 or 07123456789).',
            'phone.max' => 'Phone number must not exceed 20 characters.',
            'status.required' => 'Please select account status.',
            'status.in' => 'Invalid status selected.',
            'profile_photo.image' => 'Profile photo must be an image file.',
            'profile_photo.mimes' => 'Profile photo must be a JPEG, PNG, JPG, or GIF file.',
            'profile_photo.max' => 'Profile photo must not exceed 2MB.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin,teacher,parent', // CANNOT create superadmin
            'phone' => ['nullable', 'regex:/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/', 'max:20'],
            'status' => 'required|in:active,inactive',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors below.');
        }

        // Prepare user data
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'status' => $request->status,
            'email_verified_at' => now(), // Auto-verify admin-created accounts
        ];

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $userData['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        // Create user
        $user = User::create($userData);

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
     * Admin cannot view superadmin users
     */
    public function show(User $user)
    {
        // CRITICAL: Admin cannot view superadmin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

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

        return view('admin.users.show', compact('user', 'userStats', 'recentActivity'));
    }

    /**
     * Show the form for editing the specified user
     * Admin cannot edit superadmin users
     */
    public function edit(User $user)
    {
        // CRITICAL: Admin cannot edit superadmin users
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
     * Admin cannot edit superadmin users
     */
    public function update(Request $request, User $user)
    {
        // CRITICAL: Admin cannot edit superadmin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Prevent editing yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot edit your own account from here. Use your profile page.');
        }

        // Custom validation messages
        $messages = [
            'name.required' => 'User name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'role.required' => 'Please select a user role.',
            'role.in' => 'Invalid user role selected.',
            'phone.regex' => 'Please enter a valid phone number (e.g., +44 1234 567890 or 07123456789).',
            'phone.max' => 'Phone number must not exceed 20 characters.',
            'status.required' => 'Please select account status.',
            'status.in' => 'Invalid status selected.',
            'profile_photo.image' => 'Profile photo must be an image file.',
            'profile_photo.mimes' => 'Profile photo must be a JPEG, PNG, JPG, or GIF file.',
            'profile_photo.max' => 'Profile photo must not exceed 2MB.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin,teacher,parent', // CANNOT assign superadmin
            'phone' => ['nullable', 'regex:/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/', 'max:20'],
            'status' => 'required|in:active,inactive',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors below.');
        }

        // Prepare update data
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'status' => $request->status,
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
     * Admin cannot delete superadmin users
     */
    public function destroy(User $user)
    {
        // CRITICAL: Admin cannot delete superadmin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting the last admin
        if ($user->isAdmin()) {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Cannot delete the last admin account.');
            }
        }

        // Check if user has dependent records
        if ($user->isParent() && $user->children()->count() > 0) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete parent with active students. Please reassign or remove students first.');
        }

        if ($user->isTeacher() && $user->teachingClasses()->count() > 0) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete teacher with assigned classes. Please reassign or remove classes first.');
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

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Toggle user status (Active/Inactive)
     * Admin cannot toggle superadmin status
     */
    public function toggleStatus(User $user)
    {
        // CRITICAL: Admin cannot toggle superadmin status
        if ($user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Prevent suspending yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot suspend your own account.');
        }

        // Prevent suspending the last admin
        if ($user->isAdmin()) {
            $activeAdminCount = User::where('role', 'admin')
                ->where('status', 'active')
                ->count();
            if ($activeAdminCount <= 1 && $user->status === 'active') {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Cannot suspend the last active admin account.');
            }
        }

        // Store old status
        $oldStatus = $user->status;

        // Toggle status (active <-> inactive)
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'toggled_user_status',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => "Changed user status from {$oldStatus} to {$newStatus}: {$user->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $message = $newStatus === 'active' 
            ? 'User activated successfully!' 
            : 'User deactivated successfully!';

        return redirect()->route('admin.users.index')
            ->with('success', $message);
    }
}
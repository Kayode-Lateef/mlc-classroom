<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;


class UserManagementController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

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
        return view('superadmin.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
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
            'role' => 'required|in:superadmin,admin,teacher,parent',
            'phone' => ['nullable', 'regex:/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/', 'max:20'],
            'status' => 'required|in:active,inactive',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below.');
        }

        // Prepare user data
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'status' => $request->status,
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

        // NOTIFY THE NEW USER
        $this->notifyNewUser($user, $request->password);

        // NOTIFY SUPERADMINS (if not creating a superadmin)
        if ($user->role !== 'superadmin') {
            $this->notifySuperAdminsAboutNewUser($user);
        }

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
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

        return view('superadmin.users.edit', compact('user'));
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
            'role' => 'required|in:superadmin,admin,teacher,parent',
            'phone' => ['nullable', 'regex:/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/', 'max:20'],
            'status' => 'required|in:active,inactive',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $messages);

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
     * Toggle user status (Active/Suspended)
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
            $superadminCount = User::where('role', 'superadmin')
                ->where('status', 'active')
                ->count();
            if ($superadminCount <= 1) {
                return redirect()->route('superadmin.users.index')
                    ->with('error', 'Cannot suspend the last active superadmin account.');
            }
        }

        // Store old status
        $oldStatus = $user->status;

        // Toggle status (active <-> suspended)
        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_status_changed',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => "User status changed to {$newStatus}: {$user->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // NOTIFY THE USER ABOUT STATUS CHANGE
        $this->notifyUserStatusChange($user, $oldStatus, $newStatus);

        // NOTIFY SUPERADMINS (except the one who made the change)
        $this->notifySuperAdminsAboutStatusChange($user, $oldStatus, $newStatus);

        return redirect()->route('superadmin.users.index')
            ->with('success', "User {$newStatus} successfully!");
    }


    /**
     *  HELPER: Notify new user with their credentials
     */
    private function notifyNewUser(User $user, $temporaryPassword)
    {
        try {
            $message = "Your account has been created successfully. ";
            $message .= "Role: " . ucfirst($user->role) . ". ";
            $message .= "Please login and change your password.";

            NotificationHelper::notifyUser(
                $user,
                'Account Created',
                $message,
                'general',
                [
                    'role' => $user->role,
                    'created_by' => auth()->user()->name,
                    'temporary_password' => $temporaryPassword,  // Include for reference
                    'url' => route('login')
                ]
            );

            \Log::info("New user notification sent to: {$user->email}");

        } catch (\Exception $e) {
            \Log::error("Failed to notify new user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * HELPER: Notify superadmins about new user creation
     */
    private function notifySuperAdminsAboutNewUser(User $user)
    {
        try {
            NotificationHelper::notifySuperAdmins(
                'New User Created',
                "New {$user->role} account created for {$user->name} by " . auth()->user()->name,
                'general',
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'user_role' => $user->role,
                    'created_by' => auth()->user()->name,
                    'url' => route('superadmin.users.show', $user->id)
                ]
            );

            \Log::info("SuperAdmins notified about new user: {$user->email}");

        } catch (\Exception $e) {
            \Log::error("Failed to notify superadmins about new user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * HELPER: Notify user about their account status change
     */
    private function notifyUserStatusChange(User $user, $oldStatus, $newStatus)
    {
        try {
            if ($newStatus === 'suspended') {
                $title = 'Account Suspended';
                $message = "Your account has been suspended by " . auth()->user()->name . ". ";
                $message .= "Please contact administration for more information.";
                $type = 'emergency';
            } else {
                $title = 'Account Activated';
                $message = "Your account has been reactivated by " . auth()->user()->name . ". ";
                $message .= "You can now access the system.";
                $type = 'general';
            }

            NotificationHelper::notifyUser(
                $user,
                $title,
                $message,
                $type,
                [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'changed_by' => auth()->user()->name,
                    'url' => route('login')
                ]
            );

            \Log::info("Status change notification sent to user: {$user->email}");

        } catch (\Exception $e) {
            \Log::error("Failed to notify user {$user->id} about status change: " . $e->getMessage());
        }
    }

    /**
     * HELPER: Notify superadmins about user status changes
     */
    private function notifySuperAdminsAboutStatusChange(User $user, $oldStatus, $newStatus)
    {
        try {
            // Don't notify the admin who made the change
            $otherSuperAdmins = User::where('role', 'superadmin')
                ->where('status', 'active')
                ->where('id', '!=', auth()->id())
                ->get();

            if ($otherSuperAdmins->isEmpty()) {
                return;
            }

            $action = $newStatus === 'suspended' ? 'suspended' : 'activated';
            $message = "{$user->name} ({$user->role}) has been {$action} by " . auth()->user()->name;

            foreach ($otherSuperAdmins as $admin) {
                NotificationHelper::notifyUser(
                    $admin,
                    "User Account " . ucfirst($action),
                    $message,
                    'general',
                    [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_role' => $user->role,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'changed_by' => auth()->user()->name,
                        'url' => route('superadmin.users.show', $user->id)
                    ]
                );
            }

            \Log::info("Other superadmins notified about status change for user: {$user->email}");

        } catch (\Exception $e) {
            \Log::error("Failed to notify superadmins about status change for user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * Assign role to user (AJAX endpoint for users index page)
     */
    public function assignRole(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:superadmin,admin,teacher,parent',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update user role (simple role column, no Spatie)
        $user->update(['role' => $request->role]);

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
     * Assign permissions to user (AJAX endpoint - if using Spatie permissions)
     * Note: This uses Spatie's permission system, separate from the simple role column
     */
    public function assignPermissions(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // This uses Spatie's permission system (separate from role column)
        // Only use if you have Spatie Permission package installed
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error assigning permissions: ' . $e->getMessage()
            ], 500);
        }
    }


}
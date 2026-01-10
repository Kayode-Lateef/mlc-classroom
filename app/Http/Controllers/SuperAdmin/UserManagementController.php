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
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Permission;


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
            'requires_verification.required' => 'Please select email verification option.',
            'requires_verification.boolean' => 'Invalid verification option selected.',
        ];

        // ✅ UPDATED: Added 'suspended' and 'banned' to status validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:superadmin,admin,teacher,parent',
            'phone' => ['nullable', 'regex:/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/', 'max:20'],
            'status' => 'required|in:active,inactive,suspended,banned', // ✅ SYNCED WITH DATABASE
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'requires_verification' => 'required|boolean', // ✅ NEW: Email verification option
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

        // ✅ NEW: Handle email verification option
        if (!$request->requires_verification) {
            // Auto-verify if superadmin chooses immediate access
            $userData['email_verified_at'] = now();
        }
        // If requires_verification = true, email_verified_at stays NULL

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

        // ✅ NEW: Send appropriate notification based on verification requirement
        if ($request->requires_verification) {
            // Send verification email
            event(new Registered($user));
            $this->notifyNewUserWithVerification($user);
        } else {
            // Send welcome email with credentials (immediate access)
            $this->notifyNewUserWithCredentials($user, $request->password);
        }

        // NOTIFY SUPERADMINS (if not creating a superadmin)
        if ($user->role !== 'superadmin') {
            $this->notifySuperAdminsAboutNewUser($user);
        }

        $successMessage = $request->requires_verification 
            ? 'User created successfully! Verification email has been sent.'
            : 'User created successfully! Welcome email with credentials has been sent.';

        return redirect()->route('superadmin.users.index')
            ->with('success', $successMessage);
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

        // ✅ NEW: Get user's direct permissions (for granular admin permissions)
        $userPermissions = $user->permissions()->pluck('name')->toArray();
        $allPermissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            return explode('.', $permission->name)[0] ?? 'general';
        });

        return view('superadmin.users.show', compact('user', 'userStats', 'recentActivity', 'userPermissions', 'allPermissions'));
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

        // ✅ NEW: Get user's permissions for the edit form
        $userPermissions = $user->permissions()->pluck('id')->toArray();
        $allPermissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            return explode('.', $permission->name)[0] ?? 'general';
        });

        return view('superadmin.users.edit', compact('user', 'userPermissions', 'allPermissions'));
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

        // ✅ UPDATED: Added 'suspended' and 'banned' to status validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => 'required|in:superadmin,admin,teacher,parent',
            'phone' => ['nullable', 'regex:/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/', 'max:20'],
            'status' => 'required|in:active,inactive,suspended,banned', // ✅ SYNCED WITH DATABASE
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'permissions' => 'nullable|array', // ✅ NEW: Permission assignment
            'permissions.*' => 'exists:permissions,id',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // ✅ TRACK ROLE CHANGE for notification
        $oldRole = $user->role;
        $roleChanged = ($oldRole !== $request->role);

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

        // ✅ NEW: Sync permissions (for granular admin control)
        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->pluck('name');
            $user->syncPermissions($permissions);
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

        // ✅ NEW: Notify user if role changed
        if ($roleChanged) {
            $this->notifyUserRoleChange($user, $oldRole, $request->role);
        }

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
     * ✅ NEW: Resend email verification link
     */
    public function resendVerification(User $user)
    {
        if ($user->hasVerifiedEmail()) {
            return redirect()->back()
                ->with('warning', 'This user has already verified their email address.');
        }

        $user->sendEmailVerificationNotification();

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'resent_verification',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => "Resent email verification to: {$user->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()
            ->with('success', 'Verification email resent successfully!');
    }

    /**
     * ✅ NEW: Manually verify user email (bypass verification)
     */
    public function manualVerify(User $user)
    {
        if ($user->hasVerifiedEmail()) {
            return redirect()->back()
                ->with('warning', 'This user has already verified their email address.');
        }

        $user->markEmailAsVerified();

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'manually_verified',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => "Manually verified email for: {$user->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Notify user
        try {
            NotificationHelper::notifyUser(
                $user,
                'Email Verified',
                'Your email address has been verified by an administrator. You now have full access to the system.',
                'general',
                [
                    'verified_by' => auth()->user()->name,
                    'url' => route('login')
                ]
            );
        } catch (\Exception $e) {
            \Log::error("Failed to notify user about manual verification: " . $e->getMessage());
        }

        return redirect()->back()
            ->with('success', 'Email verified successfully!');
    }


    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * ✅ NEW: Notify new user with verification email
     */
    private function notifyNewUserWithVerification(User $user)
    {
        try {
            $message = "Your account has been created. Please verify your email address to access the system. ";
            $message .= "Role: " . ucfirst($user->role) . ". ";
            $message .= "Check your email for the verification link.";

            NotificationHelper::notifyUser(
                $user,
                'Account Created - Verify Email',
                $message,
                'general',
                [
                    'role' => $user->role,
                    'created_by' => auth()->user()->name,
                    'requires_verification' => true,
                    'url' => route('login')
                ]
            );

            \Log::info("Verification email sent to: {$user->email}");

        } catch (\Exception $e) {
            \Log::error("Failed to send verification email to {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * ✅ UPDATED: Notify new user with their credentials (immediate access)
     */
    private function notifyNewUserWithCredentials(User $user, $temporaryPassword)
    {
        try {
            $message = "Your account has been created with immediate access. ";
            $message .= "Role: " . ucfirst($user->role) . ". ";
            $message .= "Please login and change your password.";

            NotificationHelper::notifyUser(
                $user,
                'Account Created - Immediate Access',
                $message,
                'general',
                [
                    'role' => $user->role,
                    'created_by' => auth()->user()->name,
                    'temporary_password' => $temporaryPassword,
                    'requires_verification' => false,
                    'url' => route('login')
                ]
            );

            \Log::info("Welcome email sent to: {$user->email}");

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
     * ✅ NEW: Notify user about role change
     */
    private function notifyUserRoleChange(User $user, $oldRole, $newRole)
    {
        try {
            $message = "Your role has been changed from " . ucfirst($oldRole) . " to " . ucfirst($newRole) . " by " . auth()->user()->name . ". ";
            $message .= "Your permissions and access levels may have changed. Please log in to review your new role.";

            NotificationHelper::notifyUser(
                $user,
                'Role Changed',
                $message,
                'general',
                [
                    'old_role' => $oldRole,
                    'new_role' => $newRole,
                    'changed_by' => auth()->user()->name,
                    'url' => route('login')
                ]
            );

            \Log::info("Role change notification sent to user: {$user->email}");

        } catch (\Exception $e) {
            \Log::error("Failed to notify user {$user->id} about role change: " . $e->getMessage());
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

        // Track old role for notification
        $oldRole = $user->role;

        // Update user role
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

        // Notify user about role change
        $this->notifyUserRoleChange($user, $oldRole, $request->role);

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully!'
        ]);
    }

    /**
     * Assign permissions to user (AJAX endpoint - Spatie permissions)
     */
    public function assignPermissions(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $permissions = Permission::whereIn('id', $request->permissions)->pluck('name');
            $user->syncPermissions($permissions);
            
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
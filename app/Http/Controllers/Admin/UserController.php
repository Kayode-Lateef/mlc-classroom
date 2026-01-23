<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Registered;



class UserController extends Controller
{
    /**
     * Display a listing of users
     * Admin can only see admin, teacher, and parent roles (NOT superadmin)
     */
    public function index(Request $request)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('view users')) {
            abort(403, 'You do not have permission to view users.');
        }

        try {
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

            // ✅ ENHANCED: Search by name, email, or phone with better matching
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // ✅ ENHANCED: Validate sort columns to prevent SQL injection
            $allowedSortColumns = ['name', 'email', 'role', 'status', 'created_at'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'created_at';
            }
            
            $query->orderBy($sortBy, $sortOrder);

            $users = $query->paginate(config('app.pagination.users', 20));

            // ✅ ENHANCED: Get comprehensive role statistics (excluding superadmin)
            $stats = [
                'total' => User::whereIn('role', ['admin', 'teacher', 'parent'])->count(),
                'admins' => User::where('role', 'admin')->count(),
                'teachers' => User::where('role', 'teacher')->count(),
                'parents' => User::where('role', 'parent')->count(),
                'active' => User::whereIn('role', ['admin', 'teacher', 'parent'])
                    ->where('status', 'active')
                    ->count(),
                'inactive' => User::whereIn('role', ['admin', 'teacher', 'parent'])
                    ->where('status', 'inactive')
                    ->count(),
                'verified' => User::whereIn('role', ['admin', 'teacher', 'parent'])
                    ->whereNotNull('email_verified_at')
                    ->count(),
                'unverified' => User::whereIn('role', ['admin', 'teacher', 'parent'])
                    ->whereNull('email_verified_at')
                    ->count(),
            ];

            return view('admin.users.index', compact('users', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error loading users list: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An error occurred while loading users. Please try again.');
        }
    }

    /**
     * Show the form for creating a new user
     * Admin can create: admin, teacher, parent (NOT superadmin)
     */
    public function create()
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('create users')) {
            abort(403, 'You do not have permission to create users.');
        }

        return view('admin.users.create');
    }

   /**
     * Store a newly created user
     * Admin can create: admin, teacher, parent (NOT superadmin)
     */
    public function store(Request $request)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('create users')) {
            abort(403, 'You do not have permission to create users.');
        }

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

        // ✅ UPDATED: Added 'suspended', 'banned' and email verification
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin,teacher,parent', // CANNOT create superadmin
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

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();

        try {
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
                // Auto-verify if admin chooses immediate access
                $userData['email_verified_at'] = now();
            }
            // If requires_verification = true, email_verified_at stays NULL

            // ✅ ENHANCED: Handle profile photo upload with error handling
            if ($request->hasFile('profile_photo')) {
                try {
                    $userData['profile_photo'] = $request->file('profile_photo')
                        ->store('profile_photos', 'public');
                } catch (\Exception $e) {
                    Log::error('Profile photo upload failed: ' . $e->getMessage());
                    return back()
                        ->withInput()
                        ->with('error', 'Failed to upload profile photo. Please try again.');
                }
            }

            // Create user
            $user = User::create($userData);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created_user',
                'model_type' => 'User',
                'model_id' => $user->id,
                'description' => "Created user: {$user->name} ({$user->role}) - Status: {$user->status}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // ✅ NEW: Send appropriate notification based on verification requirement
            try {
                if ($request->requires_verification) {
                    // Send verification email
                    event(new Registered($user));
                    $this->notifyNewUserWithVerification($user);
                } else {
                    // Send welcome email with credentials (immediate access)
                    $this->notifyNewUserWithCredentials($user, $request->password);
                }

                // ✅ ADDED: Notify other admins and superadmins of new user creation
                NotificationHelper::notifyAdmins(
                    'New User Created',
                    "A new {$user->role} account has been created: {$user->name} ({$user->email})",
                    'user_created',
                    [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'user_role' => $user->role,
                        'created_by' => auth()->user()->name,
                        'url' => route('admin.users.show', $user->id)
                    ],
                    auth()->id() // Exclude current admin
                );

                Log::info("User creation notifications sent for {$user->email}");

            } catch (\Exception $e) {
                Log::error('Failed to send user creation notifications: ' . $e->getMessage());
                // Don't fail the request if notifications fail
            }

            $successMessage = $request->requires_verification 
                ? 'User created successfully! Verification email has been sent.'
                : 'User created successfully! Welcome email with credentials has been sent.';

            return redirect()->route('admin.users.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded file if it exists
            if (isset($userData['profile_photo'])) {
                Storage::disk('public')->delete($userData['profile_photo']);
            }
            
            Log::error('Error creating user: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            return back()
                ->withErrors(['error' => 'Failed to create user. Please try again.'])
                ->withInput();
        }
    }


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
     * ✅ NEW: Notify new user with their credentials (immediate access)
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
     * Display the specified user
     * Admin cannot view superadmin users
     */
    public function show(User $user)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('view users')) {
            abort(403, 'You do not have permission to view user details.');
        }

        // CRITICAL: Admin cannot view superadmin users
        if ($user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        try {
            // ✅ SIMPLIFIED: Match SuperAdmin's working approach
            $userStats = [];
            
            if ($user->isTeacher()) {
                $userStats = [
                    'classes' => $user->teachingClasses()->count(),
                    'students' => $user->teachingClasses()
                        ->withCount('enrollments')
                        ->get()
                        ->sum('enrollments_count'),
                    'resources' => $user->uploadedResources()->count(),
                ];
            } elseif ($user->isParent()) {
                $userStats = [
                    'children' => $user->children()->count(),
                ];
            } elseif ($user->isAdmin()) {
                $userStats = [
                    'actions_count' => ActivityLog::where('user_id', $user->id)->count(),
                ];
            }

            // Recent activity
            $recentActivity = ActivityLog::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get();

            // Account age and last activity
            $accountAge = $user->created_at->diffForHumans();
            $lastActivity = ActivityLog::where('user_id', $user->id)
                ->latest('created_at')
                ->first();

            return view('admin.users.show', compact(
                'user',
                'userStats',
                'recentActivity',
                'accountAge',
                'lastActivity'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading user details: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->with('error', 'An error occurred while loading user details.');
        }
    }

    /**
     * Show the form for editing the specified user
     * Admin cannot edit superadmin users
     */
    public function edit(User $user)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('edit users')) {
            abort(403, 'You do not have permission to edit users.');
        }

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
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('edit users')) {
            abort(403, 'You do not have permission to edit users.');
        }

        // CRITICAL: Admin cannot edit superadmin users
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
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin,teacher,parent', // CANNOT assign superadmin
            'phone' => ['nullable', 'regex:/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/', 'max:20'],
            'status' => 'required|in:active,inactive',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => 'User name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'role.required' => 'Please select a user role.',
            'role.in' => 'Invalid user role selected.',
            'phone.regex' => 'Please enter a valid phone number.',
            'phone.max' => 'Phone number must not exceed 20 characters.',
            'status.required' => 'Please select account status.',
            'status.in' => 'Invalid status selected.',
            'profile_photo.image' => 'Profile photo must be an image file.',
            'profile_photo.mimes' => 'Profile photo must be a JPEG, PNG, JPG, or GIF file.',
            'profile_photo.max' => 'Profile photo must not exceed 2MB.',
        ]);

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();

        try {
            // ✅ ENHANCED: Track changes for notifications
            $changes = [];
            $oldRole = $user->role;
            $oldStatus = $user->status;
            $oldEmail = $user->email;

            if ($validated['name'] != $user->name) {
                $changes[] = "name changed from '{$user->name}' to '{$validated['name']}'";
            }
            if ($validated['email'] != $user->email) {
                $changes[] = "email changed from '{$user->email}' to '{$validated['email']}'";
            }
            if ($validated['role'] != $user->role) {
                $changes[] = "role changed from '{$user->role}' to '{$validated['role']}'";
            }
            if ($validated['status'] != $user->status) {
                $changes[] = "status changed from '{$user->status}' to '{$validated['status']}'";
            }
            if ($validated['phone'] != $user->phone) {
                $changes[] = "phone number updated";
            }
            if ($request->filled('password')) {
                $changes[] = "password reset";
            }

            // Prepare update data
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'phone' => $validated['phone'],
                'status' => $validated['status'],
            ];

            // Update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                try {
                    // Delete old photo if exists
                    if ($user->profile_photo) {
                        Storage::disk('public')->delete($user->profile_photo);
                    }
                    $updateData['profile_photo'] = $request->file('profile_photo')
                        ->store('profile_photos', 'public');
                    $changes[] = "profile photo updated";
                } catch (\Exception $e) {
                    Log::error('Profile photo upload failed: ' . $e->getMessage());
                    throw new \Exception('Failed to upload profile photo.');
                }
            }

            // Update user
            $user->update($updateData);

            // ✅ ENHANCED: Activity log with detailed changes
            $changeDescription = !empty($changes) ? ' (' . implode(', ', $changes) . ')' : '';
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated_user',
                'model_type' => 'User',
                'model_id' => $user->id,
                'description' => "Updated user: {$user->name} ({$user->role}){$changeDescription}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // ✅ ADDED: Send notifications for significant changes
            try {
                // Notify user if their account was modified
                if (!empty($changes)) {
                    NotificationHelper::notifyUser(
                        $user,
                        'Account Updated',
                        "Your account has been updated by an administrator. Changes: " . 
                        implode(', ', $changes),
                        'account_updated',
                        [
                            'changes' => $changes,
                            'updated_by' => auth()->user()->name
                        ]
                    );
                }

                // Notify user if role changed
                if ($validated['role'] != $oldRole) {
                    NotificationHelper::notifyUser(
                        $user,
                        'Role Changed',
                        "Your role has been changed from {$oldRole} to {$validated['role']}",
                        'role_changed',
                        [
                            'old_role' => $oldRole,
                            'new_role' => $validated['role'],
                            'changed_by' => auth()->user()->name
                        ]
                    );
                }

                // Notify user if status changed to inactive
                if ($validated['status'] === 'inactive' && $oldStatus === 'active') {
                    NotificationHelper::notifyUser(
                        $user,
                        'Account Deactivated',
                        "Your account has been deactivated. Please contact administration if you believe this is an error.",
                        'account_deactivated',
                        [
                            'deactivated_by' => auth()->user()->name,
                            'urgent' => true
                        ]
                    );
                }

                // Notify user if status changed to active
                if ($validated['status'] === 'active' && $oldStatus === 'inactive') {
                    NotificationHelper::notifyUser(
                        $user,
                        'Account Activated',
                        "Your account has been reactivated. You can now access the system.",
                        'account_activated',
                        [
                            'activated_by' => auth()->user()->name,
                            'login_url' => route('login')
                        ]
                    );
                }

                // Notify user if password was reset
                if ($request->filled('password')) {
                    NotificationHelper::notifyUser(
                        $user,
                        'Password Reset',
                        "Your password has been reset by an administrator. Please login with your new credentials.",
                        'password_reset',
                        [
                            'email' => $user->email,
                            'reset_by' => auth()->user()->name,
                            'login_url' => route('login')
                        ]
                    );
                }

                // Notify admins if significant changes occurred
                if (count($changes) >= 3 || in_array($validated['role'], ['admin']) && $oldRole !== 'admin') {
                    NotificationHelper::notifyAdmins(
                        'User Account Modified',
                        "Significant changes made to {$user->name}'s account: " . implode(', ', $changes),
                        'user_modified',
                        [
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                            'changes' => $changes,
                            'modified_by' => auth()->user()->name,
                            'url' => route('admin.users.show', $user->id)
                        ],
                        auth()->id()
                    );
                }

                Log::info("User update notifications sent for {$user->email}");

            } catch (\Exception $e) {
                Log::error('Failed to send user update notifications: ' . $e->getMessage());
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully!' . 
                    (!empty($changes) ? ' Notifications sent to user.' : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating user: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id
            ]);
            
            return back()
                ->withErrors(['error' => 'Failed to update user. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified user
     * Admin cannot delete superadmin users
     */
    public function destroy(User $user)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('delete users')) {
            abort(403, 'You do not have permission to delete users.');
        }

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

        // ✅ ENHANCED: Check for dependent records with detailed feedback
        $blockingReasons = [];

        if ($user->isParent()) {
            $childrenCount = $user->children()->count();
            if ($childrenCount > 0) {
                $blockingReasons[] = "{$childrenCount} student(s) enrolled under this parent";
            }
        }

        if ($user->isTeacher()) {
            $classesCount = $user->teachingClasses()->count();
            if ($classesCount > 0) {
                $blockingReasons[] = "{$classesCount} class(es) assigned to this teacher";
            }
            
            $resourcesCount = $user->uploadedResources()->count();
            if ($resourcesCount > 0) {
                $blockingReasons[] = "{$resourcesCount} learning resource(s) created by this teacher";
            }
        }

        if (!empty($blockingReasons)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete user account. Blocking reasons: ' . 
                    implode('; ', $blockingReasons) . '. Please reassign or remove these records first.');
        }

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();

        try {
            $userName = $user->name;
            $userEmail = $user->email;
            $userRole = $user->role;
            $userId = $user->id;

            // Delete profile photo if exists
            if ($user->profile_photo) {
                try {
                    Storage::disk('public')->delete($user->profile_photo);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete profile photo: ' . $e->getMessage());
                }
            }

            // Delete user
            $user->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'deleted_user',
                'model_type' => 'User',
                'model_id' => $userId,
                'description' => "Deleted user: {$userName} ({$userRole}) - Email: {$userEmail}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            // ✅ ENHANCED: Notify both SuperAdmins AND Admins of user deletion (WITH EMAIL)
            try {
                // Notify SuperAdmins (they should know about all deletions)
                NotificationHelper::notifySuperAdmins(
                    'User Account Deleted by Admin',
                    "User account deleted: {$userName} ({$userRole}) by Admin " . auth()->user()->name,
                    'user_deleted',
                    [
                        'user_name' => $userName,
                        'user_email' => $userEmail,
                        'user_role' => $userRole,
                        'deleted_by' => auth()->user()->name,
                        'deleted_by_role' => 'Admin',
                        'deleted_at' => now()->format('d M Y, H:i'),
                    ]
                );

                // Notify other Admins (excluding current admin)
                $otherAdmins = User::where('role', 'admin')
                    ->where('status', 'active')
                    ->where('id', '!=', auth()->id())
                    ->get();

                foreach ($otherAdmins as $admin) {
                    NotificationHelper::notifyUser(
                        $admin,
                        'User Account Deleted',
                        "User account deleted: {$userName} ({$userRole}) by " . auth()->user()->name,
                        'user_deleted',
                        [
                            'user_name' => $userName,
                            'user_email' => $userEmail,
                            'user_role' => $userRole,
                            'deleted_by' => auth()->user()->name,
                            'deleted_at' => now()->format('d M Y, H:i'),
                        ],
                        true  // ✅ Send email immediately
                    );
                }

                \Log::info("User deletion notifications sent for {$userEmail}");

            } catch (\Exception $e) {
                \Log::error('Failed to send user deletion notification: ' . $e->getMessage());
            }

            return redirect()->route('admin.users.index')
                ->with('success', "User '{$userName}' deleted successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting user: ' . $e->getMessage(), [
                'user_id' => $user->id
            ]);
            
            return back()->with('error', 'Failed to delete user. Please try again.');
        }
    }

    /**
     * Toggle user status (Active/Inactive)
     * Admin cannot toggle superadmin status
     */
    public function toggleStatus(User $user)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('edit users')) {
            abort(403, 'You do not have permission to change user status.');
        }

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

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();

        try {
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
                'description' => "Changed {$user->name}'s status from {$oldStatus} to {$newStatus}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            // ✅ ADDED: Send notifications
            try {
                if ($newStatus === 'inactive') {
                    // Notify user of deactivation
                    NotificationHelper::notifyUser(
                        $user,
                        'Account Deactivated',
                        "Your account has been deactivated. Please contact administration if you believe this is an error.",
                        'account_deactivated',
                        [
                            'deactivated_by' => auth()->user()->name,
                            'urgent' => true
                        ]
                    );
                } else {
                    // Notify user of activation
                    NotificationHelper::notifyUser(
                        $user,
                        'Account Activated',
                        "Your account has been reactivated. You can now access the system.",
                        'account_activated',
                        [
                            'activated_by' => auth()->user()->name,
                            'login_url' => route('login')
                        ]
                    );
                }

                Log::info("Status toggle notification sent to {$user->email}");

            } catch (\Exception $e) {
                Log::error('Failed to send status toggle notification: ' . $e->getMessage());
            }

            $message = $newStatus === 'active' 
                ? "User '{$user->name}' activated successfully!" 
                : "User '{$user->name}' deactivated successfully!";

            return redirect()->route('admin.users.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error toggling user status: ' . $e->getMessage(), [
                'user_id' => $user->id
            ]);
            
            return back()->with('error', 'Failed to change user status. Please try again.');
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NotificationSetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    /**
     * Display admin settings page
     */
    public function index()
    {
        $admin = auth()->user();
        
        // Get notification settings (default to all enabled if not set)
        $notificationTypes = ['absence', 'homework_assigned', 'homework_graded', 'progress_report', 'schedule_change', 'emergency'];
        
        $notificationSettings = [];
        foreach ($notificationTypes as $type) {
            $setting = NotificationSetting::where('user_id', $admin->id)
                ->where('notification_type', $type)
                ->first();
                
            $notificationSettings[$type] = $setting ?? (object)[
                'notification_type' => $type,
                'email_enabled' => true,
                'sms_enabled' => true,
                'in_app_enabled' => true,
            ];
        }

        return view('admin.settings.index', compact('admin', 'notificationSettings'));
    }

    /**
     * Update admin settings
     */
    public function update(Request $request)
    {
        $admin = auth()->user();
        
        // Determine which section is being updated
        $section = $request->input('section', 'profile');

        switch ($section) {
            case 'profile':
                return $this->updateProfile($request, $admin);
            case 'password':
                return $this->updatePassword($request, $admin);
            case 'notifications':
                return $this->updateNotifications($request, $admin);
            default:
                return redirect()->back()->with('error', 'Invalid settings section.');
        }
    }

    /**
     * Update profile information
     */
    protected function updateProfile(Request $request, User $admin)
    {
        // Custom validation messages
        $messages = [
            'name.required' => 'Please enter your full name.',
            'name.max' => 'Name must not exceed 255 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'phone.regex' => 'Please provide a valid UK phone number (e.g., +44 7123 456789 or 07123 456789).',
            'profile_photo.image' => 'Profile photo must be an image file.',
            'profile_photo.mimes' => 'Profile photo must be a JPEG, PNG, JPG, or GIF file.',
            'profile_photo.max' => 'Profile photo must not exceed 2MB.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $admin->id,
            'phone' => ['nullable', 'string', 'min:10', 'max:20', 'regex:/^(\+44\s?|0)[0-9\s\-\(\)]{9,}$/'],
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors below.');
        }

        try {
            // Update basic info
            $admin->name = $request->name;
            $admin->email = $request->email;
            $admin->phone = $request->phone;

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                
                // Validate file size
                if ($file->getSize() > 2097152) { // 2MB in bytes
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Profile photo must not exceed 2MB.');
                }

                // Delete old photo if exists
                if ($admin->profile_photo && Storage::disk('public')->exists($admin->profile_photo)) {
                    Storage::disk('public')->delete($admin->profile_photo);
                }

                // Generate unique filename
                $filename = 'profile_' . $admin->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Store photo
                $path = $file->storeAs('profile-photos', $filename, 'public');
                
                $admin->profile_photo = $path;
            }

            $admin->save();

            // Log activity
            ActivityLog::create([
                'user_id' => $admin->id,
                'action' => 'updated_profile',
                'model_type' => 'User',
                'model_id' => $admin->id,
                'description' => 'Updated profile information',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.settings.index')
                ->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Profile update failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update profile. Please try again.');
        }
    }

    /**
     * Update password
     */
    protected function updatePassword(Request $request, User $admin)
    {
        // Custom validation messages
        $messages = [
            'current_password.required' => 'Please enter your current password.',
            'password.required' => 'Please enter a new password.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()],
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Please fix the validation errors below.');
        }

        try {
            // Verify current password
            if (!Hash::check($request->current_password, $admin->password)) {
                return redirect()->back()
                    ->with('error', 'Current password is incorrect.');
            }

            // Check if new password is same as current
            if (Hash::check($request->password, $admin->password)) {
                return redirect()->back()
                    ->with('error', 'New password must be different from current password.');
            }

            // Update password
            $admin->password = Hash::make($request->password);
            $admin->save();

            // Log activity
            ActivityLog::create([
                'user_id' => $admin->id,
                'action' => 'changed_password',
                'model_type' => 'User',
                'model_id' => $admin->id,
                'description' => 'Changed account password',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.settings.index')
                ->with('success', 'Password changed successfully!');

        } catch (\Exception $e) {
            \Log::error('Password change failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to change password. Please try again.');
        }
    }

    /**
     * Update notification preferences
     */
    protected function updateNotifications(Request $request, User $admin)
    {
        // Custom validation messages
        $messages = [
            'notifications.required' => 'Please select at least one notification type.',
            'notifications.array' => 'Invalid notification settings format.',
        ];

        $validator = Validator::make($request->all(), [
            'notifications' => 'required|array',
            'notifications.*.email_enabled' => 'nullable|boolean',
            'notifications.*.sms_enabled' => 'nullable|boolean',
            'notifications.*.in_app_enabled' => 'nullable|boolean',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Please fix the validation errors below.');
        }

        try {
            $updatedCount = 0;

            foreach ($request->notifications as $type => $settings) {
                NotificationSetting::updateOrCreate(
                    [
                        'user_id' => $admin->id,
                        'notification_type' => $type,
                    ],
                    [
                        'email_enabled' => isset($settings['email_enabled']) ? 1 : 0,
                        'sms_enabled' => isset($settings['sms_enabled']) ? 1 : 0,
                        'in_app_enabled' => isset($settings['in_app_enabled']) ? 1 : 0,
                    ]
                );
                
                $updatedCount++;
            }

            // Log activity
            ActivityLog::create([
                'user_id' => $admin->id,
                'action' => 'updated_notification_settings',
                'model_type' => 'NotificationSetting',
                'model_id' => null,
                'description' => "Updated {$updatedCount} notification preferences",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.settings.index')
                ->with('success', 'Notification preferences updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Notification settings update failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update notification preferences. Please try again.');
        }
    }
}
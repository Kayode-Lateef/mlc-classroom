<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NotificationSetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display admin settings page
     */
    public function index()
    {
        $admin = auth()->user();
        
        // Get notification settings (default to all enabled if not set)
        $notificationTypes = [
            'absence', 
            'homework_assigned', 
            'homework_graded', 
            'progress_report', 
            'schedule_change', 
            'emergency'
        ];
        
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
        $section = $request->input('section', 'notifications');

        switch ($section) {
            case 'notifications':
                return $this->updateNotifications($request, $admin);
            default:
                return redirect()->back()->with('error', 'Invalid settings section.');
        }
    }

    /**
     * Update notification preferences
     */
    protected function updateNotifications(Request $request, User $admin)
    {
        // Custom validation messages
        $messages = [
            'notifications.required' => 'Please provide notification settings.',
            'notifications.array' => 'Invalid notification settings format.',
        ];

        // Validate that notifications array exists
        $validator = Validator::make($request->all(), [
            'notifications' => 'required|array',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Please fix the validation errors below.');
        }

        try {
            $updatedCount = 0;
            
            // Define all possible notification types
            $notificationTypes = [
                'absence', 
                'homework_assigned', 
                'homework_graded', 
                'progress_report', 
                'schedule_change', 
                'emergency'
            ];

            // Process each notification type
            foreach ($notificationTypes as $type) {
                // Check if this type exists in the submitted data
                $settings = $request->input("notifications.{$type}", []);
                
                // For checkboxes: if key exists, it's checked (true), if not exists, it's unchecked (false)
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
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Failed to update notification preferences. Please try again.');
        }
    }
}
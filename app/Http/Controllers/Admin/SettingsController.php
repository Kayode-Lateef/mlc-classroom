<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NotificationSetting;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display admin settings page
     */
    public function index()
    {
        $admin = auth()->user();

        // FIX #1: Single query + collection keying (replaces 6 individual queries)
        // FIX #2: Uses NotificationSetting::TYPES constant (replaces duplicated array)
        $notificationSettings = NotificationSetting::getForUser($admin->id);

        // FIX #5: Provide system-level context so admin knows global SMS/email status
        $systemInfo = [
            'school_name'   => SystemSetting::get('school_name', 'Maidstone Learning Centre'),
            'email_enabled' => SystemSetting::isEmailEnabled(),
            'sms_enabled'   => SystemSetting::isSmsEnabled(),
        ];

        // Channel summary stats (aggregated across all types, not just 'absence')
        $channelStats = $this->getChannelStats($notificationSettings);

        return view('admin.settings.index', compact(
            'admin',
            'notificationSettings',
            'systemInfo',
            'channelStats'
        ));
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
            'notifications.array'    => 'Invalid notification settings format.',
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
            // FIX #3: Wrap all updates in a transaction — all succeed or none do
            DB::beginTransaction();

            $actualChanges = 0;

            // FIX #2: Uses NotificationSetting::TYPES constant (single source of truth)
            foreach (NotificationSetting::TYPES as $type) {
                // Check if this type exists in the submitted data
                $settings = $request->input("notifications.{$type}", []);

                // For checkboxes: if key exists, it's checked (true), if not exists, it's unchecked (false)
                $newValues = [
                    'email_enabled'  => isset($settings['email_enabled']) ? 1 : 0,
                    'sms_enabled'    => isset($settings['sms_enabled']) ? 1 : 0,
                    'in_app_enabled' => isset($settings['in_app_enabled']) ? 1 : 0,
                ];

                // FIX #4: Only count actual changes (not every loop iteration)
                $existing = NotificationSetting::where('user_id', $admin->id)
                    ->where('notification_type', $type)
                    ->first();

                if ($existing) {
                    // Compare old values with new to detect real changes
                    $changed = (int) $existing->email_enabled !== $newValues['email_enabled']
                        || (int) $existing->sms_enabled !== $newValues['sms_enabled']
                        || (int) $existing->in_app_enabled !== $newValues['in_app_enabled'];

                    if ($changed) {
                        $existing->update($newValues);
                        $actualChanges++;
                    }
                } else {
                    // Create new record if it doesn't exist yet
                    NotificationSetting::create(array_merge(
                        [
                            'user_id'           => $admin->id,
                            'notification_type'  => $type,
                        ],
                        $newValues
                    ));
                    $actualChanges++;
                }
            }

            DB::commit();

            // Log activity with accurate change count
            ActivityLog::create([
                'user_id'    => $admin->id,
                'action'     => 'updated_notification_settings',
                'model_type' => 'NotificationSetting',
                'model_id'   => null,
                'description' => $actualChanges > 0
                    ? "Updated {$actualChanges} notification preference(s)"
                    : "Reviewed notification preferences (no changes)",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $message = $actualChanges > 0
                ? 'Notification preferences updated successfully!'
                : 'No changes were made to your notification preferences.';

            return redirect()->route('admin.settings.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            // FIX #3: Rollback on failure — no partial saves
            DB::rollBack();

            Log::error('Notification settings update failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Failed to update notification preferences. Please try again.');
        }
    }

    /**
     * Calculate channel-level summary stats from notification settings
     * FIX #5: Aggregates across ALL types, not just 'absence'
     *
     * @param array $settings
     * @return array
     */
    protected function getChannelStats(array $settings): array
    {
        $total = count($settings);
        $emailEnabled = 0;
        $smsEnabled = 0;
        $inAppEnabled = 0;

        foreach ($settings as $setting) {
            if (is_object($setting)) {
                if ($setting->email_enabled) $emailEnabled++;
                if ($setting->sms_enabled) $smsEnabled++;
                if ($setting->in_app_enabled) $inAppEnabled++;
            }
        }

        return [
            'total'        => $total,
            'email_count'  => $emailEnabled,
            'sms_count'    => $smsEnabled,
            'in_app_count' => $inAppEnabled,
        ];
    }
}
<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class SystemSettingsController extends Controller
{
    /**
     * Display system settings
     */
    public function index()
    {
        // Get all settings grouped by category
        $settings = SystemSetting::all()->groupBy(function($setting) {
            return $this->getSettingCategory($setting->key);
        });

        // Get statistics
        $stats = [
            'total_settings' => SystemSetting::count(),
            'last_updated' => SystemSetting::orderBy('updated_at', 'desc')->first()?->updated_at,
            'sms_enabled' => SystemSetting::get('sms_enabled', false),
            'email_enabled' => SystemSetting::get('email_enabled', false),
        ];

        return view('superadmin.settings.index', compact('settings', 'stats'));
    }

    /**
     * Update system settings
     */
    public function update(Request $request)
    {
        // Validation rules based on setting types
        $rules = [
            // School Information
            'school_name' => 'required|string|max:255',
            'school_email' => 'required|email|max:255',
            'school_phone' => 'required|string|max:20',
            'school_address' => 'required|string|max:500',
            'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // System Settings
            'max_class_capacity' => 'required|integer|min:1|max:100',
            'term_start_date' => 'required|date',
            'term_end_date' => 'required|date|after:term_start_date',
            'timezone' => 'nullable|string|max:100',
            'date_format' => 'nullable|string|max:50',
            'time_format' => 'nullable|string|max:50',

            // Notification Settings
            'email_enabled' => 'required|boolean',
            'sms_enabled' => 'required|boolean',
            'admin_notification_email' => 'required|email|max:255',

            // Academic Settings
            'attendance_required' => 'required|boolean',
            'late_homework_penalty' => 'required|boolean',
            'homework_due_days' => 'nullable|integer|min:1|max:30',
            'progress_report_frequency' => 'nullable|string|max:50',

            // Maintenance
            'maintenance_mode' => 'required|boolean',
            'maintenance_message' => 'nullable|string|max:500',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below.');
        }

        $updatedSettings = [];

        try {
            // Update each setting
            foreach ($request->except(['_token', 'school_logo']) as $key => $value) {
                $setting = SystemSetting::where('key', $key)->first();
                
                if ($setting) {
                    $oldValue = $setting->value;
                    
                    // Convert value based on type
                    $typedValue = $this->convertValueByType($value, $setting->type);
                    
                    $setting->update(['value' => $typedValue]);
                    
                    $updatedSettings[] = [
                        'key' => $key,
                        'old' => $oldValue,
                        'new' => $typedValue
                    ];
                } else {
                    // Create new setting if it doesn't exist
                    SystemSetting::create([
                        'key' => $key,
                        'value' => $value,
                        'type' => $this->inferType($value),
                    ]);
                    
                    $updatedSettings[] = [
                        'key' => $key,
                        'old' => null,
                        'new' => $value
                    ];
                }
            }

            // Handle logo upload
            if ($request->hasFile('school_logo')) {
                $logoPath = $request->file('school_logo')->store('logos', 'public');
                
                SystemSetting::updateOrCreate(
                    ['key' => 'school_logo'],
                    ['value' => $logoPath, 'type' => 'string']
                );
                
                $updatedSettings[] = [
                    'key' => 'school_logo',
                    'old' => SystemSetting::get('school_logo'),
                    'new' => $logoPath
                ];
            }

            // Clear settings cache
            Cache::forget('system_settings');

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated_system_settings',
                'model_type' => 'SystemSetting',
                'model_id' => null,
                'description' => 'Updated ' . count($updatedSettings) . ' system settings',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('superadmin.settings.index')
                ->with('success', 'System settings updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Settings update failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update settings. Please try again.');
        }
    }

    /**
     * Get setting category based on key
     */
    protected function getSettingCategory($key)
    {
        $categories = [
            'school_name' => 'school',
            'school_email' => 'school',
            'school_phone' => 'school',
            'school_address' => 'school',
            'school_logo' => 'school',
            
            'max_class_capacity' => 'system',
            'term_start_date' => 'system',
            'term_end_date' => 'system',
            'timezone' => 'system',
            'date_format' => 'system',
            'time_format' => 'system',
            'maintenance_mode' => 'system',
            'maintenance_message' => 'system',
            
            'email_enabled' => 'notifications',
            'sms_enabled' => 'notifications',
            'admin_notification_email' => 'notifications',
            'sms_provider' => 'notifications',
            
            'attendance_required' => 'academic',
            'late_homework_penalty' => 'academic',
            'homework_due_days' => 'academic',
            'progress_report_frequency' => 'academic',
        ];

        return $categories[$key] ?? 'other';
    }

    /**
     * Convert value based on type
     */
    protected function convertValueByType($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'integer':
                return (string) intval($value);
            case 'json':
                return is_array($value) ? json_encode($value) : $value;
            default:
                return $value;
        }
    }

    /**
     * Infer type from value
     */
    protected function inferType($value)
    {
        if (is_bool($value) || in_array($value, ['true', 'false', '0', '1'])) {
            return 'boolean';
        }
        if (is_numeric($value)) {
            return 'integer';
        }
        if (is_array($value)) {
            return 'json';
        }
        return 'string';
    }
}
<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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
            'school_phone' => ['required', 'string', 'min:10', 'max:20', 'regex:/^(\+44\s?|0)[0-9\s\-\(\)]{9,}$/'],
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
            'email_enabled' => 'nullable|boolean',
            'sms_enabled' => 'nullable|boolean',
            'admin_notification_email' => 'required|email|max:255',

            // Academic Settings
            'hourly_rate' => 'required|numeric|min:0|max:1000',  // NEW FIELD
            'attendance_required' => 'nullable|boolean',
            'late_homework_penalty' => 'nullable|boolean',
            'homework_due_days' => 'nullable|integer|min:1|max:30',
            'progress_report_frequency' => 'nullable|string|max:50',

            // Maintenance
            'maintenance_mode' => 'nullable|boolean',
            'maintenance_message' => 'nullable|string|max:500',
        ];

        // Custom error messages
        $messages = [
            'school_name.required' => 'School name is required.',
            'school_email.required' => 'School email is required.',
            'school_email.email' => 'Please provide a valid school email address.',
            'school_phone.required' => 'School phone is required.',
            'school_phone.min' => 'Phone number must be at least 10 characters.',
            'school_phone.max' => 'Phone number must not exceed 20 characters.',
            'school_phone.regex' => 'Please enter a valid UK phone number (e.g., +44 20 1234 5678 or 020 1234 5678).',
            'school_address.required' => 'School address is required.',
            'school_logo.image' => 'School logo must be an image.',
            'school_logo.mimes' => 'School logo must be a file of type: jpeg, png, jpg, gif.',
            'school_logo.max' => 'School logo must not exceed 2MB.',
            'term_end_date.after' => 'Term end date must be after the start date.',
            'admin_notification_email.required' => 'Admin notification email is required.',
            'admin_notification_email.email' => 'Please provide a valid admin email address.',
            
            // NEW: Hourly rate validation messages
            'hourly_rate.required' => 'Hourly rate is required.',
            'hourly_rate.numeric' => 'Hourly rate must be a number.',
            'hourly_rate.min' => 'Hourly rate must be at least £0.',
            'hourly_rate.max' => 'Hourly rate cannot exceed £1,000.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors below.');
        }

        $updatedSettings = [];

        try {
            // Handle checkbox fields (they won't be in request if unchecked)
            $checkboxFields = [
                'email_enabled',
                'sms_enabled',
                'attendance_required',
                'late_homework_penalty',
                'maintenance_mode'
            ];

            // Set checkbox values (0 if not checked, 1 if checked)
            foreach ($checkboxFields as $field) {
                $request->merge([$field => $request->has($field) ? 1 : 0]);
            }

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
                $file = $request->file('school_logo');
                
                // Validate file size
                if ($file->getSize() > 2097152) { // 2MB in bytes
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'School logo must not exceed 2MB.');
                }
                
                // Delete old logo if exists
                $oldLogo = SystemSetting::where('key', 'school_logo')->first();
                if ($oldLogo && $oldLogo->value && Storage::disk('public')->exists($oldLogo->value)) {
                    Storage::disk('public')->delete($oldLogo->value);
                }
                
                // Store new logo
                $logoPath = $file->store('logos', 'public');
                
                SystemSetting::updateOrCreate(
                    ['key' => 'school_logo'],
                    ['value' => $logoPath, 'type' => 'string']
                );
                
                $updatedSettings[] = [
                    'key' => 'school_logo',
                    'old' => $oldLogo?->value,
                    'new' => $logoPath
                ];
            }

            // Clear settings cache
            Cache::forget('system_settings');

            // Log activity with special note for hourly rate changes
            $description = 'Updated ' . count($updatedSettings) . ' system settings';
            
            // Check if hourly rate was changed
            $hourlyRateChange = collect($updatedSettings)->firstWhere('key', 'hourly_rate');
            if ($hourlyRateChange) {
                $description .= sprintf(
                    ' (Hourly rate changed from £%s to £%s)',
                    number_format($hourlyRateChange['old'] ?? 0, 2),
                    number_format($hourlyRateChange['new'], 2)
                );
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated_system_settings',
                'model_type' => 'SystemSetting',
                'model_id' => null,
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('superadmin.settings.index')
                ->with('success', 'System settings updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Settings update failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
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
            
            'hourly_rate' => 'academic',  // NEW CATEGORY MAPPING
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
            case 'decimal':  // NEW TYPE FOR HOURLY RATE
                return (string) number_format((float) $value, 2, '.', '');
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
        if (is_numeric($value) && strpos($value, '.') !== false) {
            return 'decimal';  // NEW: Detect decimal values
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
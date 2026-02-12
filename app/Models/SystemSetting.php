<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'description',
    ];

    /**
     * Cache key for all settings
     */
    const CACHE_KEY = 'system_settings';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Setting categories with their keys
     */
    const CATEGORIES = [
        'school' => [
            'school_name', 'school_email', 'school_phone',
            'school_address', 'school_logo',
        ],
        'system' => [
            'max_class_capacity', 'term_start_date', 'term_end_date',
            'timezone', 'date_format', 'time_format',
            'maintenance_mode', 'maintenance_message',
        ],
        'notifications' => [
            'email_enabled', 'sms_enabled',
            'admin_notification_email', 'sms_provider',
        ],
        'academic' => [
            'hourly_rate', 'attendance_required', 'late_homework_penalty',
            'homework_due_days', 'progress_report_frequency',
        ],
    ];

    /**
     * Checkbox fields that need special handling
     */
    const CHECKBOX_FIELDS = [
        'email_enabled',
        'sms_enabled',
        'attendance_required',
        'late_homework_penalty',
        'maintenance_mode',
    ];

    /**
     * Get setting value with proper type casting
     */
    public function getValueAttribute($value)
    {
        // Use attributes directly to avoid recursion
        $type = $this->attributes['type'] ?? 'string';

        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Set setting value
     */
    public function setValueAttribute($value)
    {
        $type = $this->attributes['type'] ?? 'string';

        if ($type === 'json') {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = (string) $value;
        }
    }

    /**
     * Get setting by key with caching
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $settings = static::getAllCached();
        return $settings[$key] ?? $default;
    }

    /**
     * Get raw value (bypasses accessor) - useful for form fields
     * 
     * @param string $key
     * @param mixed $default
     * @return string|null
     */
    public static function getRaw($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->attributes['value'] ?? $default : $default;
    }

    /**
     * Set setting by key (static helper) and clear cache
     */
    public static function set($key, $value, $type = 'string')
    {
        $result = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );

        static::clearCache();

        return $result;
    }

    /**
     * Get all settings as a cached key-value map
     * Returns raw string values (not cast) for use in forms and comparisons
     * 
     * @return array
     */
    public static function getAllCached()
    {
        return Cache::remember(static::CACHE_KEY, static::CACHE_TTL, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get all settings as a flat key-value map (for view consumption)
     * Returns raw database values for form display
     * 
     * @return array
     */
    public static function getAllForForm()
    {
        return static::all()->pluck('value', 'key')->toArray();
    }

    /**
     * Get category for a given key
     * 
     * @param string $key
     * @return string
     */
    public static function getCategoryForKey($key)
    {
        foreach (static::CATEGORIES as $category => $keys) {
            if (in_array($key, $keys)) {
                return $category;
            }
        }
        return 'other';
    }

    /**
     * Check if a key is a checkbox field
     * 
     * @param string $key
     * @return bool
     */
    public static function isCheckboxField($key)
    {
        return in_array($key, static::CHECKBOX_FIELDS);
    }

    /**
     * Clear the settings cache
     */
    public static function clearCache()
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * Check if the system is in maintenance mode
     * 
     * @return bool
     */
    public static function isMaintenanceMode()
    {
        return (bool) static::get('maintenance_mode', false);
    }

    /**
     * Check if email notifications are enabled
     * 
     * @return bool
     */
    public static function isEmailEnabled()
    {
        return filter_var(static::get('email_enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Check if SMS notifications are enabled
     * 
     * @return bool
     */
    public static function isSmsEnabled()
    {
        return filter_var(static::get('sms_enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get the school name
     * 
     * @return string
     */
    public static function schoolName()
    {
        return static::get('school_name', config('app.name', 'MLC Classroom'));
    }

    /**
     * Get the hourly rate
     * 
     * @return float
     */
    public static function hourlyRate()
    {
        return (float) static::get('hourly_rate', 50.00);
    }
}
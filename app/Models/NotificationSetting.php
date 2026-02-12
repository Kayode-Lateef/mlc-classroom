<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    /**
     * Centralised notification types — single source of truth
     * Used by controllers, views, and seeders
     */
    const TYPES = [
        'absence',
        'homework_assigned',
        'homework_graded',
        'progress_report',
        'schedule_change',
        'emergency',
    ];

    /**
     * Human-readable labels for each type
     */
    const TYPE_LABELS = [
        'absence'            => 'Student Absences',
        'homework_assigned'  => 'Homework Assigned',
        'homework_graded'    => 'Homework Graded',
        'progress_report'    => 'Progress Reports',
        'schedule_change'    => 'Schedule Changes',
        'emergency'          => 'Emergency Alerts',
    ];

    /**
     * Descriptions for each type
     */
    const TYPE_DESCRIPTIONS = [
        'absence'            => 'Alerts when a student is marked absent',
        'homework_assigned'  => 'Notifications when new homework is assigned',
        'homework_graded'    => 'Notifications when homework has been graded',
        'progress_report'    => 'Progress report updates and publications',
        'schedule_change'    => 'Class schedule modifications',
        'emergency'          => 'Urgent system or safety alerts',
    ];

    /**
     * Icons for each type (Themify Icons)
     */
    const TYPE_ICONS = [
        'absence'            => 'ti-alert',
        'homework_assigned'  => 'ti-clipboard',
        'homework_graded'    => 'ti-check-box',
        'progress_report'    => 'ti-bar-chart',
        'schedule_change'    => 'ti-calendar',
        'emergency'          => 'ti-alert',
    ];

    /**
     * Icon colours for each type
     */
    const TYPE_COLOURS = [
        'absence'            => '#e06829',
        'homework_assigned'  => '#3386f7',
        'homework_graded'    => '#28a745',
        'progress_report'    => '#17a2b8',
        'schedule_change'    => '#ffc107',
        'emergency'          => '#dc3545',
    ];

    protected $fillable = [
        'user_id',
        'notification_type',
        'email_enabled',
        'sms_enabled',
        'in_app_enabled',
    ];

    protected $casts = [
        'email_enabled'  => 'boolean',
        'sms_enabled'    => 'boolean',
        'in_app_enabled' => 'boolean',
    ];

    /**
     * Relationship: Setting belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all settings for a user, keyed by type
     * Creates defaults for any missing types
     * 
     * @param int $userId
     * @return array
     */
    public static function getForUser($userId)
    {
        $settings = static::where('user_id', $userId)
            ->get()
            ->keyBy('notification_type');

        $result = [];
        foreach (static::TYPES as $type) {
            $result[$type] = $settings->get($type) ?? (object) [
                'notification_type' => $type,
                'email_enabled'     => true,
                'sms_enabled'       => true,
                'in_app_enabled'    => true,
            ];
        }

        return $result;
    }

    /**
     * Scope: Settings for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the human-readable label for a notification type
     * 
     * @param string $type
     * @return string
     */
    public static function getLabel($type)
    {
        return static::TYPE_LABELS[$type] ?? ucwords(str_replace('_', ' ', $type));
    }

    /**
     * Get the description for a notification type
     * 
     * @param string $type
     * @return string
     */
    public static function getDescription($type)
    {
        return static::TYPE_DESCRIPTIONS[$type] ?? '';
    }
}
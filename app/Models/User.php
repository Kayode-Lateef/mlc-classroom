<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // ✅ ADDED: Email verification interface
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail // ✅ ADDED: implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'profile_photo',
        'email_verified_at',
        'status', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // === ROLE CHECKS ===

    /**
     * Check if user is a superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a teacher
     */
    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /**
     * Check if user is a parent
     */
    public function isParent(): bool
    {
        return $this->role === 'parent';
    }


    // === STATUS CHECKS ===

    /**
     * Check if user account is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user account is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if user account is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * ✅ NEW: Check if user account is banned
     */
    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }

    /**
     * Check if user can access the system
     * Only active users can access
     */
    public function canAccessSystem(): bool
    {
        return in_array($this->status, ['active']);
    }

    // === RELATIONSHIPS ===
    
    /**
     * If user is a parent, get their children (students)
     */
    public function children()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    /**
     * If user is a teacher, get their classes
     */
    public function teachingClasses()
    {
        return $this->hasMany(ClassModel::class, 'teacher_id');
    }

    /**
     * User's notification settings
     */
    public function notificationSettings()
    {
        return $this->hasMany(NotificationSetting::class);
    }

    /**
     * User's SMS logs (if parent)
     */
    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    /**
     * User's activity logs
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Learning resources uploaded by user (if teacher)
     */
    public function uploadedResources()
    {
        return $this->hasMany(LearningResource::class, 'uploaded_by');
    }

    /**
     * Homework assignments created by user (if teacher)
     */
    public function homeworkAssignments()
    {
        return $this->hasMany(HomeworkAssignment::class, 'teacher_id');
    }

    /**
     * Progress sheets created by user (if teacher)
     */
    public function progressSheets()
    {
        return $this->hasMany(ProgressSheet::class, 'teacher_id');
    }

    /**
     * Attendance records marked by user (if teacher)
     */
    public function markedAttendance()
    {
        return $this->hasMany(Attendance::class, 'marked_by');
    }

    /**
     * Get the user's full phone number for SMS
     */
    public function getFormattedPhoneAttribute(): string
    {
        return $this->phone ?? '';
    }
}
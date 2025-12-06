<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'date_of_birth',
        'parent_id',
        'enrollment_date',
        'status',
        'emergency_contact',
        'emergency_phone',
        'medical_info',
        'profile_photo',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
    ];

    /**
     * Get the full name of the student
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Relationship: Student belongs to a parent (User)
     */
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Relationship: Student has many class enrollments
     */
    public function enrollments()
    {
        return $this->hasMany(ClassEnrollment::class);
    }

    /**
     * Relationship: Student belongs to many classes (through enrollments)
     */
    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_enrollments')
                    ->withPivot('enrollment_date', 'status')
                    ->withTimestamps();
    }

    /**
     * Relationship: Student has many attendance records
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Relationship: Student has many homework submissions
     */
    public function homeworkSubmissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    /**
     * Relationship: Student has many progress notes
     */
    public function progressNotes()
    {
        return $this->hasMany(ProgressNote::class);
    }

    /**
     * Scope: Active students only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Students by parent
     */
    public function scopeByParent($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }
}
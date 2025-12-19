<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'subject',
        'level',
        'room_number',
        'teacher_id',
        'capacity',
        'description',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    /**
     * Relationship: Class belongs to a teacher (User)
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Relationship: Class has many schedules
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    /**
     * Relationship: Class has many enrollments
     */
    public function enrollments()
    {
        return $this->hasMany(ClassEnrollment::class, 'class_id');
    }

    /**
     * Relationship: Class has many students (through enrollments)
     * Fixed: Specify the correct foreign key 'class_id' instead of default 'class_model_id'
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'class_enrollments', 'class_id', 'student_id')
                    ->withPivot('enrollment_date', 'status')
                    ->withTimestamps();
    }

    /**
     * Relationship: Class has many attendance records
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    /**
     * Relationship: Class has many homework assignments
     */
    public function homeworkAssignments()
    {
        return $this->hasMany(HomeworkAssignment::class, 'class_id');
    }

    /**
     * Relationship: Class has many progress sheets
     */
    public function progressSheets()
    {
        return $this->hasMany(ProgressSheet::class, 'class_id');
    }

    /**
     * Relationship: Class has many learning resources
     */
    public function learningResources()
    {
        return $this->hasMany(LearningResource::class, 'class_id');
    }

    /**
     * Scope: Classes by teacher
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Get enrolled students count
     */
    public function getEnrolledStudentsCountAttribute()
    {
        return $this->students()->wherePivot('status', 'active')->count();
    }

    /**
     * Check if class is full
     */
    public function isFull(): bool
    {
        return $this->enrolled_students_count >= $this->capacity;
    }
}
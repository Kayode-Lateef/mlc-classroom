<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'homework_assignment_id',
        'student_id',
        'submitted_date',
        'status',
        'file_path',
        'teacher_comments',
        'grade',
        'graded_at',
    ];

    protected $casts = [
        'submitted_date' => 'datetime',
        'graded_at' => 'datetime',
    ];

    /**
     * Relationship: Submission belongs to a homework assignment
     */
    public function homeworkAssignment()
    {
        return $this->belongsTo(HomeworkAssignment::class);
    }

    /**
     * Relationship: Submission belongs to a student
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Check if submission is late
     */
    public function isLate(): bool
    {
        if (!$this->submitted_date) {
            return false;
        }
        
        return $this->submitted_date->gt($this->homeworkAssignment->due_date);
    }

    /**
     * Scope: Graded submissions
     */
    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    /**
     * Scope: Pending grading
     */
    public function scopePendingGrading($query)
    {
        return $query->where('status', 'submitted');
    }
}
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
        'submitted_by',        // ✅ NEW: Who marked as submitted
        'graded_by',           // ✅ NEW: Who graded
        'submission_notes',    // ✅ NEW: Notes when marking as submitted
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
     * ✅ NEW: Relationship - User who marked as submitted
     */
    public function submittedByUser()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * ✅ NEW: Relationship - User who graded
     */
    public function gradedByUser()
    {
        return $this->belongsTo(User::class, 'graded_by');
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
        return $query->whereIn('status', ['submitted', 'late']);
    }

    /**
     * ✅ NEW: Scope - Pending submission (not yet marked as submitted)
     */
    public function scopePendingSubmission($query)
    {
        return $query->where('status', 'pending');
    }
}
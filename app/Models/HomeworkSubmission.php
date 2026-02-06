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

    /**
     * Relationship: Submission has many topic grades
     */
    public function topicGrades()
    {
        return $this->hasMany(HomeworkSubmissionTopicGrade::class, 'homework_submission_id');
    }

    /**
     * Get topic grade for a specific topic
     */
    public function getTopicGrade($topicId)
    {
        return $this->topicGrades->where('homework_topic_id', $topicId)->first();
    }

    /**
     * Check if all topics have been graded
     */
    public function allTopicsGraded(): bool
    {
        $assignmentTopicCount = $this->homeworkAssignment->topics()->count();
        if ($assignmentTopicCount === 0) {
            return false;
        }
        return $this->topicGrades()->count() >= $assignmentTopicCount;
    }

    /**
     * Get total score across all graded topics
     */
    public function getTotalTopicScoreAttribute(): int
    {
        return $this->topicGrades->sum('score');
    }

    /**
     * Get total max score across all graded topics
     */
    public function getTotalTopicMaxScoreAttribute(): int
    {
        return $this->topicGrades->sum('max_score');
    }

    /**
     * Get overall percentage from topic grades
     */
    public function getTopicPercentageAttribute(): float
    {
        $maxScore = $this->total_topic_max_score;
        if ($maxScore <= 0) {
            return 0;
        }
        return round(($this->total_topic_score / $maxScore) * 100, 1);
    }
}
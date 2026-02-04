<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'progress_sheet_id',
        'title',
        'description',
        'assigned_date',
        'due_date',
        'file_path',
        'teacher_id',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Relationship: Homework belongs to a class
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Relationship: Homework belongs to a progress sheet
     */
    public function progressSheet()
    {
        return $this->belongsTo(ProgressSheet::class);
    }

    /**
     * Relationship: Homework belongs to a teacher
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Relationship: Homework has many submissions
     */
    public function submissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    /**
     * ✅ NEW: Relationship - Homework belongs to many topics
     */
    public function topics()
    {
        return $this->belongsToMany(HomeworkTopic::class, 'homework_assignment_topic')
            ->withTimestamps();
    }

    /**
     * Check if homework is overdue
     */
    public function isOverdue(): bool
    {
        return now()->gt($this->due_date);
    }

    /**
     * Get submission rate
     */
    public function getSubmissionRateAttribute()
    {
        $total = $this->submissions()->count();
        $submitted = $this->submissions()->where('status', '!=', 'pending')->count();
        
        return $total > 0 ? round(($submitted / $total) * 100, 2) : 0;
    }

    /**
     * ✅ NEW: Get grading rate
     */
    public function getGradingRateAttribute()
    {
        $total = $this->submissions()->count();
        $graded = $this->submissions()->where('status', 'graded')->count();
        
        return $total > 0 ? round(($graded / $total) * 100, 2) : 0;
    }
}
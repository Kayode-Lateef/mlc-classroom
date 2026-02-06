<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkSubmissionTopicGrade extends Model
{
    use HasFactory;

    protected $table = 'homework_submission_topic_grades';

    protected $fillable = [
        'homework_submission_id',
        'homework_topic_id',
        'score',
        'max_score',
        'comments',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'max_score' => 'integer',
        'graded_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to a homework submission
     */
    public function submission()
    {
        return $this->belongsTo(HomeworkSubmission::class, 'homework_submission_id');
    }

    /**
     * Relationship: Belongs to a homework topic
     */
    public function topic()
    {
        return $this->belongsTo(HomeworkTopic::class, 'homework_topic_id');
    }

    /**
     * Relationship: Graded by a user
     */
    public function gradedByUser()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get percentage score for this topic
     */
    public function getPercentageAttribute(): float
    {
        if ($this->max_score <= 0) {
            return 0;
        }
        return round(($this->score / $this->max_score) * 100, 1);
    }

    /**
     * Get formatted score display (e.g., "8/10")
     */
    public function getFormattedScoreAttribute(): string
    {
        return "{$this->score}/{$this->max_score}";
    }
}
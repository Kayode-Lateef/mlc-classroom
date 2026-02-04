<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'subject',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Topic belongs to many homework assignments
     */
    public function homeworkAssignments()
    {
        return $this->belongsToMany(HomeworkAssignment::class, 'homework_assignment_topic')
            ->withTimestamps();
    }

    /**
     * Scope: Active topics only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by subject
     */
    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'schedule_id',
        'date',
        'objective',
        'topic',
        'teacher_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Relationship: Progress sheet belongs to a class
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Relationship: Progress sheet belongs to a schedule
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Relationship: Progress sheet belongs to a teacher
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Relationship: Progress sheet has many progress notes
     */
    public function progressNotes()
    {
        return $this->hasMany(ProgressNote::class);
    }

    /**
     * Relationship: Progress sheet has many homework assignments
     */
    public function homeworkAssignments()
    {
        return $this->hasMany(HomeworkAssignment::class);
    }
}
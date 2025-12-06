<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'day_of_week',
        'start_time',
        'end_time',
        'recurring',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'recurring' => 'boolean',
    ];

    /**
     * Relationship: Schedule belongs to a class
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Relationship: Schedule has many attendance records
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Relationship: Schedule has many progress sheets
     */
    public function progressSheets()
    {
        return $this->hasMany(ProgressSheet::class);
    }

    /**
     * Scope: Schedules by day
     */
    public function scopeByDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute(): string
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }
}
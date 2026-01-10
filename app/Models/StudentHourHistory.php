<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHourHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'student_hour_history';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'old_hours',
        'new_hours',
        'changed_by',
        'reason',
        'changed_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'old_hours' => 'decimal:1',
        'new_hours' => 'decimal:1',
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the student associated with this history record.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who made the change.
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get the hour difference.
     */
    public function getHourDifferenceAttribute()
    {
        return $this->new_hours - $this->old_hours;
    }

    /**
     * Check if this was an increase.
     */
    public function isIncrease()
    {
        return $this->new_hours > $this->old_hours;
    }

    /**
     * Check if this was a decrease.
     */
    public function isDecrease()
    {
        return $this->new_hours < $this->old_hours;
    }

    /**
     * Get formatted difference with sign.
     */
    public function getFormattedDifferenceAttribute()
    {
        $diff = $this->hour_difference;
        if ($diff > 0) {
            return '+' . number_format($diff, 1) . ' hrs';
        } elseif ($diff < 0) {
            return number_format($diff, 1) . ' hrs';
        }
        return 'No change';
    }
}
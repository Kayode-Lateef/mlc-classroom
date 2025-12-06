<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'progress_sheet_id',
        'student_id',
        'performance',
        'notes',
    ];

    /**
     * Relationship: Progress note belongs to a progress sheet
     */
    public function progressSheet()
    {
        return $this->belongsTo(ProgressSheet::class);
    }

    /**
     * Relationship: Progress note belongs to a student
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
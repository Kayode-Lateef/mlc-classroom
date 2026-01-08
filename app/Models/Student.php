<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'date_of_birth',
        'parent_id',
        'enrollment_date',
        'status',
        'emergency_contact',
        'emergency_phone',
        'medical_info',
        'profile_photo',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the parent (guardian) of the student.
     */
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Get the class enrollments for the student.
     */
    public function enrollments()
    {
        return $this->hasMany(ClassEnrollment::class);
    }

    /**
     * Get the classes the student is enrolled in.
     * FIXED: Using 'class_id' instead of 'class_model_id'
     */
    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_enrollments', 'student_id', 'class_id')
            ->withPivot('enrollment_date', 'status')
            ->withTimestamps();
    }

    /**
     * Get the attendance records for the student.
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the homework submissions for the student.
     */
    public function homeworkSubmissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    /**
     * Get the progress notes for the student.
     */
    public function progressNotes()
    {
        return $this->hasMany(ProgressNote::class);
    }

    /**
     * Get the student's full name.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the student's age.
     */
    public function getAgeAttribute()
    {
        return Carbon::parse($this->date_of_birth)->age;
    }

    /**
     * Get the student's profile photo URL.
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }
        
        // Return default avatar or initials
        return null;
    }

    /**
     * Get the student's initials.
     */
    public function getInitialsAttribute()
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    /**
     * Check if student is active.
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if student is within valid age range (4-18 years).
     */
    public function isValidAge()
    {
        $age = $this->age;
        return $age >= 4 && $age <= 18;
    }

    /**
     * Get attendance rate for the student.
     */
    public function getAttendanceRate()
    {
        $total = $this->attendance()->count();
        if ($total === 0) {
            return 0;
        }

        $present = $this->attendance()->where('status', 'present')->count();
        return round(($present / $total) * 100, 2);
    }

    /**
     * Get homework completion rate.
     */
    public function getHomeworkCompletionRate()
    {
        $total = $this->homeworkSubmissions()->count();
        if ($total === 0) {
            return 0;
        }

        $submitted = $this->homeworkSubmissions()
            ->whereIn('status', ['submitted', 'graded'])
            ->count();
            
        return round(($submitted / $total) * 100, 2);
    }

    /**
     * Get the number of active class enrollments.
     */
    public function getActiveEnrollmentsCount()
    {
        return $this->enrollments()->where('status', 'active')->count();
    }

    /**
     * Scope to get only active students.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get students by parent.
     */
    public function scopeByParent($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Scope to get students enrolled in a specific year.
     */
    public function scopeEnrolledInYear($query, $year)
    {
        return $query->whereYear('enrollment_date', $year);
    }

    /**
     * Scope to get students by age range.
     */
    public function scopeByAgeRange($query, $minAge, $maxAge)
    {
        $maxDate = now()->subYears($minAge);
        $minDate = now()->subYears($maxAge + 1);
        
        return $query->whereBetween('date_of_birth', [$minDate, $maxDate]);
    }

    /**
     * Check if student has any related records.
     */
    public function hasRelatedRecords()
    {
        return $this->enrollments()->count() > 0 ||
               $this->attendance()->count() > 0 ||
               $this->homeworkSubmissions()->count() > 0 ||
               $this->progressNotes()->count() > 0;
    }

    /**
     * Get a summary of related records.
     */
    public function getRelatedRecordsSummary()
    {
        return [
            'enrollments' => $this->enrollments()->count(),
            'attendance' => $this->attendance()->count(),
            'homework' => $this->homeworkSubmissions()->count(),
            'progress_notes' => $this->progressNotes()->count(),
        ];
    }

    /**
     * Boot method to add model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Before deleting, check for relationships
        static::deleting(function ($student) {
            // This is called for both soft delete and force delete
            // Relationships are checked in the controller for better error handling
        });

        // After creating
        static::created(function ($student) {
            // Can add any post-creation logic here
        });

        // After updating
        static::updated(function ($student) {
            // Track status changes
            if ($student->isDirty('status')) {
                // Status has changed - could log this
            }
        });
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
// use App\Traits\HasUkDates;

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
        'weekly_hours',
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
        'weekly_hours' => 'decimal:1',
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
     * Get the hour history for this student.
     */
    public function hourHistory()
    {
        return $this->hasMany(StudentHourHistory::class)->orderBy('changed_at', 'desc');
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
     * Get formatted weekly hours.
     */
    public function getFormattedWeeklyHoursAttribute()
    {
        return number_format($this->weekly_hours, 1) . ' hrs/week';
    }

    /**
     * Get monthly hours.
     */
    public function getMonthlyHoursAttribute()
    {
        return round($this->weekly_hours * 4.33, 1);
    }

    /**
     * Check if student is active.
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if student is within valid age range.
     */
    public function isValidAge()
    {
        $age = $this->age;
        return $age >= 4 && $age <= 18;
    }

    /**
     * Get attendance rate.
     */
    public function getAttendanceRate()
    {
        $total = $this->attendance()->count();
        if ($total === 0) return 0;

        $present = $this->attendance()->where('status', 'present')->count();
        return round(($present / $total) * 100, 2);
    }

    /**
     * Get homework completion rate.
     */
    public function getHomeworkCompletionRate()
    {
        $total = $this->homeworkSubmissions()->count();
        if ($total === 0) return 0;

        $submitted = $this->homeworkSubmissions()
            ->whereIn('status', ['submitted', 'graded'])
            ->count();
            
        return round(($submitted / $total) * 100, 2);
    }

    /**
     * Get active enrollments count.
     */
    public function getActiveEnrollmentsCount()
    {
        return $this->enrollments()->where('status', 'active')->count();
    }

    /**
     * Scope: Active students only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Students by parent.
     */
    public function scopeByParent($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Scope: Students enrolled in year.
     */
    public function scopeEnrolledInYear($query, $year)
    {
        return $query->whereYear('enrollment_date', $year);
    }

    /**
     * Scope: Students by age range.
     */
    public function scopeByAgeRange($query, $minAge, $maxAge)
    {
        $maxDate = now()->subYears($minAge);
        $minDate = now()->subYears($maxAge + 1);
        
        return $query->whereBetween('date_of_birth', [$minDate, $maxDate]);
    }

    /**
     * Check if has related records.
     */
    public function hasRelatedRecords()
    {
        return $this->enrollments()->count() > 0 ||
               $this->attendance()->count() > 0 ||
               $this->homeworkSubmissions()->count() > 0 ||
               $this->progressNotes()->count() > 0;
    }

    /**
     * Get related records summary.
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
     * Log hour change to history.
     * Call this method manually in the controller after update.
     */
    public function logHourChange($oldHours, $newHours, $reason = null)
    {
        if ($oldHours != $newHours) {
            StudentHourHistory::create([
                'student_id' => $this->id,
                'old_hours' => $oldHours,
                'new_hours' => $newHours,
                'changed_by' => auth()->id(),
                'reason' => $reason,
                'changed_at' => now(),
            ]);
        }
    }
}
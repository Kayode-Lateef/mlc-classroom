<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\ClassModel;
use App\Helpers\NotificationHelper;
use App\Models\Schedule;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display system-wide attendance records
     */
    public function index(Request $request)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('view attendance')) {
            abort(403, 'You do not have permission to view attendance records.');
        }

        try {
            $query = Attendance::with(['student.parent', 'class.teacher', 'schedule', 'markedBy']);

            // Date range filter
            $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->get('date_to', now()->format('Y-m-d'));

            $query->whereBetween('date', [$dateFrom, $dateTo]);

            // Class filter
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            // Student filter
            if ($request->filled('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Teacher filter
            if ($request->filled('teacher_id')) {
                $query->where('marked_by', $request->teacher_id);
            }

            // Search student name
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->whereHas('student', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $attendance = $query->paginate(config('app.pagination.attendance', 50));

            // Get filter options
            $classes = ClassModel::orderBy('name')->get();
            $students = Student::where('status', 'active')->orderBy('first_name')->get();
            $teachers = User::where('role', 'teacher')
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

            // Statistics for selected period
            $stats = $this->calculateStatistics($dateFrom, $dateTo, $request);

            return view('admin.attendance.index', compact(
                'attendance',
                'classes',
                'students',
                'teachers',
                'stats',
                'dateFrom',
                'dateTo'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading attendance records: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An error occurred while loading attendance records. Please try again.');
        }
    }

    /**
     * Show the form for marking new attendance
     */
    public function create(Request $request)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('mark attendance')) {
            abort(403, 'You do not have permission to mark attendance.');
        }

        try {
            // Get all classes with active status
            $classes = ClassModel::with('teacher')
                ->whereHas('enrollments', function($q) {
                    $q->where('status', 'active');
                })
                ->orderBy('name')
                ->get();
            
            // Get selected class if provided
            $selectedClass = null;
            $schedules = collect();
            $students = collect();
            $selectedDate = $request->get('date', now()->format('Y-m-d'));
            $selectedSchedule = null;

            // ✅ ENHANCED: Validate date is not in the future
            if (Carbon::parse($selectedDate)->isFuture()) {
                $selectedDate = now()->format('Y-m-d');
            }

            if ($request->filled('class_id')) {
                $selectedClass = ClassModel::with(['teacher', 'schedules'])->find($request->class_id);
                
                if ($selectedClass) {
                    // Get schedules for selected class on the selected day
                    $dayOfWeek = Carbon::parse($selectedDate)->format('l');
                    $schedules = $selectedClass->schedules()
                        ->where('day_of_week', $dayOfWeek)
                        ->orderBy('start_time')
                        ->get();

                    // Get enrolled active students
                    $students = $selectedClass->students()
                        ->wherePivot('status', 'active')
                        ->where('students.status', 'active')
                        ->orderBy('first_name')
                        ->orderBy('last_name')
                        ->get();
                }
            }

            if ($request->filled('schedule_id')) {
                $selectedSchedule = Schedule::find($request->schedule_id);
            }

            // Check if attendance already marked for this session
            $existingAttendance = null;
            if ($selectedClass && $selectedSchedule) {
                $existingAttendance = Attendance::where('class_id', $selectedClass->id)
                    ->where('schedule_id', $selectedSchedule->id)
                    ->where('date', $selectedDate)
                    ->exists();
            }

            return view('admin.attendance.create', compact(
                'classes',
                'selectedClass',
                'schedules',
                'students',
                'selectedDate',
                'selectedSchedule',
                'existingAttendance'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading attendance creation form: ' . $e->getMessage());
            
            return back()->with('error', 'An error occurred while loading the form. Please try again.');
        }
    }

    /**
     * Store newly marked attendance
     */
    public function store(Request $request)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('mark attendance')) {
            abort(403, 'You do not have permission to mark attendance.');
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date|before_or_equal:today',
            'attendance' => 'required|array|min:1',
            'attendance.*' => 'required|in:present,absent,late,unauthorized',
            'notes.*' => 'nullable|string|max:500',
        ], [
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'schedule_id.required' => 'Please select a schedule/time slot.',
            'schedule_id.exists' => 'Selected schedule does not exist.',
            'date.required' => 'Attendance date is required.',
            'date.date' => 'Please enter a valid date.',
            'date.before_or_equal' => 'Cannot mark attendance for future dates.',
            'attendance.required' => 'Please mark attendance for at least one student.',
            'attendance.min' => 'Please mark attendance for at least one student.',
            'attendance.*.required' => 'Attendance status is required for all students.',
            'attendance.*.in' => 'Invalid attendance status. Please select: present, absent, late, or unauthorized.',
            'notes.*.max' => 'Notes cannot exceed 500 characters.',
        ]);

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();
        
        try {
            // ✅ ENHANCED: Verify class and schedule exist and are valid
            $class = ClassModel::with('teacher')->findOrFail($validated['class_id']);
            $schedule = Schedule::findOrFail($validated['schedule_id']);

            // Check if attendance already exists for this session
            $existingCount = Attendance::where('class_id', $validated['class_id'])
                ->where('schedule_id', $validated['schedule_id'])
                ->where('date', $validated['date'])
                ->count();

            if ($existingCount > 0) {
                return back()->with('error', 'Attendance has already been marked for this session. Please edit the existing records instead.');
            }

            $markedCount = 0;
            $absentStudents = [];
            $lateStudents = [];
            $unauthorizedStudents = [];
            $attendanceRecords = [];

            // Create attendance records
            foreach ($validated['attendance'] as $studentId => $status) {
                $attendanceRecord = Attendance::create([
                    'student_id' => $studentId,
                    'class_id' => $validated['class_id'],
                    'schedule_id' => $validated['schedule_id'],
                    'date' => $validated['date'],
                    'status' => $status,
                    'marked_by' => auth()->id(),
                    'notes' => $validated['notes'][$studentId] ?? null,
                ]);
                
                $attendanceRecords[] = $attendanceRecord;
                $markedCount++;

                // Track students by status for notifications
                if ($status === 'absent') {
                    $absentStudents[] = $studentId;
                } elseif ($status === 'late') {
                    $lateStudents[] = $studentId;
                } elseif ($status === 'unauthorized') {
                    $unauthorizedStudents[] = $studentId;
                }
            }

            // ✅ ENHANCED: Calculate attendance statistics
            $presentCount = count(array_filter($validated['attendance'], fn($s) => $s === 'present'));
            $attendanceRate = $markedCount > 0 ? round(($presentCount / $markedCount) * 100, 1) : 0;

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'marked_attendance',
                'model_type' => 'Attendance',
                'model_id' => null,
                'description' => "Marked attendance for {$markedCount} students in class: {$class->name} on {$validated['date']} ({$attendanceRate}% present)",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // ✅ ADDED: Send notifications after successful commit
            try {
                // Notify teacher if assigned
                if ($class->teacher) {
                    NotificationHelper::notifyUser(
                        $class->teacher,
                        'Attendance Marked',
                        "Attendance has been marked for {$class->name} on " . Carbon::parse($validated['date'])->format('d M Y') . " - {$presentCount}/{$markedCount} present ({$attendanceRate}%)",
                        'attendance_marked',
                        [
                            'class_id' => $class->id,
                            'date' => $validated['date'],
                            'total' => $markedCount,
                            'present' => $presentCount,
                            'absent' => count($absentStudents),
                            'url' => route('teacher.attendance.show', [$validated['date'], $class->id, $schedule->id])
                        ]
                    );
                }

                // ✅ CRITICAL: Notify parents of absent students
                if (!empty($absentStudents)) {
                    $this->notifyAbsentStudents($absentStudents, $validated['date'], $class);
                }

                // ✅ ADDED: Notify parents of late students
                if (!empty($lateStudents)) {
                    $this->notifyLateStudents($lateStudents, $validated['date'], $class);
                }

                // ✅ ADDED: Notify parents of unauthorized absence
                if (!empty($unauthorizedStudents)) {
                    $this->notifyUnauthorizedStudents($unauthorizedStudents, $validated['date'], $class);
                }

                // ✅ ADDED: Notify admins if attendance rate is below 70%
                if ($attendanceRate < 70 && $markedCount >= 5) {
                    NotificationHelper::notifyAdmins(
                        'Low Attendance Alert',
                        "Low attendance rate in {$class->name} on " . Carbon::parse($validated['date'])->format('d M Y') . " - only {$attendanceRate}% present ({$presentCount}/{$markedCount})",
                        'low_attendance',
                        [
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'date' => $validated['date'],
                            'attendance_rate' => $attendanceRate,
                            'total' => $markedCount,
                            'present' => $presentCount,
                            'url' => route('admin.attendance.show', [$validated['date'], $class->id, $schedule->id])
                        ],
                        auth()->id() // Exclude current admin
                    );
                }

            } catch (\Exception $e) {
                Log::error('Failed to send attendance notifications: ' . $e->getMessage());
                // Don't fail the request if notifications fail
            }

            return redirect()->route('admin.attendance.index')
                ->with('success', "Attendance marked successfully for {$markedCount} students! ({$attendanceRate}% present)");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error marking attendance: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);
            
            return back()
                ->withErrors(['error' => 'Failed to mark attendance. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Display the specified attendance session
     */
    public function show($date, $classId, $scheduleId)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('view attendance')) {
            abort(403, 'You do not have permission to view attendance records.');
        }

        try {
            $class = ClassModel::with('teacher')->findOrFail($classId);
            $schedule = Schedule::findOrFail($scheduleId);
            $attendanceDate = Carbon::parse($date);

            // Get all attendance records for this session
            $attendanceRecords = Attendance::with(['student.parent', 'markedBy'])
                ->where('class_id', $classId)
                ->where('schedule_id', $scheduleId)
                ->where('date', $date)
                ->orderBy('student_id')
                ->get();

            // ✅ ENHANCED: Calculate comprehensive statistics
            $stats = [
                'total' => $attendanceRecords->count(),
                'present' => $attendanceRecords->where('status', 'present')->count(),
                'absent' => $attendanceRecords->where('status', 'absent')->count(),
                'late' => $attendanceRecords->where('status', 'late')->count(),
                'unauthorized' => $attendanceRecords->where('status', 'unauthorized')->count(),
            ];

            $stats['attendance_rate'] = $stats['total'] > 0 
                ? round(($stats['present'] / $stats['total']) * 100, 1)
                : 0;

            // ✅ ADDED: Historical comparison
            $previousSessionRate = $this->getPreviousSessionRate($classId, $scheduleId, $date);
            $stats['trend'] = $previousSessionRate ? $stats['attendance_rate'] - $previousSessionRate : null;

            // Get who marked it
            $markedBy = $attendanceRecords->first()?->markedBy;
            $markedAt = $attendanceRecords->first()?->created_at;

            return view('admin.attendance.show', compact(
                'class',
                'schedule',
                'attendanceDate',
                'attendanceRecords',
                'stats',
                'markedBy',
                'markedAt'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading attendance session: ' . $e->getMessage(), [
                'date' => $date,
                'class_id' => $classId,
                'schedule_id' => $scheduleId
            ]);
            
            return back()->with('error', 'An error occurred while loading attendance details.');
        }
    }

    /**
     * Show the form for editing attendance
     */
    public function edit($date, $classId, $scheduleId)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('edit attendance')) {
            abort(403, 'You do not have permission to edit attendance records.');
        }

        try {
            $class = ClassModel::with('teacher')->findOrFail($classId);
            $schedule = Schedule::findOrFail($scheduleId);
            $attendanceDate = Carbon::parse($date);

            // ✅ ADDED: Warn if editing old records (more than 7 days)
            $daysDiff = now()->diffInDays($attendanceDate);
            $isOldRecord = $daysDiff > 7;

            // Get all attendance records for this session
            $attendanceRecords = Attendance::with('student.parent')
                ->where('class_id', $classId)
                ->where('schedule_id', $scheduleId)
                ->where('date', $date)
                ->get()
                ->keyBy('student_id');
            
            // Get all enrolled students (in case some are missing)
            $students = $class->students()
                ->wherePivot('status', 'active')
                ->orderBy('first_name')
                ->get();

            return view('admin.attendance.edit', compact(
                'class',
                'schedule',
                'attendanceDate',
                'attendanceRecords',
                'students',
                'isOldRecord',
                'daysDiff'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading attendance edit form: ' . $e->getMessage());
            
            return back()->with('error', 'An error occurred while loading the edit form.');
        }
    }

    /**
     * Update attendance records
     */
    public function update(Request $request, $date, $classId, $scheduleId)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('edit attendance')) {
            abort(403, 'You do not have permission to edit attendance records.');
        }

        $validated = $request->validate([
            'attendance' => 'required|array|min:1',
            'attendance.*' => 'required|in:present,absent,late,unauthorized',
            'notes.*' => 'nullable|string|max:500',
        ], [
            'attendance.required' => 'Please mark attendance for at least one student.',
            'attendance.min' => 'Please mark attendance for at least one student.',
            'attendance.*.required' => 'Attendance status is required for all students.',
            'attendance.*.in' => 'Invalid attendance status selected.',
            'notes.*.max' => 'Notes cannot exceed 500 characters.',
        ]);

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();
        
        try {
            $updatedCount = 0;
            $changedStudents = [];
            $newlyAbsent = [];
            $newlyPresent = [];

            // ✅ ENHANCED: Track changes for notifications
            foreach ($validated['attendance'] as $studentId => $newStatus) {
                $attendance = Attendance::where('student_id', $studentId)
                    ->where('class_id', $classId)
                    ->where('schedule_id', $scheduleId)
                    ->where('date', $date)
                    ->first();

                if ($attendance) {
                    $oldStatus = $attendance->status;
                    
                    $attendance->update([
                        'status' => $newStatus,
                        'notes' => $validated['notes'][$studentId] ?? null,
                    ]);
                    
                    $updatedCount++;

                    // Track status changes
                    if ($oldStatus !== $newStatus) {
                        $changedStudents[] = [
                            'student_id' => $studentId,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus
                        ];

                        if ($newStatus === 'absent' && $oldStatus !== 'absent') {
                            $newlyAbsent[] = $studentId;
                        } elseif ($oldStatus === 'absent' && $newStatus === 'present') {
                            $newlyPresent[] = $studentId;
                        }
                    }
                }
            }

            // ✅ ENHANCED: Activity log with change tracking
            $class = ClassModel::find($classId);
            $changeDescription = count($changedStudents) > 0 
                ? " ({$updatedCount} students updated, " . count($changedStudents) . " status changes)"
                : "";

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated_attendance',
                'model_type' => 'Attendance',
                'model_id' => null,
                'description' => "Updated attendance for class: {$class->name} on {$date}{$changeDescription}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // ✅ ADDED: Send notifications for status changes
            try {
                // Notify parents of newly absent students
                if (!empty($newlyAbsent)) {
                    $this->notifyAbsentStudents($newlyAbsent, $date, $class, true); // true = edited
                }

                // Notify parents of students marked present (if previously absent)
                if (!empty($newlyPresent)) {
                    $this->notifyStatusCorrected($newlyPresent, $date, $class);
                }

                // Notify teacher if significant changes (more than 20% of students)
                if ($class->teacher && count($changedStudents) > 0) {
                    $percentChanged = round((count($changedStudents) / $updatedCount) * 100, 1);
                    
                    if ($percentChanged >= 20) {
                        NotificationHelper::notifyUser(
                            $class->teacher,
                            'Attendance Records Updated',
                            "Attendance for {$class->name} on " . Carbon::parse($date)->format('d M Y') . " has been updated ({$percentChanged}% of students)",
                            'attendance_updated',
                            [
                                'class_id' => $class->id,
                                'date' => $date,
                                'changed_count' => count($changedStudents),
                                'url' => route('teacher.attendance.show', [$date, $classId, $scheduleId])
                            ]
                        );
                    }
                }

            } catch (\Exception $e) {
                Log::error('Failed to send attendance update notifications: ' . $e->getMessage());
            }

            return redirect()->route('admin.attendance.show', [$date, $classId, $scheduleId])
                ->with('success', "Attendance updated successfully for {$updatedCount} students!" . 
                    (count($changedStudents) > 0 ? " (" . count($changedStudents) . " changes)" : ""));

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating attendance: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'date' => $date,
                'class_id' => $classId
            ]);
            
            return back()
                ->withErrors(['error' => 'Failed to update attendance. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified attendance session
     */
    public function destroy($date, $classId, $scheduleId)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('delete attendance')) {
            abort(403, 'You do not have permission to delete attendance records.');
        }

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();
        
        try {
            // ✅ ADDED: Warn if deleting old records
            $daysDiff = now()->diffInDays(Carbon::parse($date));
            if ($daysDiff > 30) {
                return back()->with('error', 'Cannot delete attendance records older than 30 days. Please contact SuperAdmin if necessary.');
            }

            $deletedCount = Attendance::where('class_id', $classId)
                ->where('schedule_id', $scheduleId)
                ->where('date', $date)
                ->delete();

            if ($deletedCount === 0) {
                return back()->with('error', 'No attendance records found for this session.');
            }

            // Log activity
            $class = ClassModel::find($classId);
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'deleted_attendance',
                'model_type' => 'Attendance',
                'model_id' => null,
                'description' => "Deleted attendance session for class: {$class->name} on {$date} ({$deletedCount} records)",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            // ✅ ADDED: Notify teacher
            try {
                if ($class->teacher) {
                    NotificationHelper::notifyUser(
                        $class->teacher,
                        'Attendance Session Deleted',
                        "The attendance session for {$class->name} on " . Carbon::parse($date)->format('d M Y') . " has been deleted",
                        'attendance_deleted',
                        [
                            'class_id' => $class->id,
                            'date' => $date,
                            'deleted_count' => $deletedCount
                        ]
                    );
                }
            } catch (\Exception $e) {
                Log::error('Failed to send attendance deletion notification: ' . $e->getMessage());
            }

            return redirect()->route('admin.attendance.index')
                ->with('success', 'Attendance session deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting attendance: ' . $e->getMessage(), [
                'date' => $date,
                'class_id' => $classId
            ]);
            
            return back()->with('error', 'Failed to delete attendance session. Please try again.');
        }
    }

    /**
     * Calculate statistics for a date range
     */
    private function calculateStatistics($dateFrom, $dateTo, $request)
    {
        try {
            $query = Attendance::whereBetween('date', [$dateFrom, $dateTo]);

            // Apply filters if present
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }
            if ($request->filled('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            $stats = $query->selectRaw('
                COUNT(*) as total_records,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = "unauthorized" THEN 1 ELSE 0 END) as unauthorized
            ')->first();

            $attendanceRate = $stats->total_records > 0
                ? round(($stats->present / $stats->total_records) * 100, 1)
                : 0;

            return [
                'total_records' => $stats->total_records ?? 0,
                'present' => $stats->present ?? 0,
                'absent' => $stats->absent ?? 0,
                'late' => $stats->late ?? 0,
                'unauthorized' => $stats->unauthorized ?? 0,
                'attendance_rate' => $attendanceRate,
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating attendance statistics: ' . $e->getMessage());
            
            return [
                'total_records' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'unauthorized' => 0,
                'attendance_rate' => 0,
            ];
        }
    }

    /**
     * Get previous session attendance rate for trend analysis
     */
    private function getPreviousSessionRate($classId, $scheduleId, $currentDate)
    {
        try {
            $previousSession = Attendance::where('class_id', $classId)
                ->where('schedule_id', $scheduleId)
                ->where('date', '<', $currentDate)
                ->orderBy('date', 'desc')
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present
                ')
                ->first();

            if ($previousSession && $previousSession->total > 0) {
                return round(($previousSession->present / $previousSession->total) * 100, 1);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error getting previous session rate: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ HELPER METHOD: Notify parents of absent students
     */
    private function notifyAbsentStudents(array $studentIds, $date, ClassModel $class, $isEdited = false)
    {
        try {
            $students = Student::with('parent')
                ->whereIn('id', $studentIds)
                ->get();

            foreach ($students as $student) {
                if (!$student->parent) {
                    Log::warning("Student {$student->id} has no parent assigned for absence notification");
                    continue;
                }

                $title = $isEdited ? 'Attendance Correction - Student Absent' : 'Student Absence Alert';
                $message = $isEdited
                    ? "{$student->full_name}'s attendance was corrected to absent for {$class->name} on " . Carbon::parse($date)->format('d M Y')
                    : "{$student->full_name} was marked absent from {$class->name} on " . Carbon::parse($date)->format('d M Y');

                NotificationHelper::notifyUser(
                    $student->parent,
                    $title,
                    $message,
                    'absence',
                    [
                        'student_id' => $student->id,
                        'date' => $date,
                        'class_name' => $class->name,
                        'class_id' => $class->id,
                        'is_edited' => $isEdited,
                        'url' => route('parent.students.show', $student->id)
                    ]
                );
            }

            Log::info("Absence notifications sent for " . count($studentIds) . " students");

        } catch (\Exception $e) {
            Log::error('Failed to send absence notifications: ' . $e->getMessage());
        }
    }

    /**
     * ✅ HELPER METHOD: Notify parents of late students
     */
    private function notifyLateStudents(array $studentIds, $date, ClassModel $class)
    {
        try {
            $students = Student::with('parent')
                ->whereIn('id', $studentIds)
                ->get();

            foreach ($students as $student) {
                if (!$student->parent) {
                    continue;
                }

                NotificationHelper::notifyUser(
                    $student->parent,
                    'Student Late Arrival',
                    "{$student->full_name} arrived late to {$class->name} on " . Carbon::parse($date)->format('d M Y'),
                    'late',
                    [
                        'student_id' => $student->id,
                        'date' => $date,
                        'class_name' => $class->name,
                        'class_id' => $class->id,
                        'url' => route('parent.students.show', $student->id)
                    ]
                );
            }

            Log::info("Late arrival notifications sent for " . count($studentIds) . " students");

        } catch (\Exception $e) {
            Log::error('Failed to send late notifications: ' . $e->getMessage());
        }
    }

    /**
     * ✅ HELPER METHOD: Notify parents of unauthorized absence
     */
    private function notifyUnauthorizedStudents(array $studentIds, $date, ClassModel $class)
    {
        try {
            $students = Student::with('parent')
                ->whereIn('id', $studentIds)
                ->get();

            foreach ($students as $student) {
                if (!$student->parent) {
                    continue;
                }

                NotificationHelper::notifyUser(
                    $student->parent,
                    'Unauthorized Absence Alert',
                    "{$student->full_name} had an unauthorized absence from {$class->name} on " . Carbon::parse($date)->format('d M Y'). ". Please contact the school.",
                    'unauthorized_absence',
                    [
                        'student_id' => $student->id,
                        'date' => $date,
                        'class_name' => $class->name,
                        'class_id' => $class->id,
                        'urgent' => true,
                        'url' => route('parent.students.show', $student->id)
                    ]
                );
            }

            Log::info("Unauthorized absence notifications sent for " . count($studentIds) . " students");

        } catch (\Exception $e) {
            Log::error('Failed to send unauthorized absence notifications: ' . $e->getMessage());
        }
    }

    /**
     * ✅ HELPER METHOD: Notify parents when status corrected from absent to present
     */
    private function notifyStatusCorrected(array $studentIds, $date, ClassModel $class)
    {
        try {
            $students = Student::with('parent')
                ->whereIn('id', $studentIds)
                ->get();

            foreach ($students as $student) {
                if (!$student->parent) {
                    continue;
                }

                NotificationHelper::notifyUser(
                    $student->parent,
                    'Attendance Record Corrected',
                    "{$student->full_name}'s attendance for {$class->name} on " . Carbon::parse($date)->format('d M Y') . " has been updated to present",
                    'attendance_corrected',
                    [
                        'student_id' => $student->id,
                        'date' => $date,
                        'class_name' => $class->name,
                        'class_id' => $class->id,
                        'url' => route('parent.students.show', $student->id)
                    ]
                );
            }

            Log::info("Attendance correction notifications sent for " . count($studentIds) . " students");

        } catch (\Exception $e) {
            Log::error('Failed to send correction notifications: ' . $e->getMessage());
        }
    }
}
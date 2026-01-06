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
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display system-wide attendance records
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['student', 'class', 'schedule', 'markedBy']);

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
            $query->whereHas('student', function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $attendance = $query->paginate(50);

        // Get filter options
        $classes = ClassModel::orderBy('name')->get();
        $students = Student::where('status', 'active')->orderBy('first_name')->get();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();

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
    }

    /**
     * Show the form for marking new attendance
     */
    public function create(Request $request)
    {
        // Get all classes
        $classes = ClassModel::with('teacher')->orderBy('name')->get();
        
        // Get selected class if provided
        $selectedClass = null;
        $schedules = collect();
        $students = collect();
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        $selectedSchedule = null;

        if ($request->filled('class_id')) {
            $selectedClass = ClassModel::with(['teacher', 'schedules'])->find($request->class_id);
            
            if ($selectedClass) {
                // Get schedules for selected class
                $dayOfWeek = Carbon::parse($selectedDate)->format('l');
                $schedules = $selectedClass->schedules()
                    ->where('day_of_week', $dayOfWeek)
                    ->orderBy('start_time')
                    ->get();

                // Get enrolled students
                $students = $selectedClass->students()
                    ->wherePivot('status', 'active')
                    ->orderBy('first_name')
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
    }

    /**
     * Store newly marked attendance
     */
    public function store(Request $request)
    {
        // Custom validation messages
        $messages = [
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'schedule_id.required' => 'Please select a schedule/time slot.',
            'schedule_id.exists' => 'Selected schedule does not exist.',
            'date.required' => 'Attendance date is required.',
            'date.date' => 'Please enter a valid date.',
            'date.before_or_equal' => 'Cannot mark attendance for future dates.',
            'attendance.required' => 'Please mark attendance for at least one student.',
            'attendance.*.required' => 'Attendance status is required for all students.',
            'attendance.*.in' => 'Invalid attendance status. Please select: present, absent, late, or unauthorized.',
            'notes.*.max' => 'Notes cannot exceed 500 characters.',
        ];

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date|before_or_equal:today',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,unauthorized',
            'notes.*' => 'nullable|string|max:500',
        ], $messages);

        // Check if attendance already exists for this session
        $existingCount = Attendance::where('class_id', $request->class_id)
            ->where('schedule_id', $request->schedule_id)
            ->where('date', $request->date)
            ->count();

        if ($existingCount > 0) {
            return back()->with('error', 'Attendance has already been marked for this session. Please edit the existing records instead.');
        }

        $markedCount = 0;
        $absentStudents = []; // Track absent students for notifications

        DB::beginTransaction();
        try {
            foreach ($request->attendance as $studentId => $status) {
                Attendance::create([
                    'student_id' => $studentId,
                    'class_id' => $request->class_id,
                    'schedule_id' => $request->schedule_id,
                    'date' => $request->date,
                    'status' => $status,
                    'marked_by' => auth()->id(),
                    'notes' => $request->notes[$studentId] ?? null,
                ]);
                $markedCount++;

                // Track absent students
                if ($status === 'absent') {
                    $absentStudents[] = $studentId;
                }
            }

            // Log activity
            $class = ClassModel::find($request->class_id);
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'marked_attendance',
                'model_type' => 'Attendance',
                'model_id' => null,
                'description' => "Marked attendance for {$markedCount} students in class: {$class->name} on {$request->date}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // SEND NOTIFICATIONS TO PARENTS OF ABSENT STUDENTS (After successful commit)
            if (!empty($absentStudents)) {
                $this->notifyAbsentStudents($absentStudents, $request->date, $class);
            }

            return redirect()->route('admin.attendance.index')
                ->with('success', "Attendance marked successfully for {$markedCount} students!");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Attendance marking failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to mark attendance. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified attendance session
     */
    public function show($date, $classId, $scheduleId)
    {
        $class = ClassModel::with('teacher')->findOrFail($classId);
        $schedule = Schedule::findOrFail($scheduleId);
        $attendanceDate = Carbon::parse($date);

        // Get all attendance records for this session
        $attendanceRecords = Attendance::with(['student', 'markedBy'])
            ->where('class_id', $classId)
            ->where('schedule_id', $scheduleId)
            ->where('date', $date)
            ->orderBy('student_id')
            ->get();

        // Calculate statistics
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

        return view('admin.attendance.show', compact(
            'class',
            'schedule',
            'attendanceDate',
            'attendanceRecords',
            'stats'
        ));
    }

    /**
     * Show the form for editing attendance
     */
    public function edit($date, $classId, $scheduleId)
    {
        $class = ClassModel::with('teacher')->findOrFail($classId);
        $schedule = Schedule::findOrFail($scheduleId);
        $attendanceDate = Carbon::parse($date);

        // Get all attendance records for this session
        $attendanceRecords = Attendance::with('student')
            ->where('class_id', $classId)
            ->where('schedule_id', $scheduleId)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');

        return view('admin.attendance.edit', compact(
            'class',
            'schedule',
            'attendanceDate',
            'attendanceRecords'
        ));
    }

    /**
     * Update attendance records
     */
    public function update(Request $request, $date, $classId, $scheduleId)
    {
        // Custom validation messages
        $messages = [
            'attendance.required' => 'Please mark attendance for at least one student.',
            'attendance.*.required' => 'Attendance status is required for all students.',
            'attendance.*.in' => 'Invalid attendance status selected.',
            'notes.*.max' => 'Notes cannot exceed 500 characters.',
        ];

        $request->validate([
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,unauthorized',
            'notes.*' => 'nullable|string|max:500',
        ], $messages);

        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($request->attendance as $studentId => $status) {
                Attendance::where('student_id', $studentId)
                    ->where('class_id', $classId)
                    ->where('schedule_id', $scheduleId)
                    ->where('date', $date)
                    ->update([
                        'status' => $status,
                        'notes' => $request->notes[$studentId] ?? null,
                    ]);
                $updatedCount++;
            }

            // Log activity
            $class = ClassModel::find($classId);
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated_attendance',
                'model_type' => 'Attendance',
                'model_id' => null,
                'description' => "Updated attendance for {$updatedCount} students in class: {$class->name} on {$date}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('admin.attendance.show', [$date, $classId, $scheduleId])
                ->with('success', "Attendance updated successfully for {$updatedCount} students!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update attendance. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified attendance session
     */
    public function destroy($date, $classId, $scheduleId)
    {
        try {
            $deletedCount = Attendance::where('class_id', $classId)
                ->where('schedule_id', $scheduleId)
                ->where('date', $date)
                ->delete();

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

            return redirect()->route('admin.attendance.index')
                ->with('success', 'Attendance session deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete attendance session.');
        }
    }

    /**
     * Calculate statistics for a date range
     */
    private function calculateStatistics($dateFrom, $dateTo, $request)
    {
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
    }

    /**
     * HELPER METHOD: Notify parents of absent students
     */
    private function notifyAbsentStudents(array $studentIds, $date, ClassModel $class)
    {
        try {
            $students = Student::with('parent')
                ->whereIn('id', $studentIds)
                ->get();

            foreach ($students as $student) {
                // Only notify if student has a parent
                if (!$student->parent) {
                    \Log::warning("Student {$student->id} has no parent assigned for absence notification");
                    continue;
                }

                NotificationHelper::notifyStudentParent(
                    $student,
                    'Student Absence',
                    "{$student->first_name} was marked absent on " . date('d M Y', strtotime($date)),
                    'absence',
                    [
                        'date' => $date,
                        'class_name' => $class->name,
                        'class_id' => $class->id,
                        'url' => route('parent.attendance.show', $student->id)
                    ]
                );
            }

            \Log::info("Absence notifications sent for " . count($studentIds) . " students");

        } catch (\Exception $e) {
            // Don't fail the request if notifications fail
            \Log::error('Failed to send absence notifications: ' . $e->getMessage());
        }
    }
}
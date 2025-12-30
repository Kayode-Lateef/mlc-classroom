<?php

namespace App\Http\Controllers\SuperAdmin;

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

        return view('superadmin.attendance.index', compact(
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

        return view('superadmin.attendance.create', compact(
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
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date|before_or_equal:today',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,unauthorized',
            'notes.*' => 'nullable|string|max:500',
        ]);

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

            return redirect()->route('superadmin.attendance.index')
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

        // Get who marked it
        $markedBy = $attendanceRecords->first()?->markedBy;
        $markedAt = $attendanceRecords->first()?->created_at;

        return view('superadmin.attendance.show', compact(
            'class',
            'schedule',
            'attendanceDate',
            'attendanceRecords',
            'stats',
            'markedBy',
            'markedAt'
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

        // Get all enrolled students (in case some are missing)
        $students = $class->students()
            ->wherePivot('status', 'active')
            ->orderBy('first_name')
            ->get();

        return view('superadmin.attendance.edit', compact(
            'class',
            'schedule',
            'attendanceDate',
            'attendanceRecords',
            'students'
        ));
    }

    /**
     * Update the specified attendance records
     */
    public function update(Request $request, $date, $classId, $scheduleId)
    {
        $request->validate([
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,unauthorized',
            'notes.*' => 'nullable|string|max:500',
        ]);

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

            return redirect()->route('superadmin.attendance.show', [$date, $classId, $scheduleId])
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

            return redirect()->route('superadmin.attendance.index')
                ->with('success', 'Attendance session deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete attendance session.');
        }
    }

    /**
     * Display daily attendance dashboard
     */
    public function daily(Request $request)
    {
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        $dayOfWeek = Carbon::parse($selectedDate)->format('l');

        // Get all schedules for this day
        $schedules = Schedule::with(['class.teacher', 'class.enrollments'])
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get();

        // Check which sessions already have attendance marked
        $schedulesWithStatus = $schedules->map(function($schedule) use ($selectedDate) {
            $attendanceCount = Attendance::where('class_id', $schedule->class_id)
                ->where('schedule_id', $schedule->id)
                ->where('date', $selectedDate)
                ->count();

            $enrolledCount = $schedule->class->enrollments()
                ->where('status', 'active')
                ->count();

            $schedule->attendance_marked = $attendanceCount > 0;
            $schedule->attendance_count = $attendanceCount;
            $schedule->enrolled_count = $enrolledCount;
            $schedule->is_complete = $attendanceCount === $enrolledCount && $attendanceCount > 0;

            return $schedule;
        });

        // Statistics for the day
        $stats = [
            'total_sessions' => $schedules->count(),
            'marked_sessions' => $schedulesWithStatus->where('attendance_marked', true)->count(),
            'pending_sessions' => $schedulesWithStatus->where('attendance_marked', false)->count(),
            'total_students' => $schedulesWithStatus->sum('enrolled_count'),
        ];

        // Get today's attendance summary
        $todayAttendance = Attendance::where('date', $selectedDate)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late
            ')
            ->first();

        $stats['attendance_summary'] = [
            'total' => $todayAttendance->total ?? 0,
            'present' => $todayAttendance->present ?? 0,
            'absent' => $todayAttendance->absent ?? 0,
            'late' => $todayAttendance->late ?? 0,
            'rate' => $todayAttendance->total > 0 
                ? round(($todayAttendance->present / $todayAttendance->total) * 100, 1) 
                : 0
        ];

        return view('superadmin.attendance.daily', compact(
            'schedulesWithStatus',
            'selectedDate',
            'dayOfWeek',
            'stats'
        ));
    }

    /**
     * Display attendance reports and analytics
     */
    public function reports(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $classFilter = $request->get('class_id');

        // Overall statistics
        $overallStats = $this->calculateStatistics($dateFrom, $dateTo, $request);

        // Attendance by class
        $attendanceByClassQuery = Attendance::select(
                'class_id',
                DB::raw('COUNT(*) as total_records'),
                DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count'),
                DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count'),
                DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_count'),
                DB::raw('SUM(CASE WHEN status = "unauthorized" THEN 1 ELSE 0 END) as unauthorized_count')
            )
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if ($classFilter) {
            $attendanceByClassQuery->where('class_id', $classFilter);
        }

        $attendanceByClass = $attendanceByClassQuery
            ->groupBy('class_id')
            ->with('class.teacher')
            ->get()
            ->map(function($item) {
                $item->attendance_rate = $item->total_records > 0 
                    ? round(($item->present_count / $item->total_records) * 100, 1)
                    : 0;
                return $item;
            })
            ->sortByDesc('attendance_rate');

        // Daily attendance trend
        $dailyTrendQuery = Attendance::select(
                'date',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present'),
                DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent')
            )
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if ($classFilter) {
            $dailyTrendQuery->where('class_id', $classFilter);
        }

        $dailyTrend = $dailyTrendQuery
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function($item) {
                $item->rate = $item->total > 0 
                    ? round(($item->present / $item->total) * 100, 1)
                    : 0;
                return $item;
            });

        // Students with low attendance (< 75%)
        $lowAttendanceQuery = Student::select('students.*')
            ->join('attendance', 'students.id', '=', 'attendance.student_id')
            ->whereBetween('attendance.date', [$dateFrom, $dateTo]);

        if ($classFilter) {
            $lowAttendanceQuery->join('class_enrollments', function($join) use ($classFilter) {
                $join->on('students.id', '=', 'class_enrollments.student_id')
                     ->where('class_enrollments.class_id', '=', $classFilter)
                     ->where('class_enrollments.status', '=', 'active');
            });
        }

        $lowAttendanceStudents = $lowAttendanceQuery
            ->groupBy('students.id')
            ->havingRaw('(SUM(CASE WHEN attendance.status = "present" THEN 1 ELSE 0 END) / COUNT(*)) < 0.75')
            ->with(['parent'])
            ->get()
            ->map(function($student) use ($dateFrom, $dateTo) {
                $attendanceStats = Attendance::where('student_id', $student->id)
                    ->whereBetween('date', [$dateFrom, $dateTo])
                    ->selectRaw('
                        COUNT(*) as total,
                        SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent
                    ')
                    ->first();

                $student->total_records = $attendanceStats->total ?? 0;
                $student->present_count = $attendanceStats->present ?? 0;
                $student->absent_count = $attendanceStats->absent ?? 0;
                $student->attendance_rate = $student->total_records > 0
                    ? round(($student->present_count / $student->total_records) * 100, 1)
                    : 0;

                return $student;
            })
            ->sortBy('attendance_rate');

        // Prepare chart data for daily trend
        $chartData = [
            'labels' => $dailyTrend->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('M d');
            })->toArray(),
            'data' => $dailyTrend->pluck('rate')->toArray(),
        ];

        // Get filter options
        $classes = ClassModel::orderBy('name')->get();

        return view('superadmin.attendance.reports', compact(
            'overallStats',
            'attendanceByClass',
            'dailyTrend',
            'lowAttendanceStudents',
            'chartData',
            'classes',
            'dateFrom',
            'dateTo',
            'classFilter'
        ));
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
     * Bulk mark attendance for multiple students
     */
    public function bulkMark(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'status' => 'required|in:present,absent,late,unauthorized',
            'class_id' => 'required|exists:classes,id',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date|before_or_equal:today',
        ]);

        $markedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($request->student_ids as $studentId) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_id' => $request->class_id,
                        'schedule_id' => $request->schedule_id,
                        'date' => $request->date,
                    ],
                    [
                        'status' => $request->status,
                        'marked_by' => auth()->id(),
                    ]
                );
                $markedCount++;
            }

            DB::commit();

            // SEND NOTIFICATIONS TO PARENTS IF MARKING ABSENT (After successful commit)
            if ($request->status === 'absent') {
                $class = ClassModel::find($request->class_id);
                $this->notifyAbsentStudents($request->student_ids, $request->date, $class);
            }

            return response()->json([
                'success' => true,
                'message' => "Bulk marked {$markedCount} students as " . ucfirst($request->status),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk attendance marking failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk mark attendance.',
            ], 500);
        }
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


    /**
     * Export attendance data
     */
    public function export(Request $request)
    {
        // Placeholder for Excel export functionality
        return redirect()->back()->with('info', 'Excel export functionality will be available soon.');
    }
}
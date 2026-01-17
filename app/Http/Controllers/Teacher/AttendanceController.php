<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance records (for teacher's classes only)
     */
    public function index(Request $request)
    {
        $teacher = auth()->user();
        
        // Get only teacher's classes
        $myClasses = ClassModel::where('teacher_id', $teacher->id)->get();
        $myClassIds = $myClasses->pluck('id');

        $query = Attendance::with(['student', 'class', 'schedule'])
            ->whereIn('class_id', $myClassIds);

        // Date range filter
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $query->whereBetween('date', [$dateFrom, $dateTo]);

        // Class filter
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $attendanceRecords = $query->paginate(50);

        // Statistics
        $stats = $this->calculateStatistics($dateFrom, $dateTo, $request, $myClassIds);

        return view('teacher.attendance.index', compact(
            'attendanceRecords',
            'myClasses',
            'stats',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show the form for marking attendance
     */
    public function create(Request $request)
    {
        $teacher = auth()->user();
        
        // Get only teacher's classes
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->with('schedules')
            ->orderBy('name')
            ->get();

        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        $selectedClass = null;
        $selectedSchedule = null;
        $students = collect();

        if ($request->filled('class_id') && $request->filled('schedule_id')) {
            $selectedClass = ClassModel::where('id', $request->class_id)
                ->where('teacher_id', $teacher->id) // Verify ownership
                ->firstOrFail();
                
            $selectedSchedule = Schedule::findOrFail($request->schedule_id);

            // Get enrolled students (active only)
            $students = $selectedClass->students()
                ->wherePivot('status', 'active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            // Check if attendance already marked
            $existingCount = Attendance::where('class_id', $selectedClass->id)
                ->where('schedule_id', $selectedSchedule->id)
                ->where('date', $selectedDate)
                ->count();

            if ($existingCount > 0) {
                return redirect()
                    ->route('teacher.attendance.edit', [
                        'date' => $selectedDate,
                        'classId' => $selectedClass->id,
                        'scheduleId' => $selectedSchedule->id
                    ])
                    ->with('info', 'Attendance already marked. You can edit it here.');
            }
        }

        return view('teacher.attendance.create', compact(
            'classes',
            'selectedDate',
            'selectedClass',
            'selectedSchedule',
            'students'
        ));
    }

    /**
     * Store newly marked attendance
     */
    public function store(Request $request)
    {
        $teacher = auth()->user();

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
            'attendance.min' => 'Please mark attendance for at least one student.',
            'attendance.*.required' => 'Attendance status is required for all students.',
            'attendance.*.in' => 'Invalid attendance status. Please select: present, absent, late, or unauthorized.',
            'notes.*.max' => 'Notes cannot exceed 500 characters.',
        ];

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date|before_or_equal:today',
            'attendance' => 'required|array|min:1',
            'attendance.*' => 'required|in:present,absent,late,unauthorized',
            'notes.*' => 'nullable|string|max:500',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors below.');
        }

        // Verify class belongs to teacher
        $class = ClassModel::where('id', $request->class_id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $schedule = Schedule::findOrFail($request->schedule_id);

        // Check if attendance already exists
        $existingCount = Attendance::where('class_id', $request->class_id)
            ->where('schedule_id', $request->schedule_id)
            ->where('date', $request->date)
            ->count();

        if ($existingCount > 0) {
            return back()->with('error', 'Attendance has already been marked for this session. Please edit the existing records instead.');
        }

        $markedCount = 0;
        $absentStudents = [];

        DB::beginTransaction();
        try {
            foreach ($request->attendance as $studentId => $status) {
                Attendance::create([
                    'student_id' => $studentId,
                    'class_id' => $request->class_id,
                    'schedule_id' => $request->schedule_id,
                    'date' => $request->date,
                    'status' => $status,
                    'marked_by' => $teacher->id,
                    'notes' => $request->notes[$studentId] ?? null,
                ]);
                $markedCount++;

                if ($status === 'absent') {
                    $absentStudents[] = $studentId;
                }
            }

            // Calculate statistics
            $presentCount = count(array_filter($request->attendance, fn($s) => $s === 'present'));
            $attendanceRate = $markedCount > 0 ? round(($presentCount / $markedCount) * 100, 1) : 0;

            // Log activity
            ActivityLog::create([
                'user_id' => $teacher->id,
                'action' => 'marked_attendance',
                'model_type' => 'Attendance',
                'model_id' => null,
                'description' => "Marked attendance for {$markedCount} students in {$class->name} on {$request->date} ({$attendanceRate}% present)",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Notify parents of absent students
            if (!empty($absentStudents)) {
                $this->notifyAbsentStudents($absentStudents, $request->date, $class);
            }

            return redirect()->route('teacher.attendance.index')
                ->with('success', "Attendance marked successfully for {$markedCount} students! ({$attendanceRate}% present)");

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
        $teacher = auth()->user();

        // Verify class belongs to teacher
        $class = ClassModel::where('id', $classId)
            ->where('teacher_id', $teacher->id)
            ->with('teacher')
            ->firstOrFail();

        $schedule = Schedule::findOrFail($scheduleId);
        $attendanceDate = Carbon::parse($date);

        // Get all attendance records for this session
        $attendanceRecords = Attendance::with(['student.parent', 'markedBy'])
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

        // Get marked by and marked at info
        $firstRecord = $attendanceRecords->first();
        $markedBy = $firstRecord ? $firstRecord->markedBy : null;
        $markedAt = $firstRecord ? $firstRecord->created_at : null;

        return view('teacher.attendance.show', compact(
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
        $teacher = auth()->user();

        // Verify class belongs to teacher
        $class = ClassModel::where('id', $classId)
            ->where('teacher_id', $teacher->id)
            ->with('teacher')
            ->firstOrFail();

        $schedule = Schedule::findOrFail($scheduleId);
        $attendanceDate = Carbon::parse($date);

        // Get all attendance records for this session
        $attendanceRecords = Attendance::with('student.parent')
            ->where('class_id', $classId)
            ->where('schedule_id', $scheduleId)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');

        return view('teacher.attendance.edit', compact(
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
        $teacher = auth()->user();

        // Verify class belongs to teacher
        $class = ClassModel::where('id', $classId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        // Custom validation messages
        $messages = [
            'attendance.required' => 'Please mark attendance for at least one student.',
            'attendance.min' => 'Please mark attendance for at least one student.',
            'attendance.*.required' => 'Attendance status is required for all students.',
            'attendance.*.in' => 'Invalid attendance status selected.',
            'notes.*.max' => 'Notes cannot exceed 500 characters.',
        ];

        $validator = Validator::make($request->all(), [
            'attendance' => 'required|array|min:1',
            'attendance.*' => 'required|in:present,absent,late,unauthorized',
            'notes.*' => 'nullable|string|max:500',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Please fix the validation errors below.');
        }

        DB::beginTransaction();
        
        try {
            $updatedCount = 0;
            $newlyAbsent = [];

            foreach ($request->attendance as $studentId => $newStatus) {
                $attendance = Attendance::where('student_id', $studentId)
                    ->where('class_id', $classId)
                    ->where('schedule_id', $scheduleId)
                    ->where('date', $date)
                    ->first();

                if ($attendance) {
                    $oldStatus = $attendance->status;
                    
                    $attendance->update([
                        'status' => $newStatus,
                        'notes' => $request->notes[$studentId] ?? null,
                    ]);
                    
                    $updatedCount++;

                    // Track newly absent students
                    if ($newStatus === 'absent' && $oldStatus !== 'absent') {
                        $newlyAbsent[] = $studentId;
                    }
                }
            }

            // Calculate statistics
            $presentCount = count(array_filter($request->attendance, fn($s) => $s === 'present'));
            $attendanceRate = $updatedCount > 0 ? round(($presentCount / $updatedCount) * 100, 1) : 0;

            // Log activity
            ActivityLog::create([
                'user_id' => $teacher->id,
                'action' => 'updated_attendance',
                'model_type' => 'Attendance',
                'model_id' => null,
                'description' => "Updated attendance for {$updatedCount} students in {$class->name} on {$date}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Notify parents of newly absent students
            if (!empty($newlyAbsent)) {
                $this->notifyAbsentStudents($newlyAbsent, $date, $class);
            }

            return redirect()->route('teacher.attendance.index')
                ->with('success', "Attendance updated successfully for {$updatedCount} students! ({$attendanceRate}% present)");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Attendance update failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to update attendance. Please try again.');
        }
    }

    /**
     * Remove attendance records (delete session)
     */
    public function destroy($date, $classId, $scheduleId)
    {
        $teacher = auth()->user();

        // Verify class belongs to teacher
        $class = ClassModel::where('id', $classId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        try {
            $deletedCount = Attendance::where('class_id', $classId)
                ->where('schedule_id', $scheduleId)
                ->where('date', $date)
                ->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => $teacher->id,
                'action' => 'deleted_attendance',
                'model_type' => 'Attendance',
                'model_id' => null,
                'description' => "Deleted attendance session for {$class->name} on {$date} ({$deletedCount} records)",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('teacher.attendance.index')
                ->with('success', 'Attendance session deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Attendance deletion failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to delete attendance session.');
        }
    }

    /**
     * Calculate statistics for date range
     */
    private function calculateStatistics($dateFrom, $dateTo, $request, $myClassIds)
    {
        $query = Attendance::whereIn('class_id', $myClassIds)
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
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
     * Notify parents of absent students
     */
    private function notifyAbsentStudents(array $studentIds, $date, ClassModel $class)
    {
        try {
            $students = Student::with('parent')
                ->whereIn('id', $studentIds)
                ->get();

            foreach ($students as $student) {
                if (!$student->parent) {
                    \Log::warning("Student {$student->id} has no parent assigned for absence notification");
                    continue;
                }

                NotificationHelper::notifyStudentParent(
                    $student,
                    'Student Absence',
                    "{$student->first_name} was marked absent from {$class->name} on " . Carbon::parse($date)->format('d M Y'),
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
            \Log::error('Failed to send absence notifications: ' . $e->getMessage());
        }
    }
}
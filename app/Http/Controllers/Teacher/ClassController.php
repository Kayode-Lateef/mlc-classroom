<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\HomeworkAssignment;
use App\Models\ProgressSheet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClassController extends Controller
{
    /**
     * Display a listing of teacher's assigned classes (READ-ONLY)
     */
    public function index(Request $request)
    {
        $teacher = auth()->user();

        // Get only classes assigned to this teacher
        $query = ClassModel::where('teacher_id', $teacher->id)
            ->with(['schedules', 'students']);

        // Filter by subject
        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        // Filter by level
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Search by name or room
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('room_number', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $classes = $query->paginate(20);

        // Get unique subjects and levels from teacher's classes
        $subjects = ClassModel::where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('subject');
        
        $levels = ClassModel::where('teacher_id', $teacher->id)
            ->distinct()
            ->whereNotNull('level')
            ->pluck('level');

        // Calculate statistics for each class
        foreach ($classes as $class) {
            // Student statistics
            $totalEnrolled = $class->students()->wherePivot('status', 'active')->count();
            $availableSeats = $class->capacity - $totalEnrolled;
            $utilizationRate = $class->capacity > 0 
                ? round(($totalEnrolled / $class->capacity) * 100, 1) 
                : 0;

            // Attendance statistics (last 30 days)
            $thirtyDaysAgo = now()->subDays(30)->toDateString();
            $classAttendance = Attendance::where('class_id', $class->id)
                ->where('date', '>=', $thirtyDaysAgo)
                ->get();

            $attendanceRate = $classAttendance->count() > 0
                ? round(($classAttendance->where('status', 'present')->count() / $classAttendance->count()) * 100, 1)
                : 0;

            // Homework statistics
            $totalHomework = HomeworkAssignment::where('class_id', $class->id)->count();
            $pendingHomework = HomeworkAssignment::where('class_id', $class->id)
                ->where('due_date', '>', now())
                ->count();

            $class->stats = [
                'total_enrolled' => $totalEnrolled,
                'available_seats' => $availableSeats,
                'utilization_rate' => $utilizationRate,
                'attendance_rate' => $attendanceRate,
                'total_homework' => $totalHomework,
                'pending_homework' => $pendingHomework,
            ];
        }

        // Overall statistics
        $stats = [
            'total_classes' => $classes->total(),
            'total_students' => $classes->sum(function($class) {
                return $class->students()->wherePivot('status', 'active')->count();
            }),
            'average_class_size' => $classes->count() > 0 
                ? round($classes->sum(function($class) {
                    return $class->students()->wherePivot('status', 'active')->count();
                }) / $classes->count(), 1)
                : 0,
        ];

        return view('teacher.classes.index', compact(
            'classes',
            'subjects',
            'levels',
            'stats'
        ));
    }

    /**
     * Display the specified class details (READ-ONLY)
     */
    public function show(ClassModel $class)
    {
        $teacher = auth()->user();

        // Verify this class belongs to this teacher
        if ($class->teacher_id !== $teacher->id) {
            abort(403, 'You do not have permission to view this class.');
        }

        // Load relationships - ONLY ACTIVE enrollments
        $class->load([
            'schedules' => function($query) {
                $query->orderBy('day_of_week')->orderBy('start_time');
            },
            'students' => function($query) {
                $query->wherePivot('status', 'active')
                    ->orderBy('first_name', 'asc')
                    ->orderBy('last_name', 'asc');
            }
        ]);

        // Load each student's parent information
        $class->students->each(function($student) {
            $student->load('parent');
        });

        // ============================================================
        // STUDENT STATISTICS
        // ============================================================
        
        $totalEnrolled = $class->students->count();
        $availableSeats = $class->capacity - $totalEnrolled;
        $utilizationRate = $class->capacity > 0 
            ? round(($totalEnrolled / $class->capacity) * 100, 1) 
            : 0;

        // ============================================================
        // ATTENDANCE STATISTICS
        // ============================================================
        
        // Last 30 days
        $thirtyDaysAgo = now()->subDays(30)->toDateString();
        $today = now()->toDateString();
        
        $classAttendance = Attendance::where('class_id', $class->id)
            ->where('date', '>=', $thirtyDaysAgo)
            ->get();

        $attendanceStats = [
            'total' => $classAttendance->count(),
            'present' => $classAttendance->where('status', 'present')->count(),
            'absent' => $classAttendance->where('status', 'absent')->count(),
            'late' => $classAttendance->where('status', 'late')->count(),
            'rate' => $classAttendance->count() > 0
                ? round(($classAttendance->where('status', 'present')->count() / $classAttendance->count()) * 100, 1)
                : 0,
        ];

        // Today's attendance status
        $todayAttendance = Attendance::where('class_id', $class->id)
            ->whereDate('date', $today)
            ->get();

        $todayAttendanceMarked = $todayAttendance->count() > 0;
        $todayAttendanceStats = [
            'marked' => $todayAttendanceMarked,
            'present' => $todayAttendance->where('status', 'present')->count(),
            'absent' => $todayAttendance->where('status', 'absent')->count(),
            'late' => $todayAttendance->where('status', 'late')->count(),
        ];

        // ============================================================
        // HOMEWORK STATISTICS
        // ============================================================
        
        $allHomework = HomeworkAssignment::where('class_id', $class->id)
            ->with('submissions')
            ->orderBy('due_date', 'desc')
            ->get();

        $homeworkStats = [
            'total' => $allHomework->count(),
            'active' => $allHomework->where('due_date', '>=', now())->count(),
            'overdue' => $allHomework->where('due_date', '<', now())->count(),
            'pending_grading' => 0,
        ];

        // Calculate pending grading
        foreach ($allHomework as $hw) {
            $homeworkStats['pending_grading'] += $hw->submissions->where('status', 'submitted')->count();
        }

        // Recent homework (last 5)
        $recentHomework = $allHomework->take(5);

        // ============================================================
        // PROGRESS SHEET STATISTICS
        // ============================================================
        
        $progressSheets = ProgressSheet::where('class_id', $class->id)
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        $progressStats = [
            'total' => ProgressSheet::where('class_id', $class->id)->count(),
            'this_week' => ProgressSheet::where('class_id', $class->id)
                ->where('date', '>=', now()->startOfWeek())
                ->count(),
            'this_month' => ProgressSheet::where('class_id', $class->id)
                ->whereMonth('date', now()->month)
                ->count(),
        ];

        // ============================================================
        // SCHEDULE INFORMATION
        // ============================================================
        
        // Group schedules by day of week
        $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $schedulesByDay = [];
        
        foreach ($weekDays as $day) {
            $daySchedules = $class->schedules->where('day_of_week', $day)->sortBy('start_time');
            if ($daySchedules->count() > 0) {
                $schedulesByDay[$day] = $daySchedules;
            }
        }

        // ============================================================
        // PER-STUDENT STATISTICS
        // ============================================================
        
        $studentStats = [];
        foreach ($class->students as $student) {
            // Attendance for this student in this class
            $studentAttendance = Attendance::where('student_id', $student->id)
                ->where('class_id', $class->id)
                ->where('date', '>=', $thirtyDaysAgo)
                ->get();

            $studentAttendanceRate = $studentAttendance->count() > 0
                ? round(($studentAttendance->where('status', 'present')->count() / $studentAttendance->count()) * 100, 1)
                : 0;

            // Homework submissions for this student in this class
            $studentHomework = $student->homeworkSubmissions()
                ->whereHas('homeworkAssignment', function($query) use ($class) {
                    $query->where('class_id', $class->id);
                })
                ->get();

            $homeworkCompletionRate = $studentHomework->count() > 0
                ? round(($studentHomework->whereIn('status', ['submitted', 'graded'])->count() / $studentHomework->count()) * 100, 1)
                : 0;

            // Average grade
            $gradedHomework = $studentHomework->where('status', 'graded')->whereNotNull('grade');
            $averageGrade = 0;
            
            if ($gradedHomework->count() > 0) {
                $grades = $gradedHomework->map(function($submission) {
                    if (is_numeric($submission->grade)) {
                        return (float) $submission->grade;
                    }
                    // Convert letter grades to numeric
                    $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                    return $gradeMap[strtoupper(substr($submission->grade, 0, 1))] ?? 0;
                });
                $averageGrade = round($grades->avg(), 1);
            }

            $studentStats[$student->id] = [
                'attendance_rate' => $studentAttendanceRate,
                'homework_completion' => $homeworkCompletionRate,
                'average_grade' => $averageGrade,
            ];
        }

        // Compile all statistics
        $stats = [
            'students' => [
                'total_enrolled' => $totalEnrolled,
                'available_seats' => $availableSeats,
                'utilization_rate' => $utilizationRate,
            ],
            'attendance' => $attendanceStats,
            'today_attendance' => $todayAttendanceStats,
            'homework' => $homeworkStats,
            'progress' => $progressStats,
        ];

        return view('teacher.classes.show', compact(
            'class',
            'stats',
            'recentHomework',
            'progressSheets',
            'schedulesByDay',
            'studentStats'
        ));
    }
}
<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\HomeworkSubmission;
use App\Models\ProgressNote;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentController extends Controller
{
    /**
     * Display a listing of parent's children
     */
    public function index()
    {
        $parent = auth()->user();

        // Get all parent's children with key statistics
        $children = $parent->children()
            ->where('status', 'active')
            ->withCount([
                'classes as enrolled_classes' => function($query) {
                    $query->where('class_enrollments.status', 'active');
                }
            ])
            ->get();

        // Calculate statistics for each child
        foreach ($children as $child) {
            // This month's attendance
            $thisMonthAttendance = Attendance::where('student_id', $child->id)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->get();

            $presentCount = $thisMonthAttendance->where('status', 'present')->count();
            $totalAttendance = $thisMonthAttendance->count();
            $child->attendance_rate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;

            // Pending homework
            $child->pending_homework = HomeworkSubmission::where('student_id', $child->id)
                ->where('status', 'pending')
                ->count();

            // Calculate age
            $child->age = Carbon::parse($child->date_of_birth)->age;
        }

        return view('parent.students.index', compact('children'));
    }

    /**
     * Display detailed child profile
     */
    public function show(Student $student)
    {
        $parent = auth()->user();

        // Verify student belongs to parent
        if ($student->parent_id !== $parent->id) {
            abort(403, 'You do not have permission to view this student.');
        }

        // Load relationships
        $student->load([
            'parent',
            'classes' => function($query) {
                $query->wherePivot('status', 'active')
                      ->with(['teacher', 'schedules']);
            }
        ]);

        // ============================================================
        // SECTION 1: BASIC INFORMATION
        // ============================================================
        $student->age = Carbon::parse($student->date_of_birth)->age;

        // ============================================================
        // SECTION 2: ATTENDANCE STATISTICS
        // ============================================================
        
        // All-time attendance
        $allAttendance = Attendance::where('student_id', $student->id)->get();
        $presentCount = $allAttendance->where('status', 'present')->count();
        $absentCount = $allAttendance->where('status', 'absent')->count();
        $lateCount = $allAttendance->where('status', 'late')->count();
        $totalAttendance = $allAttendance->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;

        // This month's attendance
        $thisMonthAttendance = Attendance::where('student_id', $student->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();
        
        $thisMonthPresent = $thisMonthAttendance->where('status', 'present')->count();
        $thisMonthTotal = $thisMonthAttendance->count();
        $thisMonthRate = $thisMonthTotal > 0 ? round(($thisMonthPresent / $thisMonthTotal) * 100, 1) : 0;

        // ============================================================
        // SECTION 3: HOMEWORK STATISTICS
        // ============================================================
        
        $totalHomework = HomeworkSubmission::where('student_id', $student->id)->count();
        $pendingHomework = HomeworkSubmission::where('student_id', $student->id)
            ->where('status', 'pending')
            ->count();
        $submittedHomework = HomeworkSubmission::where('student_id', $student->id)
            ->whereIn('status', ['submitted', 'late'])
            ->count();
        $gradedHomework = HomeworkSubmission::where('student_id', $student->id)
            ->where('status', 'graded')
            ->count();

        $homeworkCompletionRate = $totalHomework > 0 ? 
            round((($submittedHomework + $gradedHomework) / $totalHomework) * 100, 1) : 0;

        // ============================================================
        // SECTION 4: RECENT GRADES (Last 10 Graded Homework)
        // ============================================================
        
        $recentGrades = HomeworkSubmission::where('student_id', $student->id)
            ->where('status', 'graded')
            ->whereNotNull('grade')
            ->with('homeworkAssignment.class')
            ->orderBy('graded_at', 'desc')
            ->limit(10)
            ->get();

        // Calculate average grade
        $averageGrade = 0;
        if ($recentGrades->count() > 0) {
            $scores = $recentGrades->map(function($submission) {
                // Extract numeric score
                if (is_numeric($submission->grade)) {
                    return (float) $submission->grade;
                }
                // Handle letter grades
                $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                $grade = strtoupper(substr($submission->grade, 0, 1));
                return $gradeMap[$grade] ?? 0;
            });
            $averageGrade = round($scores->avg(), 1);
        }

        // ============================================================
        // SECTION 5: RECENT PROGRESS NOTES (Last 10)
        // ============================================================
        
        $recentProgress = ProgressNote::where('student_id', $student->id)
            ->with(['progressSheet.class', 'progressSheet.teacher'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Count performance levels
        $performanceCounts = [
            'excellent' => $recentProgress->where('performance', 'excellent')->count(),
            'good' => $recentProgress->where('performance', 'good')->count(),
            'average' => $recentProgress->where('performance', 'average')->count(),
            'struggling' => $recentProgress->where('performance', 'struggling')->count(),
            'absent' => $recentProgress->where('performance', 'absent')->count(),
        ];

        // ============================================================
        // SECTION 6: WEEKLY SCHEDULE
        // ============================================================
        
        $weeklySchedule = $this->getWeeklySchedule($student);

        // ============================================================
        // SECTION 7: ENROLLED CLASSES WITH DETAILS
        // ============================================================
        
        $enrolledClasses = $student->classes()
            ->wherePivot('status', 'active')
            ->with(['teacher', 'schedules'])
            ->withPivot('enrollment_date')
            ->get();

        // Add per-class statistics
        foreach ($enrolledClasses as $class) {
            // Class attendance
            $classAttendance = Attendance::where('student_id', $student->id)
                ->where('class_id', $class->id)
                ->get();
            
            $classPresent = $classAttendance->where('status', 'present')->count();
            $classTotal = $classAttendance->count();
            $class->attendance_rate = $classTotal > 0 ? 
                round(($classPresent / $classTotal) * 100, 1) : 0;

            // Class homework
            $classHomework = HomeworkSubmission::where('student_id', $student->id)
                ->whereHas('homeworkAssignment', function($q) use ($class) {
                    $q->where('class_id', $class->id);
                })
                ->get();
            
            $class->total_homework = $classHomework->count();
            $class->completed_homework = $classHomework->whereIn('status', ['submitted', 'late', 'graded'])->count();
        }

        // ============================================================
        // COMPILE ALL STATISTICS
        // ============================================================
        
        $stats = [
            // Attendance
            'attendance_rate' => $attendanceRate,
            'total_attendance' => $totalAttendance,
            'present' => $presentCount,
            'absent' => $absentCount,
            'late' => $lateCount,
            'this_month_rate' => $thisMonthRate,
            'this_month_present' => $thisMonthPresent,
            'this_month_total' => $thisMonthTotal,
            
            // Homework
            'total_homework' => $totalHomework,
            'pending_homework' => $pendingHomework,
            'submitted_homework' => $submittedHomework,
            'graded_homework' => $gradedHomework,
            'homework_completion_rate' => $homeworkCompletionRate,
            'average_grade' => $averageGrade,
            
            // Classes
            'enrolled_classes' => $enrolledClasses->count(),
            
            // Progress
            'total_progress_notes' => $recentProgress->count(),
            'performance_counts' => $performanceCounts,
        ];

        return view('parent.students.show', compact(
            'student',
            'stats',
            'recentGrades',
            'recentProgress',
            'weeklySchedule',
            'enrolledClasses'
        ));
    }

    /**
     * Get weekly schedule for student
     */
    protected function getWeeklySchedule($student)
    {
        $schedule = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($days as $day) {
            $classes = $student->classes()
                ->wherePivot('status', 'active')
                ->with(['schedules' => function($query) use ($day) {
                    $query->where('day_of_week', $day)->orderBy('start_time');
                }, 'teacher'])
                ->get();

            $dayClasses = [];
            foreach ($classes as $class) {
                if ($class->schedules->isNotEmpty()) {
                    foreach ($class->schedules as $schedule_item) {
                        $dayClasses[] = [
                            'class' => $class,
                            'schedule' => $schedule_item,
                        ];
                    }
                }
            }

            // Sort by start time
            usort($dayClasses, function($a, $b) {
                return strcmp($a['schedule']->start_time, $b['schedule']->start_time);
            });

            $schedule[$day] = $dayClasses;
        }

        return $schedule;
    }
}
<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\HomeworkSubmission;
use App\Models\ProgressNote;
use App\Models\HomeworkAssignment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display parent dashboard
     */
    public function index(Request $request)
    {
        $parent = auth()->user();

        // Get parent's children
        $children = $parent->children()->where('status', 'active')->get();

        // If no children, show message
        if ($children->isEmpty()) {
            return view('parent.dashboard', [
                'children' => $children,
                'selectedChild' => null,
                'stats' => null,
                'upcomingHomework' => null,
                'recentProgress' => null,
                'weeklySchedule' => null,
                'attendanceSummary' => null,
                'progressTrendData' => null,
                'subjectComparisonData' => null,
            ]);
        }

        // Select child (default to first child or from request)
        $selectedChildId = $request->get('child_id', $children->first()->id);
        $selectedChild = $children->firstWhere('id', $selectedChildId);

        // ============================================================
        // STATISTICS
        // ============================================================
        $stats = $this->getChildStatistics($selectedChild);

        // ============================================================
        // UPCOMING HOMEWORK (Next 7 Days)
        // ============================================================
        $upcomingHomework = HomeworkSubmission::where('student_id', $selectedChild->id)
            ->with('homeworkAssignment.class')
            ->whereHas('homeworkAssignment', function($query) {
                $query->whereBetween('due_date', [now(), now()->addDays(7)]);
            })
            ->where('status', '!=', 'graded')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ============================================================
        // RECENT PROGRESS NOTES (Last 5)
        // ============================================================
        $recentProgress = ProgressNote::where('student_id', $selectedChild->id)
            ->with(['progressSheet.class', 'progressSheet.teacher'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ============================================================
        // WEEKLY SCHEDULE
        // ============================================================
        $weeklySchedule = $this->getWeeklySchedule($selectedChild);

        // ============================================================
        // ATTENDANCE SUMMARY (This Week)
        // ============================================================
        $attendanceSummary = $this->getAttendanceSummary($selectedChild);

        // ============================================================
        // CHART DATA CALCULATIONS
        // ============================================================
        
        // Chart 1: Progress Trend (Last 4 Months)
        $progressTrendData = $this->getProgressTrendData($selectedChild);
        
        // Chart 2: Subject Comparison (Radar Chart)
        $subjectComparisonData = $this->getSubjectComparisonData($selectedChild);

        // ============================================================
        // RETURN VIEW WITH ALL DATA
        // ============================================================
        
        return view('parent.dashboard', compact(
            'children',
            'selectedChild',
            'stats',
            'upcomingHomework',
            'recentProgress',
            'weeklySchedule',
            'attendanceSummary',
            'progressTrendData',
            'subjectComparisonData'
        ));
    }

    /**
     * Get child statistics
     */
    protected function getChildStatistics($child)
    {
        // This month's attendance
        $thisMonthAttendance = Attendance::where('student_id', $child->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();

        $presentCount = $thisMonthAttendance->where('status', 'present')->count();
        $totalAttendance = $thisMonthAttendance->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;

        // Pending homework
        $pendingHomework = HomeworkSubmission::where('student_id', $child->id)
            ->where('status', 'pending')
            ->count();

        // Enrolled classes
        $enrolledClasses = $child->classes()->wherePivot('status', 'active')->count();

        // Recent grades (last 5 graded homework)
        $recentGrades = HomeworkSubmission::where('student_id', $child->id)
            ->where('status', 'graded')
            ->whereNotNull('grade')
            ->orderBy('graded_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate average of recent grades
        $recentAverage = 0;
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
            $recentAverage = round($scores->avg(), 1);
        }

        return [
            'attendance_rate' => $attendanceRate,
            'present_count' => $presentCount,
            'total_attendance' => $totalAttendance,
            'pending_homework' => $pendingHomework,
            'enrolled_classes' => $enrolledClasses,
            'recent_grades' => $recentGrades,
            'recent_average' => $recentAverage,
        ];
    }

    /**
     * Get weekly schedule for child
     */
    protected function getWeeklySchedule($child)
    {
        $schedule = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($days as $day) {
            $classes = $child->classes()
                ->wherePivot('status', 'active')
                ->with(['schedules' => function($query) use ($day) {
                    $query->where('day_of_week', $day)->orderBy('start_time');
                }, 'teacher'])
                ->get();

            $schedule[$day] = $classes->filter(function($class) {
                return $class->schedules->isNotEmpty();
            });
        }

        return $schedule;
    }

    /**
     * Get attendance summary for this week
     */
    protected function getAttendanceSummary($child)
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $attendance = Attendance::where('student_id', $child->id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->with(['class', 'schedule'])
            ->orderBy('date')
            ->get();

        return [
            'total' => $attendance->count(),
            'present' => $attendance->where('status', 'present')->count(),
            'absent' => $attendance->where('status', 'absent')->count(),
            'late' => $attendance->where('status', 'late')->count(),
            'records' => $attendance,
        ];
    }

    /**
     * Get progress trend data for chart (Last 4 months)
     */
    protected function getProgressTrendData($child)
    {
        $progressTrendData = [
            'labels' => [],
            'data' => []
        ];

        // Get last 4 months
        for ($i = 3; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $progressTrendData['labels'][] = $month->format('F');

            // Get all graded homework for this month
            $monthlySubmissions = HomeworkSubmission::where('student_id', $child->id)
                ->where('status', 'graded')
                ->whereNotNull('grade')
                ->whereMonth('graded_at', $month->month)
                ->whereYear('graded_at', $month->year)
                ->get();

            // Calculate average score for the month
            $monthAverage = 0;
            if ($monthlySubmissions->count() > 0) {
                $scores = $monthlySubmissions->map(function($submission) {
                    if (is_numeric($submission->grade)) {
                        return (float) $submission->grade;
                    }
                    $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                    $grade = strtoupper(substr($submission->grade, 0, 1));
                    return $gradeMap[$grade] ?? 0;
                });
                $monthAverage = round($scores->avg(), 1);
            }

            $progressTrendData['data'][] = $monthAverage;
        }

        return $progressTrendData;
    }

    /**
     * Get subject comparison data for radar chart
     */
    protected function getSubjectComparisonData($child)
    {
        $subjectComparisonData = [
            'labels' => [],
            'studentData' => [],
            'classAverageData' => []
        ];

        // Get child's enrolled classes
        $enrolledClasses = $child->classes()
            ->wherePivot('status', 'active')
            ->with('homeworkAssignments.submissions')
            ->get();

        // Get up to 5 subjects for the radar chart
        foreach ($enrolledClasses->take(5) as $class) {
            $subjectComparisonData['labels'][] = $class->subject;

            // Calculate student's average in this subject
            $studentSubmissions = HomeworkSubmission::where('student_id', $child->id)
                ->whereHas('homeworkAssignment', function($query) use ($class) {
                    $query->where('class_id', $class->id);
                })
                ->where('status', 'graded')
                ->whereNotNull('grade')
                ->get();

            $studentAverage = 0;
            if ($studentSubmissions->count() > 0) {
                $scores = $studentSubmissions->map(function($submission) {
                    if (is_numeric($submission->grade)) {
                        return (float) $submission->grade;
                    }
                    $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                    $grade = strtoupper(substr($submission->grade, 0, 1));
                    return $gradeMap[$grade] ?? 0;
                });
                $studentAverage = round($scores->avg(), 1);
            }

            $subjectComparisonData['studentData'][] = $studentAverage;

            // Calculate class average for this subject
            $allClassSubmissions = HomeworkSubmission::whereHas('homeworkAssignment', function($query) use ($class) {
                    $query->where('class_id', $class->id);
                })
                ->where('status', 'graded')
                ->whereNotNull('grade')
                ->get();

            $classAverage = 0;
            if ($allClassSubmissions->count() > 0) {
                $scores = $allClassSubmissions->map(function($submission) {
                    if (is_numeric($submission->grade)) {
                        return (float) $submission->grade;
                    }
                    $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                    $grade = strtoupper(substr($submission->grade, 0, 1));
                    return $gradeMap[$grade] ?? 0;
                });
                $classAverage = round($scores->avg(), 1);
            }

            $subjectComparisonData['classAverageData'][] = $classAverage;
        }

        return $subjectComparisonData;
    }
}
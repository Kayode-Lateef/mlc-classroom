<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\HomeworkSubmission;
use App\Models\ProgressNote;
use Illuminate\Http\Request;

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
            ]);
        }

        // Select child (default to first child or from request)
        $selectedChildId = $request->get('child_id', $children->first()->id);
        $selectedChild = $children->firstWhere('id', $selectedChildId);

        // Get child's statistics
        $stats = $this->getChildStatistics($selectedChild);

        // Get upcoming homework (next 7 days)
        $upcomingHomework = HomeworkSubmission::where('student_id', $selectedChild->id)
            ->with('homeworkAssignment.class')
            ->whereHas('homeworkAssignment', function($query) {
                $query->whereBetween('due_date', [now(), now()->addDays(7)]);
            })
            ->where('status', '!=', 'graded')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent progress notes (last 5)
        $recentProgress = ProgressNote::where('student_id', $selectedChild->id)
            ->with(['progressSheet.class', 'progressSheet.teacher'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get this week's schedule
        $weeklySchedule = $this->getWeeklySchedule($selectedChild);

        // Get this week's attendance summary
        $attendanceSummary = $this->getAttendanceSummary($selectedChild);

        // Notifications
        $notifications = auth()->user()->unreadNotifications()->limit(5)->get();

        return view('parent.dashboard', compact(
            'children',
            'selectedChild',
            'stats',
            'upcomingHomework',
            'recentProgress',
            'weeklySchedule',
            'attendanceSummary',
            'notifications'
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
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 2) : 0;

        // Pending homework
        $pendingHomework = HomeworkSubmission::where('student_id', $child->id)
            ->where('status', 'pending')
            ->count();

        // Enrolled classes
        $enrolledClasses = $child->classes()->wherePivot('status', 'active')->count();

        // Recent grades (last 5 graded homework)
        $recentGrades = HomeworkSubmission::where('student_id', $child->id)
            ->where('status', 'graded')
            ->orderBy('graded_at', 'desc')
            ->limit(5)
            ->pluck('grade');

        return [
            'attendance_rate' => $attendanceRate,
            'present_count' => $presentCount,
            'total_attendance' => $totalAttendance,
            'pending_homework' => $pendingHomework,
            'enrolled_classes' => $enrolledClasses,
            'recent_grades' => $recentGrades,
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
}
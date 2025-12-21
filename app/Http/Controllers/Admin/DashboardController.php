<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\ClassEnrollment;
use App\Models\HomeworkAssignment;
use App\Models\SmsLog;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        // ============================================================
        // KEY STATISTICS
        // ============================================================
        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::where('status', 'active')->count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_parents' => User::where('role', 'parent')->count(),
            'total_classes' => ClassModel::count(),
            'active_enrollments' => ClassEnrollment::where('status', 'active')->count(),
        ];

        // ============================================================
        // TODAY'S ATTENDANCE SUMMARY
        // ============================================================
        $today = now()->toDateString();
        $attendanceToday = [
            'present' => Attendance::whereDate('date', $today)->where('status', 'present')->count(),
            'absent' => Attendance::whereDate('date', $today)->where('status', 'absent')->count(),
            'late' => Attendance::whereDate('date', $today)->where('status', 'late')->count(),
            'total' => Attendance::whereDate('date', $today)->count(),
        ];

        // Attendance rate calculation
        $attendanceRate = $attendanceToday['total'] > 0 
            ? round(($attendanceToday['present'] / $attendanceToday['total']) * 100, 1) 
            : 0;

        // ============================================================
        // TODAY'S CLASSES
        // ============================================================
        $upcomingClasses = ClassModel::with(['teacher', 'schedules'])
            ->whereHas('schedules', function($query) {
                $query->where('day_of_week', now()->format('l'));
            })
            ->limit(10)
            ->get();

        // Count classes by status (for display purposes)
        $todaysClassesCount = $upcomingClasses->count();

        // ============================================================
        // RECENT ENROLLMENTS (Last 7 days)
        // ============================================================
        $recentEnrollments = ClassEnrollment::with(['student', 'class'])
            ->where('enrollment_date', '>=', now()->subDays(7))
            ->orderBy('enrollment_date', 'desc')
            ->limit(10)
            ->get();

        // ============================================================
        // HOMEWORK STATISTICS (This week)
        // ============================================================
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $homeworkThisWeek = HomeworkAssignment::whereBetween('due_date', [$weekStart, $weekEnd])->count();
        $submittedThisWeek = HomeworkAssignment::whereBetween('due_date', [$weekStart, $weekEnd])
            ->whereHas('submissions', function($query) {
                $query->where('status', '!=', 'pending');
            })
            ->count();
        $homeworkCompletionRate = $homeworkThisWeek > 0 
            ? round(($submittedThisWeek / $homeworkThisWeek) * 100) 
            : 0;

        // Overdue homework
        $overdueHomework = HomeworkAssignment::where('due_date', '<', now())
            ->whereDoesntHave('submissions', function($query) {
                $query->where('status', 'submitted');
            })
            ->count();



        // ============================================================
        // SMS USAGE STATISTICS (This month)
        // ============================================================
        $smsThisMonth = SmsLog::whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->count();
        $smsCostThisMonth = SmsLog::whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->sum('cost');

        // ============================================================
        // RECENT ACTIVITY LOGS (Last 15 activities)
        // ============================================================
        $recentActivity = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        // ============================================================
        // CHART DATA CALCULATIONS
        // ============================================================

        // Chart 1: Weekly Attendance Trend (Last 7 days)
        $weeklyAttendanceChartData = [
            'labels' => [],
            'data' => []
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyAttendanceChartData['labels'][] = $date->format('l'); // Monday, Tuesday, etc.
            
            $dayTotal = Attendance::whereDate('date', $date->toDateString())->count();
            $dayPresent = Attendance::whereDate('date', $date->toDateString())
                ->where('status', 'present')
                ->count();
            
            $weeklyAttendanceChartData['data'][] = $dayTotal > 0 
                ? round(($dayPresent / $dayTotal) * 100, 1) 
                : 0;
        }

        // Chart 2: Class-wise Attendance (Today) - Top 5 classes
        $classwiseAttendanceData = [
            'labels' => [],
            'data' => []
        ];

        // Get today's attendance grouped by class
        $classAttendance = Attendance::with('class')
            ->whereDate('date', $today)
            ->get()
            ->groupBy('class_id');

        // Calculate attendance percentage for each class
        $classAttendanceRates = [];
        foreach ($classAttendance as $classId => $attendances) {
            $class = ClassModel::find($classId);
            if ($class) {
                $total = $attendances->count();
                $present = $attendances->where('status', 'present')->count();
                $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                
                $classAttendanceRates[] = [
                    'name' => $class->name,
                    'rate' => $rate
                ];
            }
        }

        // Sort by rate (lowest first to highlight problem classes) and take top 5
        usort($classAttendanceRates, function($a, $b) {
            return $a['rate'] <=> $b['rate'];
        });
        $classAttendanceRates = array_slice($classAttendanceRates, 0, 5);

        // Prepare chart data
        foreach ($classAttendanceRates as $classData) {
            $classwiseAttendanceData['labels'][] = $classData['name'];
            $classwiseAttendanceData['data'][] = $classData['rate'];
        }

        // If no data, provide sample data
        if (empty($classwiseAttendanceData['labels'])) {
            $classwiseAttendanceData = [
                'labels' => ['No Data'],
                'data' => [0]
            ];
        }

        // ============================================================
        // NOTIFICATIONS & ALERTS (Dynamic)
        // ============================================================
        $notifications = [];

        // Low attendance alert (classes with <80% attendance this week)
        $lowAttendanceClasses = [];
        foreach (ClassModel::with('attendance')->get() as $class) {
            $weekAttendance = $class->attendance()
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->get();
            
            if ($weekAttendance->count() > 0) {
                $weekPresent = $weekAttendance->where('status', 'present')->count();
                $weekRate = round(($weekPresent / $weekAttendance->count()) * 100);
                
                if ($weekRate < 80) {
                    $lowAttendanceClasses[] = [
                        'class' => $class->name,
                        'rate' => $weekRate
                    ];
                }
            }
        }

        if (!empty($lowAttendanceClasses)) {
            foreach ($lowAttendanceClasses as $lac) {
                $notifications[] = [
                    'type' => 'warning',
                    'title' => 'Low Attendance Alert',
                    'message' => "{$lac['class']} has {$lac['rate']}% attendance this week",
                    'time' => '2 hours ago'
                ];
            }
        }



        // Overdue homework alert
        if ($overdueHomework > 0) {
            $notifications[] = [
                'type' => 'danger',
                'title' => "{$overdueHomework} Overdue Homework",
                'message' => 'Students have pending overdue homework submissions',
                'time' => '1 day ago'
            ];
        }

        // ============================================================
        // STUDENT PERFORMANCE DATA (Top performers & struggling students)
        // ============================================================
        $studentPerformance = Student::with(['homeworkSubmissions', 'attendance'])
            ->where('status', 'active')
            ->get()
            ->map(function($student) {
                // Calculate average score from homework
                $avgScore = $student->homeworkSubmissions()
                    ->whereNotNull('grade')
                    ->get()
                    ->avg(function($submission) {
                        // Extract numeric grade (assumes format like "85%", "A", etc.)
                        if (is_numeric($submission->grade)) {
                            return (float) $submission->grade;
                        }
                        // Convert letter grades to numbers
                        $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                        $grade = strtoupper(substr($submission->grade, 0, 1));
                        return $gradeMap[$grade] ?? 0;
                    }) ?? 0;

                // Calculate attendance rate
                $totalAttendance = $student->attendance()->count();
                $presentCount = $student->attendance()->where('status', 'present')->count();
                $attendanceRate = $totalAttendance > 0 
                    ? round(($presentCount / $totalAttendance) * 100) 
                    : 0;

                return [
                    'id' => $student->id,
                    'name' => $student->full_name,
                    'class' => $student->classes->first()->name ?? 'N/A',
                    'avg_score' => round($avgScore, 1),
                    'attendance_rate' => $attendanceRate,
                    'grade' => $this->calculateGrade($avgScore),
                    'status' => $this->getPerformanceStatus($avgScore, $attendanceRate)
                ];
            })
            ->sortByDesc('avg_score')
            ->take(10)
            ->values();

        // ============================================================
        // RETURN VIEW WITH ALL DATA
        // ============================================================
        return view('admin.dashboard', compact(
            'stats',
            'attendanceToday',
            'attendanceRate',
            'todaysClassesCount',
            'upcomingClasses',
            'recentEnrollments',
            'homeworkThisWeek',
            'homeworkCompletionRate',
            'overdueHomework',
            'smsThisMonth',
            'smsCostThisMonth',
            'recentActivity',
            'weeklyAttendanceChartData',
            'classwiseAttendanceData',
            'notifications',
            'studentPerformance'
        ));
    }

    /**
     * Calculate letter grade from numeric score
     */
    private function calculateGrade($score)
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B+';
        if ($score >= 70) return 'C+';
        if ($score >= 60) return 'D';
        return 'F';
    }

    /**
     * Get performance status based on score and attendance
     */
    private function getPerformanceStatus($score, $attendance)
    {
        if ($score >= 85 && $attendance >= 95) return 'Excellent';
        if ($score >= 70 && $attendance >= 85) return 'Good';
        if ($score >= 60 && $attendance >= 75) return 'Average';
        return 'Needs Attention';
    }
}
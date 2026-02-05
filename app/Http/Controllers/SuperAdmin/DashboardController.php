<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\ClassEnrollment;
use App\Models\HomeworkAssignment;
use App\Models\ProgressSheet;
use App\Models\SmsLog;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Models\StudentHourHistory;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display superadmin dashboard
     */
    public function index()
    {
        // ============================================================
        // WEEKLY HOURS & INCOME CALCULATIONS (NEW)
        // ============================================================
        
        // Get hourly rate from system settings
        $hourlyRate = SystemSetting::where('key', 'hourly_rate')->value('value') ?? 50;
        
        // Calculate total weekly hours (only active students)
        $totalWeeklyHours = Student::where('status', 'active')->sum('weekly_hours') ?? 0;
        
        // Calculate income projections
        $weeklyIncome = $totalWeeklyHours * $hourlyRate;
        $monthlyIncome = $weeklyIncome * 4.33; // Average weeks per month
        $annualIncome = $monthlyIncome * 12;
        
        // Average hours per active student
        $avgHoursPerStudent = Student::where('status', 'active')->avg('weekly_hours') ?? 0;
        
        // Students grouped by hour ranges (for chart)
        $studentsByHours = [
            '0.5-2 hrs' => Student::where('status', 'active')->whereBetween('weekly_hours', [0.5, 2.0])->count(),
            '2-5 hrs' => Student::where('status', 'active')->whereBetween('weekly_hours', [2.0, 5.0])->count(),
            '5-10 hrs' => Student::where('status', 'active')->whereBetween('weekly_hours', [5.0, 10.0])->count(),
            '10+ hrs' => Student::where('status', 'active')->where('weekly_hours', '>', 10.0)->count(),
        ];
        
        // Recent hour changes (last 7 days)
        $recentHourChanges = StudentHourHistory::with(['student', 'changedBy'])
            ->whereHas('student')
            ->where('changed_at', '>=', now()->subDays(7))
            ->orderBy('changed_at', 'desc')
            ->limit(5)
            ->get();
        
        // Total hour changes this month
        $hourChangesThisMonth = StudentHourHistory::whereMonth('changed_at', now()->month)
            ->whereYear('changed_at', now()->year)
            ->count();
        
        // ============================================================
        // EXISTING SYSTEM-WIDE STATISTICS
        // ============================================================
        
        $stats = [
            'total_users' => User::count(),
            'total_students' => Student::count(),
            'active_students' => Student::where('status', 'active')->count(),
            'total_superadmins' => User::where('role', 'superadmin')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_parents' => User::where('role', 'parent')->count(),
            'total_classes' => ClassModel::count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'active_enrollments' => ClassEnrollment::where('status', 'active')->count(),
        ];

        // Today's attendance summary
        $today = now()->toDateString();
        $attendanceToday = [
            'present' => Attendance::whereDate('date', $today)->where('status', 'present')->count(),
            'absent' => Attendance::whereDate('date', $today)->where('status', 'absent')->count(),
            'late' => Attendance::whereDate('date', $today)->where('status', 'late')->count(),
            'total' => Attendance::whereDate('date', $today)->count(),
        ];

        // Recent user registrations (last 30 days)
        $recentUsers = User::where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent enrollments (last 7 days)
        $recentEnrollments = ClassEnrollment::with(['student', 'class'])
            ->where('enrollment_date', '>=', now()->subDays(7))
            ->orderBy('enrollment_date', 'desc')
            ->limit(10)
            ->get();

        // Recent progress sheets (last 7 days)
        $recentProgressSheets = ProgressSheet::with(['class', 'teacher', 'progressNotes'])
            ->where('date', '>=', now()->subDays(7))
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        // Homework completion rate (this week)
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $homeworkThisWeek = HomeworkAssignment::whereBetween('due_date', [$weekStart, $weekEnd])->count();
        $submittedThisWeek = HomeworkAssignment::whereBetween('due_date', [$weekStart, $weekEnd])
            ->whereHas('submissions', function($query) {
                $query->where('status', '!=', 'pending');
            })
            ->count();
        $homeworkCompletionRate = $homeworkThisWeek > 0 ? round(($submittedThisWeek / $homeworkThisWeek) * 100) : 0;

        // SMS usage statistics (this month)
        $smsThisMonth = SmsLog::whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->count();
        $smsCostThisMonth = SmsLog::whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->sum('cost');

        // SMS usage comparison (last month vs this month)
        $smsLastMonth = SmsLog::whereMonth('sent_at', now()->subMonth()->month)
            ->whereYear('sent_at', now()->subMonth()->year)
            ->count();

        // System activity logs (more detailed for superadmin)
        $recentActivity = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Critical system events (failed logins, permission changes, etc.)
        $criticalActivity = ActivityLog::with('user')
            ->whereIn('action', [
                'deleted_user',
                'assigned_role',
                'assigned_permissions',
                'updated_role',
                'deleted_role',
                'updated_permission',
                'deleted_permission',
                'user_status_changed',
                'updated_student_hours' // NEW: Track hour changes
            ])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // ============================================================
        // CHART DATA CALCULATIONS
        // ============================================================

        // Chart Data: Enrollment Trend (Last 6 months)
        $enrollmentChartData = [
            'labels' => [],
            'students' => [],
            'teachers' => []
        ];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $enrollmentChartData['labels'][] = $month->format('M');
            
            $enrollmentChartData['students'][] = Student::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            
            $enrollmentChartData['teachers'][] = User::where('role', 'teacher')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // Chart Data: User Distribution (Current totals)
        $userDistributionData = [
            'labels' => ['Students', 'Teachers', 'Parents', 'Admins'],
            'data' => [
                $stats['total_students'],
                $stats['total_teachers'],
                $stats['total_parents'],
                $stats['total_admins'] + $stats['total_superadmins']
            ]
        ];

        // Chart Data: Weekly Attendance (Last 7 days)
        $weeklyAttendanceData = [
            'labels' => [],
            'data' => []
        ];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyAttendanceData['labels'][] = $date->format('D');
            
            $dayTotal = Attendance::whereDate('date', $date->toDateString())->count();
            $dayPresent = Attendance::whereDate('date', $date->toDateString())
                ->where('status', 'present')
                ->count();
            
            $weeklyAttendanceData['data'][] = $dayTotal > 0 
                ? round(($dayPresent / $dayTotal) * 100, 1) 
                : 0;
        }

        // Calculate attendance rate for today
        $attendanceRate = $attendanceToday['total'] > 0 
            ? round(($attendanceToday['present'] / $attendanceToday['total']) * 100, 1) 
            : 0;

        // ============================================================
        // NEW: WEEKLY HOURS TREND CHART (Last 6 months)
        // ============================================================
        
        $weeklyHoursTrendData = [
            'labels' => [],
            'hours' => [],
            'income' => []
        ];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $weeklyHoursTrendData['labels'][] = $month->format('M');
            
            // Get average weekly hours for active students in that month
            $avgHours = Student::where('status', 'active')
                ->whereYear('created_at', '<=', $month->year)
                ->whereMonth('created_at', '<=', $month->month)
                ->avg('weekly_hours') ?? 0;
            
            $weeklyHoursTrendData['hours'][] = round($avgHours, 1);
            
            // Calculate projected monthly income for that period
            $totalHours = Student::where('status', 'active')
                ->whereYear('created_at', '<=', $month->year)
                ->whereMonth('created_at', '<=', $month->month)
                ->sum('weekly_hours') ?? 0;
            
            $monthlyIncomeForPeriod = ($totalHours * $hourlyRate * 4.33);
            $weeklyHoursTrendData['income'][] = round($monthlyIncomeForPeriod, 2);
        }

        // ============================================================
        // NEW: INCOME BREAKDOWN BY STUDENT HOUR RANGES
        // ============================================================
        
        $incomeByHourRange = [
            'labels' => array_keys($studentsByHours),
            'data' => []
        ];
        
        foreach ($studentsByHours as $range => $count) {
            // Calculate average hours for each range
            if ($range === '0.5-2 hrs') {
                $rangeIncome = Student::where('status', 'active')
                    ->whereBetween('weekly_hours', [0.5, 2.0])
                    ->sum('weekly_hours') * $hourlyRate * 4.33;
            } elseif ($range === '2-5 hrs') {
                $rangeIncome = Student::where('status', 'active')
                    ->whereBetween('weekly_hours', [2.0, 5.0])
                    ->sum('weekly_hours') * $hourlyRate * 4.33;
            } elseif ($range === '5-10 hrs') {
                $rangeIncome = Student::where('status', 'active')
                    ->whereBetween('weekly_hours', [5.0, 10.0])
                    ->sum('weekly_hours') * $hourlyRate * 4.33;
            } else { // 10+ hrs
                $rangeIncome = Student::where('status', 'active')
                    ->where('weekly_hours', '>', 10.0)
                    ->sum('weekly_hours') * $hourlyRate * 4.33;
            }
            
            $incomeByHourRange['data'][] = round($rangeIncome, 2);
        }

        // ============================================================
        // RETURN VIEW WITH ALL DATA
        // ============================================================

        return view('superadmin.dashboard', compact(
            // Existing data
            'stats',
            'attendanceToday',
            'attendanceRate',
            'recentUsers',
            'recentEnrollments',
            'recentProgressSheets',
            'homeworkCompletionRate',
            'smsThisMonth',
            'smsLastMonth',
            'smsCostThisMonth',
            'recentActivity',
            'criticalActivity',
            'enrollmentChartData',
            'userDistributionData',
            'weeklyAttendanceData',
            
            // NEW: Weekly hours & income data
            'hourlyRate',
            'totalWeeklyHours',
            'weeklyIncome',
            'monthlyIncome',
            'annualIncome',
            'avgHoursPerStudent',
            'studentsByHours',
            'recentHourChanges',
            'hourChangesThisMonth',
            'weeklyHoursTrendData',
            'incomeByHourRange'
        ));
    }
}
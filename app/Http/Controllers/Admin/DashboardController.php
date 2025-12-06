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
        // Get key statistics
        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::where('status', 'active')->count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_parents' => User::where('role', 'parent')->count(),
            'total_classes' => ClassModel::count(),
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

        // Attendance rate calculation
        $attendanceRate = $attendanceToday['total'] > 0 
            ? round(($attendanceToday['present'] / $attendanceToday['total']) * 100) 
            : 0;

        // Recent enrollments (last 7 days)
        $recentEnrollments = ClassEnrollment::with(['student', 'class'])
            ->where('enrollment_date', '>=', now()->subDays(7))
            ->orderBy('enrollment_date', 'desc')
            ->limit(10)
            ->get();

        // Upcoming classes today
        $upcomingClasses = ClassModel::with(['teacher', 'schedules'])
            ->whereHas('schedules', function($query) {
                $query->where('day_of_week', now()->format('l'));
            })
            ->limit(10)
            ->get();

        // Homework statistics (this week)
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

        // SMS usage statistics (this month)
        $smsThisMonth = SmsLog::whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->count();
        $smsCostThisMonth = SmsLog::whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->sum('cost');

        // Recent activity logs
        $recentActivity = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'attendanceToday',
            'attendanceRate',
            'recentEnrollments',
            'upcomingClasses',
            'homeworkThisWeek',
            'homeworkCompletionRate',
            'overdueHomework',
            'smsThisMonth',
            'smsCostThisMonth',
            'recentActivity'
        ));
    }
}
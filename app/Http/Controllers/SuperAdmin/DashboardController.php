<?php

namespace App\Http\Controllers\SuperAdmin;

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
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    /**
     * Display superadmin dashboard
     */
    public function index()
    {
        // System-wide statistics
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
                'user_status_changed'
            ])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('superadmin.dashboard', compact(
            'stats',
            'attendanceToday',
            'recentUsers',
            'recentEnrollments',
            'homeworkCompletionRate',
            'smsThisMonth',
            'smsLastMonth',
            'smsCostThisMonth',
            'recentActivity',
            'criticalActivity'
        ));
    }
}
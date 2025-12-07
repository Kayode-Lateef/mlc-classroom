<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\HomeworkSubmission;
use App\Models\Schedule;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display teacher dashboard
     */
    public function index()
    {
        $teacher = auth()->user();

        // Today's classes with schedules
        $todayClasses = ClassModel::where('teacher_id', $teacher->id)
            ->with(['schedules' => function($query) {
                $query->where('day_of_week', now()->format('l'));
            }])
            ->get();

        // Pending homework to grade
        $pendingHomework = HomeworkSubmission::whereHas('homeworkAssignment', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->where('status', 'submitted')
            ->with(['student', 'homeworkAssignment'])
            ->orderBy('submitted_date', 'desc')
            ->limit(10)
            ->get();

        // Recent student progress (last 5 progress sheets)
        $recentProgress = \App\Models\ProgressSheet::where('teacher_id', $teacher->id)
            ->with('class')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // Upcoming classes this week
        $upcomingClasses = Schedule::whereHas('class', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->with('class')
            ->where('day_of_week', '>=', now()->format('l'))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        // Quick stats
        // $stats = [
        //     'total_classes' => ClassModel::where('teacher_id', $teacher->id)->count(),
        //     'total_students' => ClassModel::where('teacher_id', $teacher->id)
        //         ->withCount('students')
        //         ->get()
        //         ->sum('students_count'),
        //     'pending_grading' => HomeworkSubmission::whereHas('homeworkAssignment', function($query) use ($teacher) {
        //             $query->where('teacher_id', $teacher->id);
        //         })
        //         ->where('status', 'submitted')
        //         ->count(),
        // ];

        // Notifications
        // $notifications = auth()->user()->unreadNotifications()->limit(5)->get();

        return view('teacher.dashboard', compact(
            'todayClasses',
            'pendingHomework',
            'recentProgress',
            'upcomingClasses',
            // 'stats',
            // 'notifications'
        ));
    }
}
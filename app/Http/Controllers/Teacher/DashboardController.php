<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\HomeworkSubmission;
use App\Models\HomeworkAssignment;
use App\Models\Schedule;
use App\Models\ProgressSheet;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display teacher dashboard
     */
    public function index()
    {
        $teacher = auth()->user();

        // ============================================================
        // KEY STATISTICS
        // ============================================================
        
        // Get teacher's classes
        $myClasses = ClassModel::where('teacher_id', $teacher->id)->get();
        $myClassIds = $myClasses->pluck('id');
        
        // Total students across all classes
        $totalStudents = 0;
        foreach ($myClasses as $class) {
            $totalStudents += $class->enrollments()->where('status', 'active')->count();
        }

        $stats = [
            'total_classes' => $myClasses->count(),
            'total_students' => $totalStudents,
            'pending_grading' => HomeworkSubmission::whereHas('homeworkAssignment', function($query) use ($teacher) {
                    $query->where('teacher_id', $teacher->id);
                })
                ->where('status', 'submitted')
                ->count(),
            'active_assignments' => HomeworkAssignment::where('teacher_id', $teacher->id)
                ->where('due_date', '>=', now())
                ->count(),
        ];

        // ============================================================
        // TODAY'S OVERVIEW
        // ============================================================
        
        $today = now()->toDateString();
        $dayOfWeek = now()->format('l'); // e.g., "Monday"
        
        // Classes today
        $todaysClasses = ClassModel::where('teacher_id', $teacher->id)
            ->whereHas('schedules', function($query) use ($dayOfWeek) {
                $query->where('day_of_week', $dayOfWeek);
            })
            ->with(['schedules' => function($query) use ($dayOfWeek) {
                $query->where('day_of_week', $dayOfWeek);
            }])
            ->get();
        
        $classesTodayCount = $todaysClasses->count();
        
        // Today's attendance rate (across all teacher's classes)
        $todayAttendance = Attendance::whereDate('date', $today)
            ->whereIn('class_id', $myClassIds)
            ->get();
        
        $todayAttendanceRate = $todayAttendance->count() > 0 
            ? round(($todayAttendance->where('status', 'present')->count() / $todayAttendance->count()) * 100, 1)
            : 0;
        
        // Absent students today
        $absentStudentsToday = Attendance::whereDate('date', $today)
            ->whereIn('class_id', $myClassIds)
            ->where('status', 'absent')
            ->count();
        
        // Assignments due this week
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $dueThisWeek = HomeworkAssignment::where('teacher_id', $teacher->id)
            ->whereBetween('due_date', [$weekStart, $weekEnd])
            ->count();

        // ============================================================
        // TODAY'S SCHEDULE (Detailed)
        // ============================================================
        
        $todaysSchedule = [];
        foreach ($todaysClasses as $class) {
            foreach ($class->schedules as $schedule) {
                $startTime = Carbon::parse($schedule->start_time);
                $endTime = Carbon::parse($schedule->end_time);
                $now = now();
                
                // Determine status
                if ($endTime->lt($now)) {
                    $status = 'completed';
                } elseif ($startTime->lte($now) && $endTime->gt($now)) {
                    $status = 'in_progress';
                } else {
                    $status = 'upcoming';
                }
                
                // Get attendance for this class today (if completed)
                $attendance = [];
                if ($status === 'completed') {
                    $attendanceRecords = Attendance::whereDate('date', $today)
                        ->where('class_id', $class->id)
                        ->where('schedule_id', $schedule->id)
                        ->get();
                    
                    $attendance = [
                        'total' => $attendanceRecords->count(),
                        'present' => $attendanceRecords->where('status', 'present')->count(),
                        'rate' => $attendanceRecords->count() > 0 
                            ? round(($attendanceRecords->where('status', 'present')->count() / $attendanceRecords->count()) * 100)
                            : 0
                    ];
                }
                
                $todaysSchedule[] = [
                    'class' => $class,
                    'schedule' => $schedule,
                    'status' => $status,
                    'attendance' => $attendance,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ];
            }
        }
        
        // Sort by start time
        usort($todaysSchedule, function($a, $b) {
            return $a['start_time']->timestamp <=> $b['start_time']->timestamp;
        });

        // ============================================================
        // PENDING HOMEWORK TO GRADE
        // ============================================================
        
        $pendingHomework = HomeworkSubmission::whereHas('homeworkAssignment', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->where('status', 'submitted')
            ->with(['student', 'homeworkAssignment.class'])
            ->orderBy('submitted_date', 'asc')
            ->limit(20)
            ->get();
        
        // Group by assignment
        $pendingByAssignment = [];
        foreach ($pendingHomework as $submission) {
            $assignmentId = $submission->homeworkAssignment->id;
            if (!isset($pendingByAssignment[$assignmentId])) {
                $pendingByAssignment[$assignmentId] = [
                    'assignment' => $submission->homeworkAssignment,
                    'total_submitted' => 0,
                    'total_graded' => 0,
                ];
            }
            $pendingByAssignment[$assignmentId]['total_submitted']++;
        }
        
        // Get graded count for each assignment
        foreach ($pendingByAssignment as $assignmentId => &$data) {
            $data['total_graded'] = HomeworkSubmission::where('homework_assignment_id', $assignmentId)
                ->where('status', 'graded')
                ->count();
        }

        // ============================================================
        // STUDENT PERFORMANCE (Recent)
        // ============================================================
        
        $recentStudentPerformance = [];
        
        // Get students from teacher's classes
        $studentsInClasses = [];
        foreach ($myClasses as $class) {
            $students = $class->enrollments()
                ->where('status', 'active')
                ->with('student')
                ->get();
            
            foreach ($students as $enrollment) {
                $student = $enrollment->student;
                if (!isset($studentsInClasses[$student->id])) {
                    // Calculate student's average score in teacher's assignments
                    $submissions = HomeworkSubmission::whereHas('homeworkAssignment', function($query) use ($teacher) {
                            $query->where('teacher_id', $teacher->id);
                        })
                        ->where('student_id', $student->id)
                        ->whereNotNull('grade')
                        ->get();
                    
                    $avgScore = 0;
                    $lastTestScore = 0;
                    
                    if ($submissions->count() > 0) {
                        $scores = $submissions->map(function($sub) {
                            // Extract numeric score
                            if (is_numeric($sub->grade)) {
                                return (float) $sub->grade;
                            }
                            // Handle letter grades
                            $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                            $grade = strtoupper(substr($sub->grade, 0, 1));
                            return $gradeMap[$grade] ?? 0;
                        });
                        
                        $avgScore = round($scores->avg(), 1);
                        $lastTestScore = $scores->last();
                    }
                    
                    // Calculate attendance rate
                    $studentAttendance = Attendance::where('student_id', $student->id)
                        ->whereIn('class_id', $myClassIds)
                        ->get();
                    
                    $attendanceRate = $studentAttendance->count() > 0
                        ? round(($studentAttendance->where('status', 'present')->count() / $studentAttendance->count()) * 100)
                        : 0;
                    
                    $studentsInClasses[$student->id] = [
                        'student' => $student,
                        'class' => $class->name,
                        'last_test' => $lastTestScore,
                        'avg_score' => $avgScore,
                        'attendance_rate' => $attendanceRate,
                    ];
                }
            }
        }
        
        // Sort by last test score and take top 5
        $recentStudentPerformance = collect($studentsInClasses)
            ->sortByDesc('last_test')
            ->take(5)
            ->values();

        // ============================================================
        // STUDENTS NEEDING ATTENTION
        // ============================================================
        
        $studentsNeedingAttention = collect($studentsInClasses)
            ->filter(function($data) {
                return $data['avg_score'] < 70 || $data['attendance_rate'] < 80;
            })
            ->sortBy('avg_score')
            ->take(10)
            ->values();

        // ============================================================
        // RECENT SUBMISSIONS
        // ============================================================
        
        $recentSubmissions = HomeworkSubmission::whereHas('homeworkAssignment', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->with(['student', 'homeworkAssignment.class'])
            ->orderBy('submitted_date', 'desc')
            ->limit(10)
            ->get();

        // ============================================================
        // CHART DATA CALCULATIONS
        // ============================================================
        
        // Chart 1: Class Performance (Average scores per class)
        $classPerformanceData = [
            'labels' => [],
            'data' => []
        ];
        
        foreach ($myClasses->take(5) as $class) {
            $classPerformanceData['labels'][] = $class->name;
            
            // Get average score for this class
            $submissions = HomeworkSubmission::whereHas('homeworkAssignment', function($query) use ($teacher, $class) {
                    $query->where('teacher_id', $teacher->id)
                          ->where('class_id', $class->id);
                })
                ->whereNotNull('grade')
                ->get();
            
            $avgScore = 0;
            if ($submissions->count() > 0) {
                $scores = $submissions->map(function($sub) {
                    if (is_numeric($sub->grade)) {
                        return (float) $sub->grade;
                    }
                    $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                    $grade = strtoupper(substr($sub->grade, 0, 1));
                    return $gradeMap[$grade] ?? 0;
                });
                $avgScore = round($scores->avg(), 1);
            }
            
            $classPerformanceData['data'][] = $avgScore;
        }
        
        // Chart 2: Attendance Trend (Last 4 weeks)
        $attendanceTrendData = [
            'labels' => [],
            'data' => []
        ];
        
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            
            $attendanceTrendData['labels'][] = 'Week ' . (4 - $i);
            
            $weekAttendance = Attendance::whereBetween('date', [$weekStart, $weekEnd])
                ->whereIn('class_id', $myClassIds)
                ->get();
            
            $weekRate = $weekAttendance->count() > 0
                ? round(($weekAttendance->where('status', 'present')->count() / $weekAttendance->count()) * 100, 1)
                : 0;
            
            $attendanceTrendData['data'][] = $weekRate;
        }

        // ============================================================
        // RETURN VIEW WITH ALL DATA
        // ============================================================
        
        return view('teacher.dashboard', compact(
            'stats',
            'classesTodayCount',
            'todayAttendanceRate',
            'absentStudentsToday',
            'dueThisWeek',
            'todaysSchedule',
            'pendingByAssignment',
            'recentStudentPerformance',
            'studentsNeedingAttention',
            'recentSubmissions',
            'classPerformanceData',
            'attendanceTrendData'
        ));
    }
}
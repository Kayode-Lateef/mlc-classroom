<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\HomeworkAssignment;
use App\Models\HomeworkSubmission;
use App\Models\ProgressSheet;
use App\Models\ProgressNote;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use PDF; // Barryvdh\DomPDF\Facade\Pdf
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceReportExport;
use App\Exports\StudentReportExport;
use App\Exports\ClassReportExport;
use App\Exports\HomeworkReportExport;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        // Statistics
        $stats = [
            'total_students' => Student::where('status', 'active')->count(),
            'total_classes' => ClassModel::count(),
            'attendance_rate' => $this->calculateOverallAttendanceRate(),
            'homework_completion' => $this->calculateOverallHomeworkCompletion(),
        ];

        // Recent reports (placeholder - will store in database if needed)
        $recentReports = collect([
            [
                'type' => 'Attendance Report',
                'generated_by' => 'Super Admin',
                'date' => now()->subDays(2),
                'format' => 'PDF',
            ],
            [
                'type' => 'Student Performance',
                'generated_by' => 'Super Admin',
                'date' => now()->subDays(5),
                'format' => 'Excel',
            ],
        ]);

        // Reports by type (last 6 months)
        $reportsByType = [
            'labels' => ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'attendance' => [12, 15, 18, 22, 25, 28],
            'students' => [8, 10, 12, 15, 18, 20],
            'classes' => [5, 7, 9, 11, 13, 15],
            'homework' => [10, 12, 14, 16, 18, 20],
        ];

        return view('superadmin.reports.index', compact('stats', 'recentReports', 'reportsByType'));
    }

    /**
     * Attendance reports page
     */
    public function attendance(Request $request)
    {
        $query = Attendance::with(['student', 'class', 'markedBy']);

        // Date range filter
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $query->whereBetween('date', [$dateFrom, $dateTo]);

        // Class filter
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Student filter
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendanceRecords = $query->orderBy('date', 'desc')->paginate(20);

        // Statistics
        $totalRecords = $query->count();
        $presentCount = Attendance::whereBetween('date', [$dateFrom, $dateTo])
            ->where('status', 'present')
            ->count();
        $absentCount = Attendance::whereBetween('date', [$dateFrom, $dateTo])
            ->where('status', 'absent')
            ->count();
        $lateCount = Attendance::whereBetween('date', [$dateFrom, $dateTo])
            ->where('status', 'late')
            ->count();
        $attendanceRate = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 1) : 0;

        $stats = [
            'total' => $totalRecords,
            'present' => $presentCount,
            'absent' => $absentCount,
            'late' => $lateCount,
            'attendance_rate' => $attendanceRate,
        ];

        // Attendance trend (last 30 days)
        $attendanceTrend = $this->getAttendanceTrend($dateFrom, $dateTo);

        // Status distribution
        $statusDistribution = [
            'labels' => ['Present', 'Absent', 'Late', 'Unauthorized'],
            'data' => [
                $presentCount,
                $absentCount,
                $lateCount,
                Attendance::whereBetween('date', [$dateFrom, $dateTo])
                    ->where('status', 'unauthorized')
                    ->count(),
            ],
        ];

        // Get filter options
        $classes = ClassModel::orderBy('name')->get();
        $students = Student::where('status', 'active')->orderBy('first_name')->get();

        return view('superadmin.reports.attendance', compact(
            'attendanceRecords',
            'stats',
            'attendanceTrend',
            'statusDistribution',
            'classes',
            'students',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Student reports page
     */
    public function students(Request $request)
    {
        $selectedStudentId = $request->get('student_id');
        $dateFrom = $request->get('date_from', now()->subDays(90)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $students = Student::where('status', 'active')->orderBy('first_name')->get();
        $classes = ClassModel::orderBy('name')->get();

        $studentData = null;
        $attendanceData = null;
        $homeworkData = null;
        $progressData = null;
        $charts = null;

        if ($selectedStudentId) {
            $student = Student::with(['parent', 'enrollments.class'])->findOrFail($selectedStudentId);

            // Attendance data
            $attendanceRecords = Attendance::where('student_id', $selectedStudentId)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->with('class')
                ->orderBy('date', 'desc')
                ->get();

            $totalAttendance = $attendanceRecords->count();
            $presentCount = $attendanceRecords->where('status', 'present')->count();
            $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;

            $attendanceData = [
                'records' => $attendanceRecords->take(10),
                'total' => $totalAttendance,
                'present' => $presentCount,
                'absent' => $attendanceRecords->where('status', 'absent')->count(),
                'late' => $attendanceRecords->where('status', 'late')->count(),
                'rate' => $attendanceRate,
            ];

            // Homework data
            $homeworkSubmissions = HomeworkSubmission::where('student_id', $selectedStudentId)
                ->with('homeworkAssignment.class')
                ->orderBy('created_at', 'desc')
                ->get();

            $totalHomework = $homeworkSubmissions->count();
            $submittedCount = $homeworkSubmissions->whereIn('status', ['submitted', 'graded'])->count();
            $gradedCount = $homeworkSubmissions->where('status', 'graded')->count();
            $homeworkRate = $totalHomework > 0 ? round(($submittedCount / $totalHomework) * 100, 1) : 0;

            // Calculate average grade
            $averageGrade = 0;
            $gradedSubmissions = $homeworkSubmissions->where('status', 'graded')->whereNotNull('grade');
            if ($gradedSubmissions->count() > 0) {
                $grades = $gradedSubmissions->map(function($sub) {
                    if (is_numeric($sub->grade)) {
                        return (float) $sub->grade;
                    }
                    $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                    return $gradeMap[strtoupper(substr($sub->grade, 0, 1))] ?? 0;
                });
                $averageGrade = round($grades->avg(), 1);
            }

            $homeworkData = [
                'submissions' => $homeworkSubmissions->take(10),
                'total' => $totalHomework,
                'submitted' => $submittedCount,
                'graded' => $gradedCount,
                'rate' => $homeworkRate,
                'average_grade' => $averageGrade,
            ];

            // Progress notes
            $progressNotes = ProgressNote::where('student_id', $selectedStudentId)
                ->with('progressSheet.class')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            $progressData = [
                'notes' => $progressNotes,
                'count' => $progressNotes->count(),
            ];

            // Charts data
            $charts = [
                'attendance_trend' => $this->getStudentAttendanceTrend($selectedStudentId, $dateFrom, $dateTo),
                'grade_progression' => $this->getStudentGradeProgression($selectedStudentId),
            ];

            $studentData = $student;
        }

        return view('superadmin.reports.students', compact(
            'students',
            'classes',
            'studentData',
            'attendanceData',
            'homeworkData',
            'progressData',
            'charts',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Class reports page
     */
    public function classes(Request $request)
    {
        $selectedClassId = $request->get('class_id');
        $dateFrom = $request->get('date_from', now()->subDays(90)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $classes = ClassModel::with('teacher')->orderBy('name')->get();

        $classData = null;
        $studentStats = null;
        $attendanceStats = null;
        $homeworkStats = null;
        $charts = null;

        if ($selectedClassId) {
            $class = ClassModel::with(['teacher', 'students'])->findOrFail($selectedClassId);

            // Student statistics
            $totalStudents = $class->students()->wherePivot('status', 'active')->count();
            $activeStudents = $class->students()->wherePivot('status', 'active')->count();

            // Attendance statistics
            $classAttendance = Attendance::where('class_id', $selectedClassId)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->get();

            $totalAttendance = $classAttendance->count();
            $presentCount = $classAttendance->where('status', 'present')->count();
            $classAttendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;

            $attendanceStats = [
                'total' => $totalAttendance,
                'present' => $presentCount,
                'absent' => $classAttendance->where('status', 'absent')->count(),
                'late' => $classAttendance->where('status', 'late')->count(),
                'rate' => $classAttendanceRate,
            ];

            // Homework statistics
            $classHomework = HomeworkAssignment::where('class_id', $selectedClassId)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->withCount('submissions')
                ->get();

            $totalAssignments = $classHomework->count();
            $totalSubmissions = HomeworkSubmission::whereHas('homeworkAssignment', function($q) use ($selectedClassId) {
                $q->where('class_id', $selectedClassId);
            })->count();

            $homeworkStats = [
                'total_assignments' => $totalAssignments,
                'total_submissions' => $totalSubmissions,
                'average_completion' => $totalAssignments > 0 ? round(($totalSubmissions / ($totalAssignments * $totalStudents)) * 100, 1) : 0,
            ];

            // Student performance list
            $studentStats = $class->students()
                ->wherePivot('status', 'active')
                ->get()
                ->map(function($student) use ($dateFrom, $dateTo) {
                    // Attendance rate
                    $studentAttendance = $student->attendance()
                        ->whereBetween('date', [$dateFrom, $dateTo])
                        ->get();
                    $totalAtt = $studentAttendance->count();
                    $presentAtt = $studentAttendance->where('status', 'present')->count();
                    $attRate = $totalAtt > 0 ? round(($presentAtt / $totalAtt) * 100, 1) : 0;

                    // Homework completion
                    $studentHomework = $student->homeworkSubmissions()->count();
                    $submittedHomework = $student->homeworkSubmissions()
                        ->whereIn('status', ['submitted', 'graded'])
                        ->count();
                    $hwRate = $studentHomework > 0 ? round(($submittedHomework / $studentHomework) * 100, 1) : 0;

                    // Average grade
                    $gradedSubmissions = $student->homeworkSubmissions()
                        ->where('status', 'graded')
                        ->whereNotNull('grade')
                        ->get();
                    
                    $avgGrade = 0;
                    if ($gradedSubmissions->count() > 0) {
                        $grades = $gradedSubmissions->map(function($sub) {
                            if (is_numeric($sub->grade)) return (float) $sub->grade;
                            $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                            return $gradeMap[strtoupper(substr($sub->grade, 0, 1))] ?? 0;
                        });
                        $avgGrade = round($grades->avg(), 1);
                    }

                    return [
                        'student' => $student,
                        'attendance_rate' => $attRate,
                        'homework_rate' => $hwRate,
                        'average_grade' => $avgGrade,
                    ];
                })
                ->sortByDesc('average_grade')
                ->values();

            // Charts
            $charts = [
                'performance_trend' => $this->getClassPerformanceTrend($selectedClassId, $dateFrom, $dateTo),
                'student_distribution' => $this->getClassStudentDistribution($selectedClassId),
            ];

            $classData = $class;
        }

        return view('superadmin.reports.classes', compact(
            'classes',
            'classData',
            'studentStats',
            'attendanceStats',
            'homeworkStats',
            'charts',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Homework reports page
     */
    public function homework(Request $request)
    {
        $query = HomeworkAssignment::with(['class', 'teacher']);

        // Date range filter
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $query->whereBetween('due_date', [$dateFrom, $dateTo]);

        // Class filter
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Teacher filter
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('due_date', '<', now())->whereHas('submissions', function($q) {
                    $q->where('status', 'pending');
                });
            }
        }

        $homeworkAssignments = $query->orderBy('due_date', 'desc')->paginate(20);

        // Calculate statistics for each assignment
        $homeworkAssignments->getCollection()->transform(function($homework) {
            $totalStudents = $homework->class->students()->wherePivot('status', 'active')->count();
            $submissions = $homework->submissions;
            $submittedCount = $submissions->whereIn('status', ['submitted', 'graded'])->count();
            $gradedCount = $submissions->where('status', 'graded')->count();
            
            $homework->total_students = $totalStudents;
            $homework->submitted_count = $submittedCount;
            $homework->graded_count = $gradedCount;
            $homework->completion_rate = $totalStudents > 0 ? round(($submittedCount / $totalStudents) * 100, 1) : 0;
            
            // Average grade
            $gradedSubmissions = $submissions->where('status', 'graded')->whereNotNull('grade');
            if ($gradedSubmissions->count() > 0) {
                $grades = $gradedSubmissions->map(function($sub) {
                    if (is_numeric($sub->grade)) return (float) $sub->grade;
                    $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                    return $gradeMap[strtoupper(substr($sub->grade, 0, 1))] ?? 0;
                });
                $homework->average_grade = round($grades->avg(), 1);
            } else {
                $homework->average_grade = 0;
            }

            return $homework;
        });

        // Overall statistics
        $allHomework = HomeworkAssignment::whereBetween('due_date', [$dateFrom, $dateTo])->get();
        $totalAssignments = $allHomework->count();
        
        $allSubmissions = HomeworkSubmission::whereHas('homeworkAssignment', function($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('due_date', [$dateFrom, $dateTo]);
        })->get();

        $stats = [
            'total_assignments' => $totalAssignments,
            'average_completion' => $totalAssignments > 0 ? round($allSubmissions->whereIn('status', ['submitted', 'graded'])->count() / $totalAssignments, 1) : 0,
            'grading_pending' => $allSubmissions->where('status', 'submitted')->count(),
            'overdue' => HomeworkAssignment::where('due_date', '<', now())
                ->whereHas('submissions', function($q) {
                    $q->where('status', 'pending');
                })->count(),
        ];

        // Charts
        $completionTrend = $this->getHomeworkCompletionTrend($dateFrom, $dateTo);
        $gradeDistribution = $this->getGradeDistribution($dateFrom, $dateTo);

        // Filter options
        $classes = ClassModel::orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();

        return view('superadmin.reports.homework', compact(
            'homeworkAssignments',
            'stats',
            'completionTrend',
            'gradeDistribution',
            'classes',
            'teachers',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Export report (PDF/Excel/CSV)
     */
    public function export(Request $request)
    {
        $reportType = $request->get('report_type');
        $format = $request->get('format', 'pdf');
        
        // Validate
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:attendance,student,class,homework',
            'format' => 'required|in:pdf,excel,csv',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Invalid export parameters.');
        }

        try {
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'exported_report',
                'model_type' => 'Report',
                'description' => "Exported {$reportType} report in {$format} format",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Route to appropriate export method
            switch ($reportType) {
                case 'attendance':
                    return $this->exportAttendance($request, $format);
                case 'student':
                    return $this->exportStudent($request, $format);
                case 'class':
                    return $this->exportClass($request, $format);
                case 'homework':
                    return $this->exportHomework($request, $format);
                default:
                    return redirect()->back()->with('error', 'Invalid report type.');
            }

        } catch (\Exception $e) {
            \Log::error('Export failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Export failed. Please try again.');
        }
    }

    /**
     * Export attendance report
     */
    protected function exportAttendance(Request $request, $format)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $query = Attendance::with(['student', 'class', 'markedBy'])
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $attendanceRecords = $query->orderBy('date', 'desc')->get();

        // Statistics
        $stats = [
            'total' => $attendanceRecords->count(),
            'present' => $attendanceRecords->where('status', 'present')->count(),
            'absent' => $attendanceRecords->where('status', 'absent')->count(),
            'late' => $attendanceRecords->where('status', 'late')->count(),
            'attendance_rate' => $attendanceRecords->count() > 0 
                ? round(($attendanceRecords->where('status', 'present')->count() / $attendanceRecords->count()) * 100, 1) 
                : 0,
        ];

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.pdf.attendance', [
                'attendanceRecords' => $attendanceRecords,
                'stats' => $stats,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ]);
            
            return $pdf->download('attendance-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($format === 'excel' || $format === 'csv') {
            $export = new AttendanceReportExport($attendanceRecords, $stats, $dateFrom, $dateTo);
            
            if ($format === 'csv') {
                return Excel::download($export, 'attendance-report-' . now()->format('Y-m-d') . '.csv', \Maatwebsite\Excel\Excel::CSV);
            }
            
            return Excel::download($export, 'attendance-report-' . now()->format('Y-m-d') . '.xlsx');
        }
    }

    /**
     * Export student report
     */
    protected function exportStudent(Request $request, $format)
    {
        $studentId = $request->get('student_id');
        $dateFrom = $request->get('date_from', now()->subDays(90)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        if (!$studentId) {
            return redirect()->back()->with('error', 'Please select a student.');
        }

        $student = Student::with(['parent', 'enrollments.class'])->findOrFail($studentId);

        // Gather data (same as students() method)
        $attendanceRecords = Attendance::where('student_id', $studentId)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->with('class')
            ->orderBy('date', 'desc')
            ->get();

        $totalAttendance = $attendanceRecords->count();
        $presentCount = $attendanceRecords->where('status', 'present')->count();

        $attendanceData = [
            'records' => $attendanceRecords->take(10),
            'total' => $totalAttendance,
            'present' => $presentCount,
            'absent' => $attendanceRecords->where('status', 'absent')->count(),
            'late' => $attendanceRecords->where('status', 'late')->count(),
            'rate' => $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0,
        ];

        $homeworkSubmissions = HomeworkSubmission::where('student_id', $studentId)
            ->with('homeworkAssignment.class')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalHomework = $homeworkSubmissions->count();
        $submittedCount = $homeworkSubmissions->whereIn('status', ['submitted', 'graded'])->count();

        // Calculate average grade
        $averageGrade = 0;
        $gradedSubmissions = $homeworkSubmissions->where('status', 'graded')->whereNotNull('grade');
        if ($gradedSubmissions->count() > 0) {
            $grades = $gradedSubmissions->map(function($sub) {
                if (is_numeric($sub->grade)) return (float) $sub->grade;
                $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                return $gradeMap[strtoupper(substr($sub->grade, 0, 1))] ?? 0;
            });
            $averageGrade = round($grades->avg(), 1);
        }

        $homeworkData = [
            'submissions' => $homeworkSubmissions->take(10),
            'total' => $totalHomework,
            'submitted' => $submittedCount,
            'graded' => $homeworkSubmissions->where('status', 'graded')->count(),
            'rate' => $totalHomework > 0 ? round(($submittedCount / $totalHomework) * 100, 1) : 0,
            'average_grade' => $averageGrade,
        ];

        $progressNotes = ProgressNote::where('student_id', $studentId)
            ->with('progressSheet.class')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $progressData = [
            'notes' => $progressNotes,
            'count' => $progressNotes->count(),
        ];

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.pdf.student', [
                'student' => $student,
                'attendanceData' => $attendanceData,
                'homeworkData' => $homeworkData,
                'progressData' => $progressData,
            ]);
            
            return $pdf->download('student-report-' . $student->id . '-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($format === 'excel' || $format === 'csv') {
            $export = new StudentReportExport($student, $attendanceData, $homeworkData, $progressData);
            
            if ($format === 'csv') {
                return Excel::download($export, 'student-report-' . $student->id . '-' . now()->format('Y-m-d') . '.csv', \Maatwebsite\Excel\Excel::CSV);
            }
            
            return Excel::download($export, 'student-report-' . $student->id . '-' . now()->format('Y-m-d') . '.xlsx');
        }
    }

    /**
     * Export class report
     */
    protected function exportClass(Request $request, $format)
    {
        $classId = $request->get('class_id');
        $dateFrom = $request->get('date_from', now()->subDays(90)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        if (!$classId) {
            return redirect()->back()->with('error', 'Please select a class.');
        }

        $class = ClassModel::with(['teacher', 'students'])->findOrFail($classId);

        // Gather statistics (same as classes() method)
        $totalStudents = $class->students()->wherePivot('status', 'active')->count();

        $classAttendance = Attendance::where('class_id', $classId)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->get();

        $totalAttendance = $classAttendance->count();
        $presentCount = $classAttendance->where('status', 'present')->count();

        $attendanceStats = [
            'total' => $totalAttendance,
            'present' => $presentCount,
            'absent' => $classAttendance->where('status', 'absent')->count(),
            'late' => $classAttendance->where('status', 'late')->count(),
            'rate' => $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0,
        ];

        $classHomework = HomeworkAssignment::where('class_id', $classId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        $homeworkStats = [
            'total_assignments' => $classHomework->count(),
            'average_completion' => 75, // Placeholder
        ];

        $studentStats = $class->students()
            ->wherePivot('status', 'active')
            ->get()
            ->map(function($student) use ($dateFrom, $dateTo) {
                $studentAttendance = $student->attendance()->whereBetween('date', [$dateFrom, $dateTo])->get();
                $totalAtt = $studentAttendance->count();
                $presentAtt = $studentAttendance->where('status', 'present')->count();
                
                return [
                    'student' => $student,
                    'attendance_rate' => $totalAtt > 0 ? round(($presentAtt / $totalAtt) * 100, 1) : 0,
                    'homework_rate' => 80, // Placeholder
                    'average_grade' => 75, // Placeholder
                ];
            });

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.pdf.class', [
                'class' => $class,
                'studentStats' => $studentStats,
                'attendanceStats' => $attendanceStats,
                'homeworkStats' => $homeworkStats,
            ]);
            
            return $pdf->download('class-report-' . $class->id . '-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($format === 'excel' || $format === 'csv') {
            $export = new ClassReportExport($class, $studentStats, $attendanceStats, $homeworkStats);
            
            if ($format === 'csv') {
                return Excel::download($export, 'class-report-' . $class->id . '-' . now()->format('Y-m-d') . '.csv', \Maatwebsite\Excel\Excel::CSV);
            }
            
            return Excel::download($export, 'class-report-' . $class->id . '-' . now()->format('Y-m-d') . '.xlsx');
        }
    }

    /**
     * Export homework report
     */
    protected function exportHomework(Request $request, $format)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $query = HomeworkAssignment::with(['class', 'teacher', 'submissions'])
            ->whereBetween('due_date', [$dateFrom, $dateTo]);

        $homeworkAssignments = $query->orderBy('due_date', 'desc')->get();

        // Calculate stats for each
        $homeworkAssignments->transform(function($homework) {
            $totalStudents = $homework->class->students()->wherePivot('status', 'active')->count();
            $submissions = $homework->submissions;
            $submittedCount = $submissions->whereIn('status', ['submitted', 'graded'])->count();
            
            $homework->total_students = $totalStudents;
            $homework->submitted_count = $submittedCount;
            $homework->graded_count = $submissions->where('status', 'graded')->count();
            $homework->completion_rate = $totalStudents > 0 ? round(($submittedCount / $totalStudents) * 100, 1) : 0;
            $homework->average_grade = 75; // Placeholder
            
            return $homework;
        });

        $stats = [
            'total_assignments' => $homeworkAssignments->count(),
            'average_completion' => 70,
            'grading_pending' => 10,
            'overdue' => 5,
        ];

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.pdf.homework', [
                'homeworkAssignments' => $homeworkAssignments,
                'stats' => $stats,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ]);
            
            return $pdf->download('homework-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($format === 'excel' || $format === 'csv') {
            $export = new HomeworkReportExport($homeworkAssignments, $stats);
            
            if ($format === 'csv') {
                return Excel::download($export, 'homework-report-' . now()->format('Y-m-d') . '.csv', \Maatwebsite\Excel\Excel::CSV);
            }
            
            return Excel::download($export, 'homework-report-' . now()->format('Y-m-d') . '.xlsx');
        }
    }

    // Helper methods for calculations

    protected function calculateOverallAttendanceRate()
    {
        $total = Attendance::count();
        $present = Attendance::where('status', 'present')->count();
        return $total > 0 ? round(($present / $total) * 100, 1) : 0;
    }

    protected function calculateOverallHomeworkCompletion()
    {
        $total = HomeworkSubmission::count();
        $submitted = HomeworkSubmission::whereIn('status', ['submitted', 'graded'])->count();
        return $total > 0 ? round(($submitted / $total) * 100, 1) : 0;
    }

    protected function getAttendanceTrend($dateFrom, $dateTo)
    {
        $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo));
        $labels = [];
        $presentData = [];
        $absentData = [];

        if ($days <= 30) {
            // Daily
            for ($i = 0; $i <= min($days, 30); $i++) {
                $date = Carbon::parse($dateFrom)->addDays($i);
                $labels[] = $date->format('d M');
                $presentData[] = Attendance::whereDate('date', $date)->where('status', 'present')->count();
                $absentData[] = Attendance::whereDate('date', $date)->where('status', 'absent')->count();
            }
        }

        return [
            'labels' => $labels,
            'present' => $presentData,
            'absent' => $absentData,
        ];
    }

    protected function getStudentAttendanceTrend($studentId, $dateFrom, $dateTo)
    {
        $labels = [];
        $data = [];

        for ($i = 0; $i < 12; $i++) {
            $month = now()->subMonths(11 - $i);
            $labels[] = $month->format('M');
            
            $total = Attendance::where('student_id', $studentId)
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->count();
            
            $present = Attendance::where('student_id', $studentId)
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->where('status', 'present')
                ->count();
            
            $data[] = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    protected function getStudentGradeProgression($studentId)
    {
        $submissions = HomeworkSubmission::where('student_id', $studentId)
            ->where('status', 'graded')
            ->whereNotNull('grade')
            ->orderBy('graded_at')
            ->limit(10)
            ->get();

        $labels = [];
        $data = [];

        foreach ($submissions as $submission) {
            $labels[] = $submission->graded_at ? $submission->graded_at->format('d M') : 'N/A';
            
            if (is_numeric($submission->grade)) {
                $data[] = (float) $submission->grade;
            } else {
                $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                $data[] = $gradeMap[strtoupper(substr($submission->grade, 0, 1))] ?? 0;
            }
        }

        return ['labels' => $labels, 'data' => $data];
    }

    protected function getClassPerformanceTrend($classId, $dateFrom, $dateTo)
    {
        // Simplified - last 6 months attendance rate
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M');
            
            $total = Attendance::where('class_id', $classId)
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->count();
            
            $present = Attendance::where('class_id', $classId)
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->where('status', 'present')
                ->count();
            
            $data[] = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    protected function getClassStudentDistribution($classId)
    {
        // Grade distribution
        return [
            'labels' => ['90-100', '80-89', '70-79', '60-69', 'Below 60'],
            'data' => [5, 10, 8, 4, 2], // Placeholder
        ];
    }

    protected function getHomeworkCompletionTrend($dateFrom, $dateTo)
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $week = now()->subWeeks($i);
            $labels[] = 'Week ' . (7 - $i);
            
            $weekStart = $week->startOfWeek();
            $weekEnd = $week->endOfWeek();
            
            $total = HomeworkAssignment::whereBetween('due_date', [$weekStart, $weekEnd])->count();
            $submitted = HomeworkSubmission::whereHas('homeworkAssignment', function($q) use ($weekStart, $weekEnd) {
                $q->whereBetween('due_date', [$weekStart, $weekEnd]);
            })->whereIn('status', ['submitted', 'graded'])->count();
            
            $data[] = $total > 0 ? round(($submitted / $total) * 100, 1) : 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    protected function getGradeDistribution($dateFrom, $dateTo)
    {
        $submissions = HomeworkSubmission::whereHas('homeworkAssignment', function($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('due_date', [$dateFrom, $dateTo]);
        })->where('status', 'graded')->whereNotNull('grade')->get();

        $distribution = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];

        foreach ($submissions as $submission) {
            if (is_numeric($submission->grade)) {
                $grade = (float) $submission->grade;
                if ($grade >= 90) $distribution['A']++;
                elseif ($grade >= 80) $distribution['B']++;
                elseif ($grade >= 70) $distribution['C']++;
                elseif ($grade >= 60) $distribution['D']++;
                else $distribution['F']++;
            } else {
                $letter = strtoupper(substr($submission->grade, 0, 1));
                if (isset($distribution[$letter])) {
                    $distribution[$letter]++;
                }
            }
        }

        return [
            'labels' => array_keys($distribution),
            'data' => array_values($distribution),
        ];
    }
}
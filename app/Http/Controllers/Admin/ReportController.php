<?php

namespace App\Http\Controllers\Admin;

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
                'generated_by' => 'Admin',
                'date' => now()->subDays(2),
                'format' => 'PDF',
            ],
            [
                'type' => 'Student Performance',
                'generated_by' => 'Admin',
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

        return view('admin.reports.index', compact('stats', 'recentReports', 'reportsByType'));
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

        // Sort
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $attendanceRecords = $query->paginate(50);

        // Statistics
        $allRecords = Attendance::whereBetween('date', [$dateFrom, $dateTo]);
        
        if ($request->filled('class_id')) {
            $allRecords->where('class_id', $request->class_id);
        }
        
        $stats = [
            'total' => $allRecords->count(),
            'present' => $allRecords->where('status', 'present')->count(),
            'absent' => $allRecords->where('status', 'absent')->count(),
            'late' => $allRecords->where('status', 'late')->count(),
            'unauthorized' => $allRecords->where('status', 'unauthorized')->count(),
        ];

        $stats['attendance_rate'] = $stats['total'] > 0 
            ? round(($stats['present'] / $stats['total']) * 100, 1)
            : 0;

        // Chart data
        $attendanceTrend = $this->getAttendanceTrend($dateFrom, $dateTo, $request);

        // Filter options
        $classes = ClassModel::orderBy('name')->get();
        $students = Student::where('status', 'active')->orderBy('first_name')->get();

        return view('admin.reports.attendance', compact(
            'attendanceRecords',
            'stats',
            'attendanceTrend',
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

            $attendanceData = [
                'records' => $attendanceRecords->take(10),
                'total' => $totalAttendance,
                'present' => $presentCount,
                'absent' => $attendanceRecords->where('status', 'absent')->count(),
                'late' => $attendanceRecords->where('status', 'late')->count(),
                'rate' => $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0,
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

        return view('admin.reports.students', compact(
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
            $classAttendanceRate = $totalAttendance > 0 
                ? round(($presentCount / $totalAttendance) * 100, 1) 
                : 0;

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
                ->get();

            $homeworkStats = [
                'total_assignments' => $classHomework->count(),
                'average_completion' => 75, // Placeholder - calculate from submissions
            ];

            // Per-student statistics
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

            // Charts
            $charts = [
                'attendance_trend' => $this->getClassAttendanceTrend($selectedClassId, $dateFrom, $dateTo),
                'grade_distribution' => $this->getClassGradeDistribution($selectedClassId),
            ];

            $classData = $class;
        }

        return view('admin.reports.classes', compact(
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
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $query = HomeworkAssignment::with(['class', 'teacher', 'submissions'])
            ->whereBetween('due_date', [$dateFrom, $dateTo]);

        // Class filter
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Teacher filter
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $homeworkAssignments = $query->orderBy('due_date', 'desc')->paginate(20);

        // Calculate stats for each
        $homeworkAssignments->transform(function($homework) {
            $totalStudents = $homework->class->students()->wherePivot('status', 'active')->count();
            $submissions = $homework->submissions;
            $submittedCount = $submissions->whereIn('status', ['submitted', 'graded'])->count();
            
            $homework->total_students = $totalStudents;
            $homework->submitted_count = $submittedCount;
            $homework->graded_count = $submissions->where('status', 'graded')->count();
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

        return view('admin.reports.homework', compact(
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
        
        // Custom validation messages
        $messages = [
            'report_type.required' => 'Please select a report type to export.',
            'report_type.in' => 'Invalid report type selected.',
            'format.required' => 'Please select an export format.',
            'format.in' => 'Invalid export format. Choose PDF, Excel, or CSV.',
        ];
        
        // Validate
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:attendance,student,class,homework',
            'format' => 'required|in:pdf,excel,csv',
        ], $messages);
        
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

    // HELPER METHODS FOR CALCULATIONS
    
    protected function calculateOverallAttendanceRate()
    {
        $total = Attendance::whereMonth('created_at', now()->month)->count();
        $present = Attendance::whereMonth('created_at', now()->month)
            ->where('status', 'present')->count();
        
        return $total > 0 ? round(($present / $total) * 100, 1) : 0;
    }

    protected function calculateOverallHomeworkCompletion()
    {
        $total = HomeworkSubmission::whereMonth('created_at', now()->month)->count();
        $submitted = HomeworkSubmission::whereMonth('created_at', now()->month)
            ->whereIn('status', ['submitted', 'graded'])->count();
        
        return $total > 0 ? round(($submitted / $total) * 100, 1) : 0;
    }

    protected function getAttendanceTrend($dateFrom, $dateTo, $request)
    {
        // Generate trend data - placeholder
        return [
            'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            'present' => [85, 90, 88, 92],
            'absent' => [10, 7, 9, 5],
            'late' => [5, 3, 3, 3],
        ];
    }

    protected function getStudentAttendanceTrend($studentId, $dateFrom, $dateTo)
    {
        // Generate student trend - placeholder
        return [
            'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            'data' => [90, 95, 88, 92],
        ];
    }

    protected function getStudentGradeProgression($studentId)
    {
        // Generate grade progression - placeholder
        return [
            'labels' => ['Sep', 'Oct', 'Nov', 'Dec'],
            'data' => [75, 78, 82, 85],
        ];
    }

    protected function getClassAttendanceTrend($classId, $dateFrom, $dateTo)
    {
        // Generate class trend - placeholder
        return [
            'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            'data' => [88, 90, 87, 91],
        ];
    }

    protected function getClassGradeDistribution($classId)
    {
        // Generate grade distribution - placeholder
        return [
            'labels' => ['A', 'B', 'C', 'D', 'F'],
            'data' => [15, 35, 30, 15, 5],
        ];
    }

    protected function getHomeworkCompletionTrend($dateFrom, $dateTo)
    {
        // Generate completion trend - placeholder
        return [
            'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            'data' => [75, 80, 78, 85],
        ];
    }

    protected function getGradeDistribution($dateFrom, $dateTo)
    {
        // Generate grade distribution - placeholder
        return [
            'labels' => ['A', 'B', 'C', 'D', 'F'],
            'data' => [20, 30, 25, 15, 10],
        ];
    }

    // EXPORT METHODS (Placeholder - implement with actual Export classes)
    
    protected function exportAttendance(Request $request, $format)
    {
        // Implement attendance export
        return redirect()->back()->with('info', 'Attendance export feature coming soon.');
    }

    protected function exportStudent(Request $request, $format)
    {
        // Implement student export
        return redirect()->back()->with('info', 'Student export feature coming soon.');
    }

    protected function exportClass(Request $request, $format)
    {
        // Implement class export
        return redirect()->back()->with('info', 'Class export feature coming soon.');
    }

    protected function exportHomework(Request $request, $format)
    {
        // Implement homework export
        return redirect()->back()->with('info', 'Homework export feature coming soon.');
    }
}
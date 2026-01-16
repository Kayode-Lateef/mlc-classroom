<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display attendance history for parent's children
     */
    public function index(Request $request)
    {
        $parent = auth()->user();

        // Get parent's children
        $children = $parent->children()->where('status', 'active')->get();

        // If no children, show message
        if ($children->isEmpty()) {
            return view('parent.attendance.index', [
                'children' => $children,
                'selectedChild' => null,
                'attendance' => collect(),
                'classes' => collect(),
                'stats' => null,
            ]);
        }

        // Select child (default to first child or from request)
        $selectedChildId = $request->get('child_id', $children->first()->id);
        $selectedChild = $children->firstWhere('id', $selectedChildId);

        // Verify child belongs to parent
        if (!$selectedChild || $selectedChild->parent_id !== $parent->id) {
            $selectedChild = $children->first();
        }

        // ============================================================
        // BUILD QUERY
        // ============================================================
        
        $query = Attendance::where('student_id', $selectedChild->id)
            ->with(['class.teacher', 'schedule', 'markedBy']);

        // Date range filter (default last 30 days)
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $query->whereBetween('date', [$dateFrom, $dateTo]);

        // Class filter
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $attendance = $query->paginate(20);

        // ============================================================
        // STATISTICS FOR SELECTED PERIOD
        // ============================================================
        
        $periodAttendance = Attendance::where('student_id', $selectedChild->id)
            ->whereBetween('date', [$dateFrom, $dateTo]);

        // Apply same filters
        if ($request->filled('class_id')) {
            $periodAttendance->where('class_id', $request->class_id);
        }
        if ($request->filled('status')) {
            $periodAttendance->where('status', $request->status);
        }

        $periodAttendance = $periodAttendance->get();

        $stats = [
            'period' => [
                'total' => $periodAttendance->count(),
                'present' => $periodAttendance->where('status', 'present')->count(),
                'absent' => $periodAttendance->where('status', 'absent')->count(),
                'late' => $periodAttendance->where('status', 'late')->count(),
                'rate' => 0,
            ],
            'overall' => [],
            'monthly' => [],
            'by_class' => [],
        ];

        $stats['period']['rate'] = $stats['period']['total'] > 0 ? 
            round(($stats['period']['present'] / $stats['period']['total']) * 100, 1) : 0;

        // ============================================================
        // OVERALL STATISTICS (All Time)
        // ============================================================
        
        $allAttendance = Attendance::where('student_id', $selectedChild->id)->get();
        
        $stats['overall'] = [
            'total' => $allAttendance->count(),
            'present' => $allAttendance->where('status', 'present')->count(),
            'absent' => $allAttendance->where('status', 'absent')->count(),
            'late' => $allAttendance->where('status', 'late')->count(),
            'rate' => 0,
        ];

        $stats['overall']['rate'] = $stats['overall']['total'] > 0 ? 
            round(($stats['overall']['present'] / $stats['overall']['total']) * 100, 1) : 0;

        // ============================================================
        // MONTHLY STATISTICS (This Month)
        // ============================================================
        
        $thisMonthAttendance = Attendance::where('student_id', $selectedChild->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();
        
        $stats['monthly'] = [
            'total' => $thisMonthAttendance->count(),
            'present' => $thisMonthAttendance->where('status', 'present')->count(),
            'absent' => $thisMonthAttendance->where('status', 'absent')->count(),
            'late' => $thisMonthAttendance->where('status', 'late')->count(),
            'rate' => 0,
        ];

        $stats['monthly']['rate'] = $stats['monthly']['total'] > 0 ? 
            round(($stats['monthly']['present'] / $stats['monthly']['total']) * 100, 1) : 0;

        // ============================================================
        // BY CLASS STATISTICS
        // ============================================================
        
        $enrolledClasses = $selectedChild->classes()
            ->wherePivot('status', 'active')
            ->with('teacher')
            ->get();

        foreach ($enrolledClasses as $class) {
            $classAttendance = Attendance::where('student_id', $selectedChild->id)
                ->where('class_id', $class->id)
                ->get();
            
            $classPresent = $classAttendance->where('status', 'present')->count();
            $classTotal = $classAttendance->count();
            
            $stats['by_class'][] = [
                'class' => $class,
                'total' => $classTotal,
                'present' => $classPresent,
                'absent' => $classAttendance->where('status', 'absent')->count(),
                'late' => $classAttendance->where('status', 'late')->count(),
                'rate' => $classTotal > 0 ? round(($classPresent / $classTotal) * 100, 1) : 0,
            ];
        }

        // Sort by class name
        usort($stats['by_class'], function($a, $b) {
            return strcmp($a['class']->name, $b['class']->name);
        });

        // ============================================================
        // FILTER OPTIONS
        // ============================================================
        
        $classes = $selectedChild->classes()
            ->wherePivot('status', 'active')
            ->orderBy('name')
            ->get();

        return view('parent.attendance.index', compact(
            'children',
            'selectedChild',
            'attendance',
            'classes',
            'stats',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Display detailed attendance for specific child
     */
    public function show(Student $student, Request $request)
    {
        $parent = auth()->user();

        // Verify student belongs to parent
        if ($student->parent_id !== $parent->id) {
            abort(403, 'You do not have permission to view this student\'s attendance.');
        }

        // Load relationships
        $student->load([
            'classes' => function($query) {
                $query->wherePivot('status', 'active')
                      ->with('teacher');
            }
        ]);

        // ============================================================
        // DATE RANGE SETUP
        // ============================================================
        
        // Default to last 90 days for detail view
        $dateFrom = $request->get('date_from', now()->subDays(90)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // ============================================================
        // ALL ATTENDANCE RECORDS FOR PERIOD
        // ============================================================
        
        $attendanceRecords = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->with(['class.teacher', 'schedule', 'markedBy'])
            ->orderBy('date', 'desc')
            ->get();

        // ============================================================
        // COMPREHENSIVE STATISTICS
        // ============================================================
        
        $stats = [
            'period' => [
                'total' => $attendanceRecords->count(),
                'present' => $attendanceRecords->where('status', 'present')->count(),
                'absent' => $attendanceRecords->where('status', 'absent')->count(),
                'late' => $attendanceRecords->where('status', 'late')->count(),
                'rate' => 0,
            ],
            'overall' => [],
            'this_week' => [],
            'this_month' => [],
            'last_month' => [],
            'by_class' => [],
            'by_status' => [],
            'weekly_trend' => [],
        ];

        $stats['period']['rate'] = $stats['period']['total'] > 0 ? 
            round(($stats['period']['present'] / $stats['period']['total']) * 100, 1) : 0;

        // Overall statistics
        $allAttendance = Attendance::where('student_id', $student->id)->get();
        $stats['overall'] = [
            'total' => $allAttendance->count(),
            'present' => $allAttendance->where('status', 'present')->count(),
            'absent' => $allAttendance->where('status', 'absent')->count(),
            'late' => $allAttendance->where('status', 'late')->count(),
            'rate' => $allAttendance->count() > 0 ? 
                round(($allAttendance->where('status', 'present')->count() / $allAttendance->count()) * 100, 1) : 0,
        ];

        // This week
        $thisWeekAttendance = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->get();
        $stats['this_week'] = [
            'total' => $thisWeekAttendance->count(),
            'present' => $thisWeekAttendance->where('status', 'present')->count(),
            'rate' => $thisWeekAttendance->count() > 0 ? 
                round(($thisWeekAttendance->where('status', 'present')->count() / $thisWeekAttendance->count()) * 100, 1) : 0,
        ];

        // This month
        $thisMonthAttendance = Attendance::where('student_id', $student->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();
        $stats['this_month'] = [
            'total' => $thisMonthAttendance->count(),
            'present' => $thisMonthAttendance->where('status', 'present')->count(),
            'rate' => $thisMonthAttendance->count() > 0 ? 
                round(($thisMonthAttendance->where('status', 'present')->count() / $thisMonthAttendance->count()) * 100, 1) : 0,
        ];

        // Last month
        $lastMonth = now()->subMonth();
        $lastMonthAttendance = Attendance::where('student_id', $student->id)
            ->whereMonth('date', $lastMonth->month)
            ->whereYear('date', $lastMonth->year)
            ->get();
        $stats['last_month'] = [
            'total' => $lastMonthAttendance->count(),
            'present' => $lastMonthAttendance->where('status', 'present')->count(),
            'rate' => $lastMonthAttendance->count() > 0 ? 
                round(($lastMonthAttendance->where('status', 'present')->count() / $lastMonthAttendance->count()) * 100, 1) : 0,
        ];

        // By class breakdown
        foreach ($student->classes as $class) {
            $classAttendance = Attendance::where('student_id', $student->id)
                ->where('class_id', $class->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->get();
            
            if ($classAttendance->count() > 0) {
                $classPresent = $classAttendance->where('status', 'present')->count();
                $stats['by_class'][] = [
                    'class' => $class,
                    'total' => $classAttendance->count(),
                    'present' => $classPresent,
                    'absent' => $classAttendance->where('status', 'absent')->count(),
                    'late' => $classAttendance->where('status', 'late')->count(),
                    'rate' => round(($classPresent / $classAttendance->count()) * 100, 1),
                ];
            }
        }

        // Status distribution
        $stats['by_status'] = [
            'present' => $attendanceRecords->where('status', 'present')->count(),
            'absent' => $attendanceRecords->where('status', 'absent')->count(),
            'late' => $attendanceRecords->where('status', 'late')->count(),
        ];

        // Weekly trend (last 8 weeks)
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            
            $weekAttendance = Attendance::where('student_id', $student->id)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->get();
            
            $weekPresent = $weekAttendance->where('status', 'present')->count();
            $weekTotal = $weekAttendance->count();
            
            $stats['weekly_trend'][] = [
                'week' => $weekStart->format('M d'),
                'rate' => $weekTotal > 0 ? round(($weekPresent / $weekTotal) * 100, 1) : 0,
            ];
        }

        // ============================================================
        // GROUP RECORDS BY DATE
        // ============================================================
        
        $attendanceByDate = $attendanceRecords->groupBy(function($record) {
            return $record->date->format('Y-m-d');
        });

        return view('parent.attendance.show', compact(
            'student',
            'attendanceRecords',
            'attendanceByDate',
            'stats',
            'dateFrom',
            'dateTo'
        ));
    }
}
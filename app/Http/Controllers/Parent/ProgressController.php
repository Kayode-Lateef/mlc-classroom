<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\ProgressNote;
use App\Models\ProgressSheet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProgressController extends Controller
{
    /**
     * Display progress reports for parent's children
     */
    public function index(Request $request)
    {
        $parent = auth()->user();

        // Get parent's children
        $children = $parent->children()->where('status', 'active')->get();

        // If no children, show message
        if ($children->isEmpty()) {
            return view('parent.progress.index', [
                'children' => $children,
                'selectedChild' => null,
                'progressNotes' => collect(),
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
        
        $query = ProgressNote::where('student_id', $selectedChild->id)
            ->with(['progressSheet.class.teacher', 'progressSheet.teacher']);

        // Date range filter (default last 90 days)
        $dateFrom = $request->get('date_from', now()->subDays(90)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $query->whereHas('progressSheet', function($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('date', [$dateFrom, $dateTo]);
        });

        // Class filter
        if ($request->filled('class_id')) {
            $query->whereHas('progressSheet', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // Performance filter
        if ($request->filled('performance')) {
            $query->where('performance', $request->performance);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $progressNotes = $query->paginate(20);

        // ============================================================
        // STATISTICS
        // ============================================================
        
        $allNotes = ProgressNote::where('student_id', $selectedChild->id)
            ->with('progressSheet')
            ->get();
        
        $stats = [
            'total' => $allNotes->count(),
            'excellent' => $allNotes->where('performance', 'excellent')->count(),
            'good' => $allNotes->where('performance', 'good')->count(),
            'average' => $allNotes->where('performance', 'average')->count(),
            'struggling' => $allNotes->where('performance', 'struggling')->count(),
            'absent' => $allNotes->where('performance', 'absent')->count(),
            'success_rate' => 0,
            'with_notes' => $allNotes->whereNotNull('notes')->count(),
            'by_class' => [],
            'recent_trend' => [],
        ];

        // Calculate success rate (excellent + good)
        $totalWithPerformance = $stats['total'] - $stats['absent'];
        if ($totalWithPerformance > 0) {
            $successCount = $stats['excellent'] + $stats['good'];
            $stats['success_rate'] = round(($successCount / $totalWithPerformance) * 100, 1);
        }

        // By class breakdown
        $enrolledClasses = $selectedChild->classes()
            ->wherePivot('status', 'active')
            ->with('teacher')
            ->get();

        foreach ($enrolledClasses as $class) {
            $classNotes = ProgressNote::where('student_id', $selectedChild->id)
                ->whereHas('progressSheet', function($q) use ($class) {
                    $q->where('class_id', $class->id);
                })
                ->get();
            
            if ($classNotes->count() > 0) {
                $classExcellent = $classNotes->where('performance', 'excellent')->count();
                $classGood = $classNotes->where('performance', 'good')->count();
                $classTotal = $classNotes->count() - $classNotes->where('performance', 'absent')->count();
                
                $stats['by_class'][] = [
                    'class' => $class,
                    'total' => $classNotes->count(),
                    'excellent' => $classExcellent,
                    'good' => $classGood,
                    'average' => $classNotes->where('performance', 'average')->count(),
                    'struggling' => $classNotes->where('performance', 'struggling')->count(),
                    'success_rate' => $classTotal > 0 ? 
                        round((($classExcellent + $classGood) / $classTotal) * 100, 1) : 0,
                ];
            }
        }

        // Recent trend (last 8 weeks)
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            
            $weekNotes = ProgressNote::where('student_id', $selectedChild->id)
                ->whereHas('progressSheet', function($q) use ($weekStart, $weekEnd) {
                    $q->whereBetween('date', [$weekStart, $weekEnd]);
                })
                ->get();
            
            $weekExcellent = $weekNotes->where('performance', 'excellent')->count();
            $weekGood = $weekNotes->where('performance', 'good')->count();
            $weekTotal = $weekNotes->count() - $weekNotes->where('performance', 'absent')->count();
            
            $stats['recent_trend'][] = [
                'week' => $weekStart->format('M d'),
                'rate' => $weekTotal > 0 ? 
                    round((($weekExcellent + $weekGood) / $weekTotal) * 100, 1) : 0,
                'count' => $weekNotes->count(),
            ];
        }

        // ============================================================
        // FILTER OPTIONS
        // ============================================================
        
        $classes = $selectedChild->classes()
            ->wherePivot('status', 'active')
            ->orderBy('name')
            ->get();

        return view('parent.progress.index', compact(
            'children',
            'selectedChild',
            'progressNotes',
            'classes',
            'stats',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Display detailed progress for specific child
     */
    public function show(Student $student, Request $request)
    {
        $parent = auth()->user();

        // Verify student belongs to parent
        if ($student->parent_id !== $parent->id) {
            abort(403, 'You do not have permission to view this student\'s progress.');
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
        
        // Default to last 6 months for detail view
        $dateFrom = $request->get('date_from', now()->subMonths(6)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // ============================================================
        // ALL PROGRESS NOTES FOR PERIOD
        // ============================================================
        
        $progressNotes = ProgressNote::where('student_id', $student->id)
            ->whereHas('progressSheet', function($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('date', [$dateFrom, $dateTo]);
            })
            ->with(['progressSheet.class.teacher', 'progressSheet.teacher'])
            ->orderBy('created_at', 'desc')
            ->get();

        // ============================================================
        // COMPREHENSIVE STATISTICS
        // ============================================================
        
        $stats = [
            'period' => [
                'total' => $progressNotes->count(),
                'excellent' => $progressNotes->where('performance', 'excellent')->count(),
                'good' => $progressNotes->where('performance', 'good')->count(),
                'average' => $progressNotes->where('performance', 'average')->count(),
                'struggling' => $progressNotes->where('performance', 'struggling')->count(),
                'absent' => $progressNotes->where('performance', 'absent')->count(),
                'success_rate' => 0,
            ],
            'overall' => [],
            'this_month' => [],
            'last_month' => [],
            'by_class' => [],
            'by_performance' => [],
            'monthly_trend' => [],
        ];

        // Period success rate
        $periodTotal = $stats['period']['total'] - $stats['period']['absent'];
        if ($periodTotal > 0) {
            $periodSuccess = $stats['period']['excellent'] + $stats['period']['good'];
            $stats['period']['success_rate'] = round(($periodSuccess / $periodTotal) * 100, 1);
        }

        // Overall statistics
        $allNotes = ProgressNote::where('student_id', $student->id)->get();
        $stats['overall'] = [
            'total' => $allNotes->count(),
            'excellent' => $allNotes->where('performance', 'excellent')->count(),
            'good' => $allNotes->where('performance', 'good')->count(),
            'average' => $allNotes->where('performance', 'average')->count(),
            'struggling' => $allNotes->where('performance', 'struggling')->count(),
            'absent' => $allNotes->where('performance', 'absent')->count(),
            'success_rate' => 0,
        ];

        $overallTotal = $stats['overall']['total'] - $stats['overall']['absent'];
        if ($overallTotal > 0) {
            $overallSuccess = $stats['overall']['excellent'] + $stats['overall']['good'];
            $stats['overall']['success_rate'] = round(($overallSuccess / $overallTotal) * 100, 1);
        }

        // This month
        $thisMonthNotes = ProgressNote::where('student_id', $student->id)
            ->whereHas('progressSheet', function($q) {
                $q->whereMonth('date', now()->month)
                  ->whereYear('date', now()->year);
            })
            ->get();
        
        $stats['this_month'] = [
            'total' => $thisMonthNotes->count(),
            'excellent' => $thisMonthNotes->where('performance', 'excellent')->count(),
            'good' => $thisMonthNotes->where('performance', 'good')->count(),
            'struggling' => $thisMonthNotes->where('performance', 'struggling')->count(),
        ];

        // Last month
        $lastMonth = now()->subMonth();
        $lastMonthNotes = ProgressNote::where('student_id', $student->id)
            ->whereHas('progressSheet', function($q) use ($lastMonth) {
                $q->whereMonth('date', $lastMonth->month)
                  ->whereYear('date', $lastMonth->year);
            })
            ->get();
        
        $stats['last_month'] = [
            'total' => $lastMonthNotes->count(),
            'excellent' => $lastMonthNotes->where('performance', 'excellent')->count(),
            'good' => $lastMonthNotes->where('performance', 'good')->count(),
            'struggling' => $lastMonthNotes->where('performance', 'struggling')->count(),
        ];

        // By class breakdown
        foreach ($student->classes as $class) {
            $classNotes = ProgressNote::where('student_id', $student->id)
                ->whereHas('progressSheet', function($q) use ($class, $dateFrom, $dateTo) {
                    $q->where('class_id', $class->id)
                      ->whereBetween('date', [$dateFrom, $dateTo]);
                })
                ->get();
            
            if ($classNotes->count() > 0) {
                $classExcellent = $classNotes->where('performance', 'excellent')->count();
                $classGood = $classNotes->where('performance', 'good')->count();
                $classTotal = $classNotes->count() - $classNotes->where('performance', 'absent')->count();
                
                $stats['by_class'][] = [
                    'class' => $class,
                    'total' => $classNotes->count(),
                    'excellent' => $classExcellent,
                    'good' => $classGood,
                    'average' => $classNotes->where('performance', 'average')->count(),
                    'struggling' => $classNotes->where('performance', 'struggling')->count(),
                    'absent' => $classNotes->where('performance', 'absent')->count(),
                    'success_rate' => $classTotal > 0 ? 
                        round((($classExcellent + $classGood) / $classTotal) * 100, 1) : 0,
                ];
            }
        }

        // Performance distribution
        $stats['by_performance'] = [
            'excellent' => $progressNotes->where('performance', 'excellent')->count(),
            'good' => $progressNotes->where('performance', 'good')->count(),
            'average' => $progressNotes->where('performance', 'average')->count(),
            'struggling' => $progressNotes->where('performance', 'struggling')->count(),
            'absent' => $progressNotes->where('performance', 'absent')->count(),
        ];

        // Monthly trend (last 6 months)
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthNotes = ProgressNote::where('student_id', $student->id)
                ->whereHas('progressSheet', function($q) use ($month) {
                    $q->whereMonth('date', $month->month)
                      ->whereYear('date', $month->year);
                })
                ->get();
            
            $monthExcellent = $monthNotes->where('performance', 'excellent')->count();
            $monthGood = $monthNotes->where('performance', 'good')->count();
            $monthTotal = $monthNotes->count() - $monthNotes->where('performance', 'absent')->count();
            
            $stats['monthly_trend'][] = [
                'month' => $month->format('M Y'),
                'rate' => $monthTotal > 0 ? 
                    round((($monthExcellent + $monthGood) / $monthTotal) * 100, 1) : 0,
                'count' => $monthNotes->count(),
            ];
        }

        // ============================================================
        // GROUP NOTES BY CLASS AND DATE
        // ============================================================
        
        $notesByClass = $progressNotes->groupBy(function($note) {
            return $note->progressSheet->class_id;
        });

        $notesByDate = $progressNotes->groupBy(function($note) {
            return $note->progressSheet->date->format('Y-m-d');
        });

        return view('parent.progress.show', compact(
            'student',
            'progressNotes',
            'notesByClass',
            'notesByDate',
            'stats',
            'dateFrom',
            'dateTo'
        ));
    }
}
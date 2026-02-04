<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\HomeworkAssignment;
use App\Models\HomeworkSubmission;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HomeworkController extends Controller
{
    /**
     * Display homework for parent's children
     */
    public function index(Request $request)
    {
        $parent = auth()->user();

        // Get parent's children
        $children = $parent->children()->where('status', 'active')->get();

        // If no children, show message
        if ($children->isEmpty()) {
            return view('parent.homework.index', [
                'children' => $children,
                'selectedChild' => null,
                'homework' => collect(),
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
        
        $query = HomeworkSubmission::where('student_id', $selectedChild->id)
            ->with(['homeworkAssignment.class.teacher', 'homeworkAssignment.teacher']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Class filter
        if ($request->filled('class_id')) {
            $query->whereHas('homeworkAssignment', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('homeworkAssignment', function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'due_date') {
            $query->join('homework_assignments', 'homework_submissions.homework_assignment_id', '=', 'homework_assignments.id')
                  ->orderBy('homework_assignments.due_date', $sortOrder)
                  ->select('homework_submissions.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $homework = $query->paginate(20);

        // ============================================================
        // STATISTICS
        // ============================================================
        
        $allSubmissions = HomeworkSubmission::where('student_id', $selectedChild->id)->get();
        
        $stats = [
            'total' => $allSubmissions->count(),
            'pending' => $allSubmissions->where('status', 'pending')->count(),
            'submitted' => $allSubmissions->where('status', 'submitted')->count(),
            'late' => $allSubmissions->where('status', 'late')->count(),
            'graded' => $allSubmissions->where('status', 'graded')->count(),
            'completion_rate' => 0,
            'average_grade' => 0,
        ];

        // Calculate completion rate
        if ($stats['total'] > 0) {
            $completed = $stats['submitted'] + $stats['late'] + $stats['graded'];
            $stats['completion_rate'] = round(($completed / $stats['total']) * 100, 1);
        }

        // Calculate average grade
        $gradedSubmissions = $allSubmissions->where('status', 'graded')->whereNotNull('grade');
        if ($gradedSubmissions->count() > 0) {
            $scores = $gradedSubmissions->map(function($submission) {
                if (is_numeric($submission->grade)) {
                    return (float) $submission->grade;
                }
                $gradeMap = ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 50];
                $grade = strtoupper(substr($submission->grade, 0, 1));
                return $gradeMap[$grade] ?? 0;
            });
            $stats['average_grade'] = round($scores->avg(), 1);
        }

        // Upcoming homework (next 7 days)
        $stats['upcoming'] = HomeworkSubmission::where('student_id', $selectedChild->id)
            ->whereIn('status', ['pending', 'submitted'])
            ->whereHas('homeworkAssignment', function($q) {
                $q->whereBetween('due_date', [now(), now()->addDays(7)]);
            })
            ->count();

        // Overdue homework
        $stats['overdue'] = HomeworkSubmission::where('student_id', $selectedChild->id)
            ->where('status', 'pending')
            ->whereHas('homeworkAssignment', function($q) {
                $q->where('due_date', '<', now());
            })
            ->count();

        // ============================================================
        // FILTER OPTIONS
        // ============================================================
        
        $classes = $selectedChild->classes()
            ->wherePivot('status', 'active')
            ->orderBy('name')
            ->get();

        return view('parent.homework.index', compact(
            'children',
            'selectedChild',
            'homework',
            'classes',
            'stats'
        ));
    }

    /**
     * Display specific homework assignment details
     */
    public function show(HomeworkAssignment $homework, Request $request)
    {
        $parent = auth()->user();

        // Get parent's children
        $children = $parent->children()->where('status', 'active')->get();

        // Determine which child's submission to show
        $selectedChildId = $request->get('child_id');
        
        if ($selectedChildId) {
            $selectedChild = $children->firstWhere('id', $selectedChildId);
        } else {
            // Find first child who has this homework
            $selectedChild = null;
            foreach ($children as $child) {
                $submission = HomeworkSubmission::where('homework_assignment_id', $homework->id)
                    ->where('student_id', $child->id)
                    ->first();
                if ($submission) {
                    $selectedChild = $child;
                    break;
                }
            }
            
            if (!$selectedChild) {
                abort(404, 'This homework is not assigned to any of your children.');
            }
        }

        // Verify child belongs to parent
        if (!$selectedChild || $selectedChild->parent_id !== $parent->id) {
            abort(403, 'You do not have permission to view this homework.');
        }

        // Get the submission for this child
        $submission = HomeworkSubmission::where('homework_assignment_id', $homework->id)
            ->where('student_id', $selectedChild->id)
            ->firstOrFail();

        // Load relationships
        $homework->load(['class.teacher', 'teacher', 'progressSheet']);

        // Check if overdue
        $isOverdue = $homework->due_date < now() && $submission->status === 'pending';

        // Days until due (or days overdue)
        $daysUntilDue = now()->diffInDays($homework->due_date, false);

        return view('parent.homework.show', compact(
            'homework',
            'submission',
            'selectedChild',
            'children',
            'isOverdue',
            'daysUntilDue'
        ));
    }



       /**
     * Download homework file
     */
    public function download(HomeworkAssignment $homework)
    {
        $teacher = auth()->user();

        // Verify homework belongs to teacher
        if ($homework->teacher_id !== $teacher->id) {
            abort(403, 'You do not have permission to download this file.');
        }

        if (!$homework->file_path || !Storage::disk('public')->exists($homework->file_path)) {
            return redirect()->back()
                ->with('error', 'File not found.');
        }

        // Log activity
        ActivityLog::create([
            'user_id' => $teacher->id,
            'action' => 'downloaded_homework',
            'model_type' => 'HomeworkAssignment',
            'model_id' => $homework->id,
            'description' => "Downloaded homework file: {$homework->title}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return Storage::disk('public')->download(
            $homework->file_path,
            $homework->title . '.' . pathinfo($homework->file_path, PATHINFO_EXTENSION)
        );
    }
}
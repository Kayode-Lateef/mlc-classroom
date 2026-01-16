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
     * Submit homework on behalf of child
     */
    public function submit(Request $request, HomeworkAssignment $homework)
    {
        $parent = auth()->user();

        // Custom validation messages
        $messages = [
            'child_id.required' => 'Please select a child.',
            'child_id.exists' => 'Selected child does not exist.',
            'file.required' => 'Please upload a submission file.',
            'file.file' => 'Invalid file uploaded.',
            'file.max' => 'File size must not exceed 10MB.',
            'file.mimes' => 'File must be a PDF, DOC, DOCX, JPG, JPEG, or PNG.',
        ];

        $validator = Validator::make($request->all(), [
            'child_id' => 'required|exists:students,id',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Please fix the validation errors below.');
        }

        // Verify child belongs to parent
        $child = Student::findOrFail($request->child_id);
        if ($child->parent_id !== $parent->id) {
            abort(403, 'You do not have permission to submit homework for this student.');
        }

        // Get submission
        $submission = HomeworkSubmission::where('homework_assignment_id', $homework->id)
            ->where('student_id', $child->id)
            ->firstOrFail();

        // Check if already submitted or graded
        if (in_array($submission->status, ['submitted', 'late', 'graded'])) {
            return redirect()->back()
                ->with('error', 'This homework has already been submitted.');
        }

        try {
            // Handle file upload
            $file = $request->file('file');
            
            // Additional file size validation
            if ($file->getSize() > 10485760) { // 10MB
                return redirect()->back()
                    ->with('error', 'File size must not exceed 10MB.');
            }

            // Generate unique filename
            $filename = time() . '_' . $child->id . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $filePath = $file->storeAs('homework-submissions', $filename, 'public');

            if (!$filePath) {
                return redirect()->back()
                    ->with('error', 'Failed to upload file. Please try again.');
            }

            // Determine status (late if past due date)
            $status = 'submitted';
            if (now() > $homework->due_date) {
                $status = 'late';
            }

            // Update submission
            $submission->update([
                'file_path' => $filePath,
                'submitted_date' => now(),
                'status' => $status,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $parent->id,
                'action' => 'submitted_homework',
                'model_type' => 'HomeworkSubmission',
                'model_id' => $submission->id,
                'description' => "Parent submitted homework for {$child->full_name}: {$homework->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $message = $status === 'late' 
                ? 'Homework submitted successfully! Note: This submission is late.'
                : 'Homework submitted successfully!';

            return redirect()->route('parent.homework.show', ['homework' => $homework->id, 'child_id' => $child->id])
                ->with('success', $message);

        } catch (\Exception $e) {
            // Delete uploaded file if exists
            if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            
            \Log::error('Homework submission failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to submit homework. Please try again.');
        }
    }
}
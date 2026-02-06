<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeworkAssignment;
use App\Models\HomeworkSubmission;
use App\Models\HomeworkSubmissionTopicGrade;
use App\Models\HomeworkTopic;
use App\Models\ClassModel;
use App\Models\ProgressSheet;
use App\Models\User;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HomeworkController extends Controller
{
    /**
     * Display a listing of homework assignments
     */
    public function index(Request $request)
    {
        $query = HomeworkAssignment::with(['class.teacher', 'teacher', 'submissions']);

        // Date range filter
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $query->whereBetween('assigned_date', [$dateFrom, $dateTo]);

        // Class filter
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Teacher filter
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Status filter (based on due date)
        if ($request->filled('status')) {
            $today = now()->format('Y-m-d');
            switch ($request->status) {
                case 'upcoming':
                    $query->where('due_date', '>', $today);
                    break;
                case 'due_today':
                    $query->whereDate('due_date', $today);
                    break;
                case 'overdue':
                    $query->where('due_date', '<', $today);
                    break;
            }
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $homework = $query->paginate(20);

        // Get filter options
        $classes = ClassModel::orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();

        // Statistics
        $stats = $this->calculateStatistics($dateFrom, $dateTo, $request);

        return view('admin.homework.index', compact(
            'homework',
            'classes',
            'teachers',
            'stats',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show the form for creating a new homework assignment
     */
    public function create()
    {
        $classes = ClassModel::with('teacher')->orderBy('name')->get();
        $progressSheets = ProgressSheet::with('class')
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date', 'desc')
            ->get();

        $topics = HomeworkTopic::active()->orderBy('subject')->orderBy('name')->get();
        
        return view('admin.homework.create', compact('classes', 'progressSheets', 'topics'));
    }

    /**
     * Store a newly created homework assignment
     */
    public function store(Request $request)
    {
        // Custom validation messages
        $messages = [
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'progress_sheet_id.exists' => 'Selected progress sheet does not exist.',
            'title.required' => 'Please enter a homework title.',
            'title.max' => 'Title must not exceed 255 characters.',
            'description.max' => 'Description must not exceed 2000 characters.',
            'assigned_date.required' => 'Please select an assigned date.',
            'assigned_date.date' => 'Please provide a valid assigned date.',
            'due_date.required' => 'Please select a due date.',
            'due_date.date' => 'Please provide a valid due date.',
            'due_date.after_or_equal' => 'Due date must be on or after the assigned date.',
            'file.file' => 'Invalid file uploaded.',
            'file.mimes' => 'File must be a PDF, DOC, DOCX, JPG, JPEG, or PNG.',
            'file.max' => 'File size must not exceed 10MB.',
        ];

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'progress_sheet_id' => 'nullable|exists:progress_sheets,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'assigned_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:assigned_date',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'topic_ids' => 'nullable|array',
            'topic_ids.*' => 'exists:homework_topics,id',
            'topic_ids' => 'nullable|array',
            'topic_ids.*' => 'exists:homework_topics,id',
            'topic_max_scores' => 'nullable|array',
            'topic_max_scores.*' => 'nullable|integer|min:1|max:1000',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors below.');
        }

        try {
            DB::beginTransaction();

            // Handle file upload
            $filePath = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Validate file size
                if ($file->getSize() > 10485760) { // 10MB in bytes
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'File size must not exceed 10MB.');
                }
                
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs('homework-assignments', $filename, 'public');
            }

            // Create homework assignment
            $homework = HomeworkAssignment::create([
                'class_id' => $request->class_id,
                'progress_sheet_id' => $request->progress_sheet_id,
                'title' => $request->title,
                'description' => $request->description,
                'assigned_date' => $request->assigned_date,
                'due_date' => $request->due_date,
                'file_path' => $filePath,
                'teacher_id' => auth()->id(),
            ]);

            // Sync topics with max_score pivot data
            if ($request->has('topic_ids') && is_array($request->topic_ids)) {
                $topicData = [];
                foreach ($request->topic_ids as $topicId) {
                    $topicData[$topicId] = [
                        'max_score' => $request->input("topic_max_scores.{$topicId}", null),
                    ];
                }
                $homework->topics()->sync($topicData);
            }

            // Create submissions for all enrolled students
            $class = ClassModel::with('students')->find($request->class_id);
            foreach ($class->students()->wherePivot('status', 'active')->get() as $student) {
                HomeworkSubmission::create([
                    'homework_assignment_id' => $homework->id,
                    'student_id' => $student->id,
                    'status' => 'pending',
                ]);
            }

            // Notify all parents in the class about the new homework
            NotificationHelper::notifyClassParents(
                $homework->class,
                'New Homework Assigned',
                 "New homework '{$homework->title}' assigned to {$homework->class->name}. Physical submission required by " . $homework->due_date->format('d/m/Y') . ".",
                'homework',
                [
                    'homework_id' => $homework->id,
                    'subject' => $homework->title,
                    'due_date' => $homework->due_date->format('Y-m-d'),
                    'class_name' => $homework->class->name,
                    'url' => route('parent.homework.show', $homework->id)
                ]
            );

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created_homework',
                'model_type' => 'HomeworkAssignment',
                'model_id' => $homework->id,
                'description' => "Created homework: {$homework->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('admin.homework.index')
                ->with('success', 'Homework assigned and parents notified!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if exists
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            
            \Log::error('Homework creation failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create homework assignment. Please try again.');
        }
    }

/**
 * Display the specified homework assignment
 */
public function show(HomeworkAssignment $homework)
{
    $homework->load([
        'class.teacher',
        'teacher',
        'topics',
        'progressSheet',
        'submissions.student.parent',
        'submissions.submittedByUser',
        'submissions.gradedByUser',
        'submissions.topicGrades.topic',
        'submissions.topicGrades.gradedByUser',
    ]);

    // Calculate submission statistics
    $stats = [
        'total_students' => $homework->submissions->count(),
        'submitted' => $homework->submissions->whereIn('status', ['submitted', 'late', 'graded'])->count(),
        'pending' => $homework->submissions->where('status', 'pending')->count(),
        'graded' => $homework->submissions->where('status', 'graded')->count(),
        'late' => $homework->submissions->where('status', 'late')->count(),
    ];

    // Group submissions by status
    $submissionsByStatus = $homework->submissions->groupBy('status');

    return view('admin.homework.show', compact('homework', 'stats', 'submissionsByStatus'));
}

    /**
     * Show the form for editing the specified homework assignment
     */
    public function edit(HomeworkAssignment $homework)
    {
        $homework->load('class', 'progressSheet');
        $classes = ClassModel::with('teacher')->orderBy('name')->get();
        $progressSheets = ProgressSheet::with('class')
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date', 'desc')
            ->get();

        $topics = HomeworkTopic::active()->orderBy('subject')->orderBy('name')->get();
    
        return view('admin.homework.edit', compact('homework', 'classes', 'progressSheets', 'topics'));
    }

    /**
     * Update the specified homework assignment
     */
    public function update(Request $request, HomeworkAssignment $homework)
    {
        // Custom validation messages (same as store)
        $messages = [
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'progress_sheet_id.exists' => 'Selected progress sheet does not exist.',
            'title.required' => 'Please enter a homework title.',
            'title.max' => 'Title must not exceed 255 characters.',
            'description.max' => 'Description must not exceed 2000 characters.',
            'assigned_date.required' => 'Please select an assigned date.',
            'assigned_date.date' => 'Please provide a valid assigned date.',
            'due_date.required' => 'Please select a due date.',
            'due_date.date' => 'Please provide a valid due date.',
            'due_date.after_or_equal' => 'Due date must be on or after the assigned date.',
            'file.file' => 'Invalid file uploaded.',
            'file.mimes' => 'File must be a PDF, DOC, DOCX, JPG, JPEG, or PNG.',
            'file.max' => 'File size must not exceed 10MB.',
        ];

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'progress_sheet_id' => 'nullable|exists:progress_sheets,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'assigned_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:assigned_date',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'topic_ids' => 'nullable|array',
            'topic_ids.*' => 'exists:homework_topics,id',
            'topic_max_scores' => 'nullable|array',
            'topic_max_scores.*' => 'nullable|integer|min:1|max:1000',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors below.');
        }

        try {
            DB::beginTransaction();

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Validate file size
                if ($file->getSize() > 10485760) { // 10MB in bytes
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'File size must not exceed 10MB.');
                }
                
                // Delete old file
                if ($homework->file_path && Storage::disk('public')->exists($homework->file_path)) {
                    Storage::disk('public')->delete($homework->file_path);
                }

                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs('homework-assignments', $filename, 'public');
                $homework->file_path = $filePath;
            }

            // Update homework assignment
            $homework->update([
                'class_id' => $request->class_id,
                'progress_sheet_id' => $request->progress_sheet_id,
                'title' => $request->title,
                'description' => $request->description,
                'assigned_date' => $request->assigned_date,
                'due_date' => $request->due_date,
            ]);

            // Sync topics
            if ($request->has('topic_ids') && is_array($request->topic_ids)) {
                $topicData = [];
                foreach ($request->topic_ids as $index => $topicId) {
                    $topicData[$topicId] = [
                        'max_score' => $request->input("topic_max_scores.{$topicId}", null),
                    ];
                }
                $homework->topics()->sync($topicData);
            } else {
                $homework->topics()->detach();
            }


            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated_homework',
                'model_type' => 'HomeworkAssignment',
                'model_id' => $homework->id,
                'description' => "Updated homework: {$homework->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('admin.homework.show', $homework)
                ->with('success', 'Homework assignment updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Homework update failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update homework assignment. Please try again.');
        }
    }

    /**
     * Remove the specified homework assignment
     */
    public function destroy(HomeworkAssignment $homework)
    {
        try {
            $title = $homework->title;
            $id = $homework->id;

            // Delete file if exists
            if ($homework->file_path && Storage::disk('public')->exists($homework->file_path)) {
                Storage::disk('public')->delete($homework->file_path);
            }

            // Delete submission files
            foreach ($homework->submissions as $submission) {
                if ($submission->file_path && Storage::disk('public')->exists($submission->file_path)) {
                    Storage::disk('public')->delete($submission->file_path);
                }
            }

            $homework->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'deleted_homework',
                'model_type' => 'HomeworkAssignment',
                'model_id' => $id,
                'description' => "Deleted homework: {$title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.homework.index')
                ->with('success', 'Homework assignment deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Homework deletion failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete homework assignment. Please try again.');
        }
    }

    /**
     * Grade a homework submission via AJAX
     */
    public function gradeSubmission(Request $request, $homeworkId)
    {
        $user = auth()->user(); // Get current user (superadmin or teacher)

        $validator = Validator::make($request->all(), [
            'submission_id' => 'required|exists:homework_submissions,id',
            'grade' => 'required|string|max:50',
            'teacher_comments' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $submission = HomeworkSubmission::findOrFail($request->submission_id);
            
            // Verify this submission belongs to the homework
            if ($submission->homework_assignment_id != $homeworkId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission does not belong to this homework'
                ], 403);
            }

            $submission->update([
                'grade' => $request->grade,
                'teacher_comments' => $request->teacher_comments,
                'status' => 'graded',
                'graded_at' => now(),
                'graded_by' => $user->id, // Track who graded it
            ]);

            // NOTIFY PARENT
            NotificationHelper::notifyStudentParent(
                $submission->student,
                'Homework Graded',
                "Homework '{$submission->homeworkAssignment->title}' has been graded. Grade: {$request->grade}",
                'homework_graded',
                [
                    'homework_id' => $submission->homework_assignment_id,
                    'submission_id' => $submission->id,
                    'grade' => $request->grade,
                    'class_name' => $submission->homeworkAssignment->class->name,
                    'url' => route('parent.homework.show', $submission->homeworkAssignment->id)
                ]
            );

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'graded_homework',
                'model_type' => 'HomeworkSubmission',
                'model_id' => $submission->id,
                'description' => "Graded homework for {$submission->student->full_name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Homework graded successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Homework grading failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to grade homework. Please try again.'
            ], 500);
        }
    }


    /**
     * Download homework file
     */
    public function download(HomeworkAssignment $homework)
    {
        if (!$homework->file_path || !Storage::disk('public')->exists($homework->file_path)) {
            return redirect()->back()
                ->with('error', 'File not found.');
        }

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
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

    /**
     * Calculate statistics for homework
     */
    private function calculateStatistics($dateFrom, $dateTo, $request)
    {
        $query = HomeworkAssignment::whereBetween('assigned_date', [$dateFrom, $dateTo]);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $totalHomework = $query->count();
        $today = now()->format('Y-m-d');

        return [
            'total_homework' => $totalHomework,
            'upcoming' => HomeworkAssignment::where('due_date', '>', $today)->count(),
            'due_today' => HomeworkAssignment::whereDate('due_date', $today)->count(),
            'overdue' => HomeworkAssignment::where('due_date', '<', $today)->count(),
        ];
    }



    /**
     * ✅ Mark single submission as submitted
     */
    public function markAsSubmitted(Request $request, HomeworkAssignment $homework)
    {
        $user = auth()->user(); // Get current user (superadmin or teacher)

        $validator = Validator::make($request->all(), [
            'submission_id' => 'required|exists:homework_submissions,id',
            'submission_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Invalid submission data.');
        }

        try {
            $submission = HomeworkSubmission::where('id', $request->submission_id)
                ->where('homework_assignment_id', $homework->id)
                ->firstOrFail();

            // Check if already submitted or graded
            if (in_array($submission->status, ['submitted', 'late', 'graded'])) {
                return redirect()->back()
                    ->with('warning', 'This submission has already been marked as submitted.');
            }

            $isLate = now()->gt($homework->due_date);

            $submission->update([
                'status' => $isLate ? 'late' : 'submitted',
                'submitted_date' => now(),
                'submitted_by' => $user->id,
                'submission_notes' => $request->submission_notes,
            ]);

            // Optional: Send notification to parent
            NotificationHelper::notifyStudentParent(
                $submission->student,
                'Homework Submitted',
                "Homework '{$homework->title}' has been marked as submitted by teacher.",
                'homework_submitted',
                [
                    'homework_id' => $homework->id,
                    'submission_id' => $submission->id,
                    'class_name' => $homework->class->name,
                    'url' => route('parent.homework.show', $homework->id)
                ]
            );

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'marked_submission',
                'model_type' => 'HomeworkSubmission',
                'model_id' => $submission->id,
                'description' => "Marked homework as submitted for student: {$submission->student->full_name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->back()
                ->with('success', 'Homework marked as submitted successfully!');

        } catch (\Exception $e) {
            \Log::error('Failed to mark homework as submitted: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to mark homework as submitted. Please try again.');
        }
    }


    /**
     * ✅ Bulk mark submissions as submitted
     */
    public function bulkMarkAsSubmitted(Request $request, HomeworkAssignment $homework)
    {
        $user = auth()->user(); // Get current user (superadmin or teacher)

        $validator = Validator::make($request->all(), [
            'submission_ids' => 'required|array|min:1',
            'submission_ids.*' => 'exists:homework_submissions,id',
            'submission_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Please select at least one submission.');
        }

        try {
            DB::beginTransaction();

            $isLate = now()->gt($homework->due_date);
            $status = $isLate ? 'late' : 'submitted';
            $count = 0;

            foreach ($request->submission_ids as $submissionId) {
                $submission = HomeworkSubmission::where('id', $submissionId)
                    ->where('homework_assignment_id', $homework->id)
                    ->where('status', 'pending')
                    ->first();

                if ($submission) {
                    $submission->update([
                        'status' => $status,
                        'submitted_date' => now(),
                        'submitted_by' => $user->id,
                        'submission_notes' => $request->submission_notes,
                    ]);
                    
                    // Optional: Send notification to parent
                    NotificationHelper::notifyStudentParent(
                        $submission->student,
                        'Homework Submitted',
                        "Homework '{$homework->title}' has been marked as submitted by teacher.",
                        'homework_submitted',
                        [
                            'homework_id' => $homework->id,
                            'submission_id' => $submission->id,
                            'class_name' => $homework->class->name,
                            'url' => route('parent.homework.show', $homework->id)
                        ]
                    );
                    
                    $count++;
                }
            }

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'bulk_marked_submission',
                'model_type' => 'HomeworkAssignment',
                'model_id' => $homework->id,
                'description' => "Bulk marked {$count} submissions as submitted for: {$homework->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', "{$count} homework submission(s) marked as submitted successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk mark as submitted failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to mark submissions. Please try again.');
        }
    }

    /**
     * ✅ Bulk grade submissions
     */
    public function bulkGrade(Request $request, HomeworkAssignment $homework)
    {
        $user = auth()->user(); // Get current user (superadmin or teacher)

        $validator = Validator::make($request->all(), [
            'submission_ids' => 'required|array|min:1',
            'submission_ids.*' => 'exists:homework_submissions,id',
            'grade' => 'required|string|max:50',
            'teacher_comments' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Please provide valid grading information.');
        }

        try {
            DB::beginTransaction();

            $count = 0;

            foreach ($request->submission_ids as $submissionId) {
                $submission = HomeworkSubmission::where('id', $submissionId)
                    ->where('homework_assignment_id', $homework->id)
                    ->whereIn('status', ['submitted', 'late'])
                    ->first();

                if ($submission) {
                    $submission->update([
                        'grade' => $request->grade,
                        'teacher_comments' => $request->teacher_comments,
                        'status' => 'graded',
                        'graded_at' => now(),
                        'graded_by' => $user->id,
                    ]);

                    // Notify parent
                    NotificationHelper::notifyStudentParent(
                        $submission->student,
                        'Homework Graded',
                        "Homework '{$homework->title}' has been graded. Grade: {$request->grade}",
                        'homework_graded',
                        [
                            'homework_id' => $submission->homework_assignment_id,
                            'submission_id' => $submission->id,
                            'grade' => $request->grade,
                            'class_name' => $submission->homeworkAssignment->class->name,
                            'url' => route('parent.homework.show', $submission->homeworkAssignment->id)
                        ]
                    );

                    $count++;
                }
            }

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'bulk_graded',
                'model_type' => 'HomeworkAssignment',
                'model_id' => $homework->id,
                'description' => "Bulk graded {$count} submissions for: {$homework->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', "{$count} homework submission(s) graded successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk grading failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to grade submissions. Please try again.');
        }
    }

    /**
     * Grade individual topics for a homework submission (Score/Max format)
     */
    public function gradeTopics(Request $request, HomeworkAssignment $homework)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'submission_id' => 'required|exists:homework_submissions,id',
            'topic_grades' => 'required|array|min:1',
            'topic_grades.*.topic_id' => 'required|exists:homework_topics,id',
            'topic_grades.*.score' => 'required|integer|min:0',
            'topic_grades.*.max_score' => 'required|integer|min:1',
            'topic_grades.*.comments' => 'nullable|string|max:500',
        ], [
            'topic_grades.*.score.required' => 'Please enter a score for each topic.',
            'topic_grades.*.score.min' => 'Score cannot be negative.',
            'topic_grades.*.max_score.required' => 'Max score is required for each topic.',
            'topic_grades.*.max_score.min' => 'Max score must be at least 1.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Please provide valid topic scores.');
        }

        try {
            DB::beginTransaction();

            $submission = HomeworkSubmission::where('id', $request->submission_id)
                ->where('homework_assignment_id', $homework->id)
                ->firstOrFail();

            // Only allow grading submitted/late/graded submissions
            if (!in_array($submission->status, ['submitted', 'late', 'graded'])) {
                return redirect()->back()
                    ->with('error', 'Cannot grade topics for a pending submission.');
            }

            $gradedCount = 0;

            foreach ($request->topic_grades as $topicGrade) {
                // Verify topic belongs to this homework assignment
                $topicExists = $homework->topics()
                    ->where('homework_topics.id', $topicGrade['topic_id'])
                    ->exists();

                if (!$topicExists) {
                    continue;
                }

                // Validate score does not exceed max_score
                if ($topicGrade['score'] > $topicGrade['max_score']) {
                    continue; // Skip invalid — score can't exceed max
                }

                HomeworkSubmissionTopicGrade::updateOrCreate(
                    [
                        'homework_submission_id' => $submission->id,
                        'homework_topic_id' => $topicGrade['topic_id'],
                    ],
                    [
                        'score' => $topicGrade['score'],
                        'max_score' => $topicGrade['max_score'],
                        'comments' => $topicGrade['comments'] ?? null,
                        'graded_by' => $user->id, // Track who graded it (admin/superadmin)
                        'graded_at' => now(),
                    ]
                );

                $gradedCount++;
            }

            // If all topics graded, update submission overall status if not already graded
            if ($gradedCount > 0 && $submission->status !== 'graded') {
                $submission->update([
                    'status' => 'graded',
                    'graded_at' => now(),
                    'graded_by' => $user->id,
                ]);
            }

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'graded_topics',
                'model_type' => 'HomeworkSubmission',
                'model_id' => $submission->id,
                'description' => "Graded {$gradedCount} topic(s) for {$submission->student->full_name} on: {$homework->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', "{$gradedCount} topic score(s) saved successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Topic grading failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to save topic scores. Please try again.');
        }
    }
}
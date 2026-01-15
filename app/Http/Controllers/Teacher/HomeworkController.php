<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\HomeworkAssignment;
use App\Models\HomeworkSubmission;
use App\Models\ClassModel;
use App\Models\ProgressSheet;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HomeworkController extends Controller
{
    /**
     * Display a listing of homework assignments (teacher's classes only)
     */
    public function index(Request $request)
    {
        $teacher = auth()->user();

        $query = HomeworkAssignment::with(['class.teacher', 'submissions'])
            ->where('teacher_id', $teacher->id); // ONLY teacher's homework

        // Date range filter
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $query->whereBetween('assigned_date', [$dateFrom, $dateTo]);

        // Class filter
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Status filter
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

        // Get filter options (only teacher's classes)
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();

        // Statistics
        $stats = $this->calculateStatistics($dateFrom, $dateTo, $request, $teacher->id);

        return view('teacher.homework.index', compact(
            'homework',
            'classes',
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
        $teacher = auth()->user();

        // Only teacher's classes
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->with('teacher')
            ->orderBy('name')
            ->get();

        // Recent progress sheets from teacher's classes
        $progressSheets = ProgressSheet::whereHas('class', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->with('class')
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date', 'desc')
            ->get();

        return view('teacher.homework.create', compact('classes', 'progressSheets'));
    }

    /**
     * Store a newly created homework assignment
     */
    public function store(Request $request)
    {
        $teacher = auth()->user();

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
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors below.');
        }

        // Verify class belongs to teacher
        $class = ClassModel::where('id', $request->class_id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

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
                'teacher_id' => $teacher->id,
            ]);

            // Create submissions for all enrolled students
            $students = $class->students()->wherePivot('status', 'active')->get();
            foreach ($students as $student) {
                HomeworkSubmission::create([
                    'homework_assignment_id' => $homework->id,
                    'student_id' => $student->id,
                    'status' => 'pending',
                ]);
            }

            // Notify all parents in the class
            NotificationHelper::notifyClassParents(
                $homework->class,
                'New Homework Assigned',
                "New homework '{$homework->title}' assigned. Due: {$homework->due_date->format('d M Y')}",
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
                'user_id' => $teacher->id,
                'action' => 'created_homework',
                'model_type' => 'HomeworkAssignment',
                'model_id' => $homework->id,
                'description' => "Created homework: {$homework->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('teacher.homework.index')
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
        $teacher = auth()->user();

        // Verify homework belongs to teacher
        if ($homework->teacher_id !== $teacher->id) {
            abort(403, 'You do not have permission to view this homework.');
        }

        $homework->load([
            'class.teacher',
            'progressSheet',
            'submissions.student.parent'
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

        return view('teacher.homework.show', compact('homework', 'stats', 'submissionsByStatus'));
    }

    /**
     * Show the form for editing the specified homework assignment
     */
    public function edit(HomeworkAssignment $homework)
    {
        $teacher = auth()->user();

        // Verify homework belongs to teacher
        if ($homework->teacher_id !== $teacher->id) {
            abort(403, 'You do not have permission to edit this homework.');
        }

        $homework->load('class', 'progressSheet');

        // Only teacher's classes
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->with('teacher')
            ->orderBy('name')
            ->get();

        // Recent progress sheets from teacher's classes
        $progressSheets = ProgressSheet::whereHas('class', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->with('class')
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date', 'desc')
            ->get();

        return view('teacher.homework.edit', compact('homework', 'classes', 'progressSheets'));
    }

    /**
     * Update the specified homework assignment
     */
    public function update(Request $request, HomeworkAssignment $homework)
    {
        $teacher = auth()->user();

        // Verify homework belongs to teacher
        if ($homework->teacher_id !== $teacher->id) {
            abort(403, 'You do not have permission to edit this homework.');
        }

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
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors below.');
        }

        // Verify new class also belongs to teacher
        $class = ClassModel::where('id', $request->class_id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

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

            // Log activity
            ActivityLog::create([
                'user_id' => $teacher->id,
                'action' => 'updated_homework',
                'model_type' => 'HomeworkAssignment',
                'model_id' => $homework->id,
                'description' => "Updated homework: {$homework->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('teacher.homework.show', $homework)
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
        $teacher = auth()->user();

        // Verify homework belongs to teacher
        if ($homework->teacher_id !== $teacher->id) {
            abort(403, 'You do not have permission to delete this homework.');
        }

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
                'user_id' => $teacher->id,
                'action' => 'deleted_homework',
                'model_type' => 'HomeworkAssignment',
                'model_id' => $id,
                'description' => "Deleted homework: {$title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('teacher.homework.index')
                ->with('success', 'Homework assignment deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Homework deletion failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete homework assignment. Please try again.');
        }
    }

    /**
     * Grade a homework submission
     */
    public function grade(Request $request, HomeworkAssignment $homework)
    {
        $teacher = auth()->user();

        // Verify homework belongs to teacher
        if ($homework->teacher_id !== $teacher->id) {
            abort(403, 'You do not have permission to grade this homework.');
        }

        // Custom validation messages
        $messages = [
            'submission_id.required' => 'Please select a submission to grade.',
            'submission_id.exists' => 'Selected submission does not exist.',
            'grade.required' => 'Please enter a grade for this submission.',
            'grade.max' => 'Grade must not exceed 50 characters.',
            'teacher_comments.max' => 'Teacher comments must not exceed 1000 characters.',
        ];

        $validator = Validator::make($request->all(), [
            'submission_id' => 'required|exists:homework_submissions,id',
            'grade' => 'required|string|max:50',
            'teacher_comments' => 'nullable|string|max:1000',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Please provide a valid grade.');
        }

        try {
            $submission = HomeworkSubmission::findOrFail($request->submission_id);

            // Verify submission belongs to this homework
            if ($submission->homework_assignment_id !== $homework->id) {
                return redirect()->back()
                    ->with('error', 'Invalid submission for this homework.');
            }

            $submission->update([
                'grade' => $request->grade,
                'teacher_comments' => $request->teacher_comments,
                'status' => 'graded',
                'graded_at' => now(),
            ]);

            // NOTIFY PARENT
            NotificationHelper::notifyStudentParent(
                $submission->student,
                'Homework Graded',
                "Homework '{$homework->title}' has been graded. Grade: {$request->grade}",
                'homework_graded',
                [
                    'homework_id' => $homework->id,
                    'submission_id' => $submission->id,
                    'grade' => $request->grade,
                    'class_name' => $homework->class->name,
                    'url' => route('parent.homework.show', $homework->id)
                ]
            );

            // Log activity
            ActivityLog::create([
                'user_id' => $teacher->id,
                'action' => 'graded_homework',
                'model_type' => 'HomeworkSubmission',
                'model_id' => $submission->id,
                'description' => "Graded homework for {$submission->student->full_name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->back()
                ->with('success', 'Homework graded and parent notified!');

        } catch (\Exception $e) {
            \Log::error('Homework grading failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to grade homework. Please try again.');
        }
    }

    /**
     * View submissions for a homework assignment
     */
    public function submissions(HomeworkAssignment $homework)
    {
        $teacher = auth()->user();

        // Verify homework belongs to teacher
        if ($homework->teacher_id !== $teacher->id) {
            abort(403, 'You do not have permission to view these submissions.');
        }

        $homework->load(['class', 'submissions.student.parent']);

        // Group submissions by status
        $submissionsByStatus = $homework->submissions->groupBy('status');

        // Statistics
        $stats = [
            'total' => $homework->submissions->count(),
            'pending' => $homework->submissions->where('status', 'pending')->count(),
            'submitted' => $homework->submissions->where('status', 'submitted')->count(),
            'late' => $homework->submissions->where('status', 'late')->count(),
            'graded' => $homework->submissions->where('status', 'graded')->count(),
        ];

        return view('teacher.homework.submissions', compact('homework', 'submissionsByStatus', 'stats'));
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

    /**
     * Calculate statistics for homework
     */
    private function calculateStatistics($dateFrom, $dateTo, $request, $teacherId)
    {
        $query = HomeworkAssignment::where('teacher_id', $teacherId)
            ->whereBetween('assigned_date', [$dateFrom, $dateTo]);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $totalHomework = $query->count();
        $today = now()->format('Y-m-d');

        return [
            'total_homework' => $totalHomework,
            'upcoming' => HomeworkAssignment::where('teacher_id', $teacherId)
                ->where('due_date', '>', $today)
                ->count(),
            'due_today' => HomeworkAssignment::where('teacher_id', $teacherId)
                ->whereDate('due_date', $today)
                ->count(),
            'overdue' => HomeworkAssignment::where('teacher_id', $teacherId)
                ->where('due_date', '<', $today)
                ->count(),
        ];
    }
}
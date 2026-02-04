<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ProgressSheet;
use App\Models\ProgressNote;
use App\Models\ClassModel;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProgressSheetController extends Controller
{
    /**
     * Display a listing of progress sheets (teacher's classes only)
     */
    public function index(Request $request)
    {
        $teacher = auth()->user();

        $query = ProgressSheet::with(['class', 'progressNotes'])
            ->where('teacher_id', $teacher->id); // ONLY teacher's progress sheets

        // Date range filter
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $query->whereBetween('date', [$dateFrom, $dateTo]);

        // Class filter
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('topic', 'like', '%' . $request->search . '%')
                  ->orWhere('objective', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $progressSheets = $query->paginate(12);

        // Get filter options (only teacher's classes)
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();

        // Statistics
        $stats = $this->calculateStatistics($dateFrom, $dateTo, $request, $teacher->id);

        return view('teacher.progress-sheets.index', compact(
            'progressSheets',
            'classes',
            'stats',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show the form for creating a new progress sheet
     */
    public function create()
    {
        $teacher = auth()->user();

        // Only teacher's classes
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->with('teacher')
            ->orderBy('name')
            ->get();

        return view('teacher.progress-sheets.create', compact('classes'));
    }

    /**
     * Store a newly created progress sheet
     */
    public function store(Request $request)
    {
        $teacher = auth()->user();

        // Custom validation messages
        $messages = [
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'date.required' => 'Progress sheet date is required.',
            'date.date' => 'Please enter a valid date.',
            'date.before_or_equal' => 'Cannot create progress sheet for future dates.',
            'schedule_id.exists' => 'Selected schedule does not exist.',
            'topic.required' => 'Lesson topic is required.',
            'topic.max' => 'Topic must not exceed 255 characters.',
            'objective.max' => 'Objective must not exceed 1000 characters.',
            'notes.max' => 'General notes must not exceed 2000 characters.',
            'student_notes.array' => 'Invalid student notes format.',
            'student_notes.*.student_id.required' => 'Student ID is required for each note.',
            'student_notes.*.student_id.exists' => 'Selected student does not exist.',
            'student_notes.*.performance.in' => 'Invalid performance level. Choose: excellent, good, average, struggling, or absent.',
            'student_notes.*.notes.max' => 'Individual student notes must not exceed 500 characters.',
        ];

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date|before_or_equal:today',
            'schedule_id' => 'nullable|exists:schedules,id',
            'topic' => 'required|string|max:255',
            'objective' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'student_notes' => 'nullable|array',
            'student_notes.*.student_id' => 'required|exists:students,id',
            'student_notes.*.performance' => 'nullable|in:excellent,good,average,struggling,absent',
            'student_notes.*.notes' => 'nullable|string|max:500',
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

        // Check for duplicate
        $existingSheet = ProgressSheet::where('class_id', $request->class_id)
            ->where('date', $request->date)
            ->first();

        if ($existingSheet) {
            return back()->withInput()->with('error', 'A progress sheet already exists for this class on this date. Please edit the existing one or choose a different date.');
        }

        try {
            DB::beginTransaction();

            // Create progress sheet
            $progressSheet = ProgressSheet::create([
                'class_id' => $request->class_id,
                'schedule_id' => $request->schedule_id,
                'date' => $request->date,
                'topic' => $request->topic,
                'objective' => $request->objective,
                'notes' => $request->notes,
                'teacher_id' => $teacher->id,
            ]);

            $studentsWithNotes = [];
            $performanceStats = [
                'excellent' => 0,
                'good' => 0,
                'average' => 0,
                'struggling' => 0,
                'absent' => 0,
                'total_notes' => 0,
            ];

            // Create student notes
            if ($request->has('student_notes')) {
                foreach ($request->student_notes as $noteData) {
                    if (!empty($noteData['performance']) || !empty($noteData['notes'])) {
                        ProgressNote::create([
                            'progress_sheet_id' => $progressSheet->id,
                            'student_id' => $noteData['student_id'],
                            'performance' => $noteData['performance'] ?? null,
                            'notes' => $noteData['notes'] ?? null,
                        ]);
                        
                        $studentsWithNotes[] = $noteData['student_id'];
                        $performanceStats['total_notes']++;
                        
                        if (!empty($noteData['performance'])) {
                            $performanceStats[$noteData['performance']]++;
                        }
                    }
                }
            }

            // Log activity
            ActivityLog::create([
                'user_id' => $teacher->id,
                'action' => 'created_progress_sheet',
                'model_type' => 'ProgressSheet',
                'model_id' => $progressSheet->id,
                'description' => "Created progress sheet: {$progressSheet->topic} for {$class->name} ({$performanceStats['total_notes']} student notes)",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Send notifications
            if (!empty($studentsWithNotes)) {
                $this->notifyParentsOfProgressSheet($progressSheet, $studentsWithNotes);
            }

            return redirect()->route('teacher.progress-sheets.index')
                ->with('success', 'Progress sheet created successfully! ' . count($studentsWithNotes) . ' parent notifications sent.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Progress sheet creation failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create progress sheet. Please try again.');
        }
    }

    /**
     * Display the specified progress sheet
     */
    public function show(ProgressSheet $progressSheet)
    {
        $teacher = auth()->user();

        // Verify progress sheet belongs to teacher
        if ($progressSheet->teacher_id !== $teacher->id) {
            abort(403, 'You do not have permission to view this progress sheet.');
        }

        $progressSheet->load([
            'class',
            'schedule',
            'progressNotes.student.parent'
        ]);

        // Group notes by performance
        $notesByPerformance = $progressSheet->progressNotes->groupBy('performance');

        // Calculate statistics
        $stats = [
            'total_students' => $progressSheet->progressNotes->count(),
            'excellent' => $progressSheet->progressNotes->where('performance', 'excellent')->count(),
            'good' => $progressSheet->progressNotes->where('performance', 'good')->count(),
            'average' => $progressSheet->progressNotes->where('performance', 'average')->count(),
            'struggling' => $progressSheet->progressNotes->where('performance', 'struggling')->count(),
            'absent' => $progressSheet->progressNotes->where('performance', 'absent')->count(),
        ];

        return view('teacher.progress-sheets.show', compact(
            'progressSheet',
            'notesByPerformance',
            'stats'
        ));
    }

    /**
     * Show the form for editing the specified progress sheet
     */
    public function edit(ProgressSheet $progressSheet)
    {
        $teacher = auth()->user();

        // Verify progress sheet belongs to teacher
        if ($progressSheet->teacher_id !== $teacher->id) {
            abort(403, 'You do not have permission to edit this progress sheet.');
        }

        $progressSheet->load(['class', 'schedule', 'progressNotes.student']);

        // Only teacher's classes
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->with('teacher')
            ->orderBy('name')
            ->get();

        $students = Student::where('status', 'active')
            ->whereHas('classes', function($q) use ($progressSheet) {
                $q->where('classes.id', $progressSheet->class_id);
            })
            ->orderBy('first_name')
            ->get();

        return view('teacher.progress-sheets.edit', compact(
            'progressSheet',
            'classes',
            'students'
        ));
    }

    /**
     * Update the specified progress sheet
     */
    public function update(Request $request, ProgressSheet $progressSheet)
    {
        $teacher = auth()->user();

        // Verify progress sheet belongs to teacher
        if ($progressSheet->teacher_id !== $teacher->id) {
            abort(403, 'You do not have permission to edit this progress sheet.');
        }

        // Same validation as store
        $messages = [
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'date.required' => 'Progress sheet date is required.',
            'date.date' => 'Please enter a valid date.',
            'date.before_or_equal' => 'Cannot create progress sheet for future dates.',
            'schedule_id.exists' => 'Selected schedule does not exist.',
            'topic.required' => 'Lesson topic is required.',
            'topic.max' => 'Topic must not exceed 255 characters.',
            'objective.max' => 'Objective must not exceed 1000 characters.',
            'notes.max' => 'General notes must not exceed 2000 characters.',
            'student_notes.array' => 'Invalid student notes format.',
            'student_notes.*.student_id.required' => 'Student ID is required for each note.',
            'student_notes.*.student_id.exists' => 'Selected student does not exist.',
            'student_notes.*.performance.in' => 'Invalid performance level.',
            'student_notes.*.notes.max' => 'Individual student notes must not exceed 500 characters.',
        ];

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date|before_or_equal:today',
            'schedule_id' => 'nullable|exists:schedules,id',
            'topic' => 'required|string|max:255',
            'objective' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'student_notes' => 'nullable|array',
            'student_notes.*.student_id' => 'required|exists:students,id',
            'student_notes.*.performance' => 'nullable|in:excellent,good,average,struggling,absent',
            'student_notes.*.notes' => 'nullable|string|max:500',
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

            // Update progress sheet
            $progressSheet->update([
                'class_id' => $request->class_id,
                'schedule_id' => $request->schedule_id,
                'date' => $request->date,
                'topic' => $request->topic,
                'objective' => $request->objective,
                'notes' => $request->notes,
            ]);

            // Delete existing notes
            $progressSheet->progressNotes()->delete();

            $totalNotes = 0;

            // Create new student notes
            if ($request->has('student_notes')) {
                foreach ($request->student_notes as $noteData) {
                    if (!empty($noteData['performance']) || !empty($noteData['notes'])) {
                        ProgressNote::create([
                            'progress_sheet_id' => $progressSheet->id,
                            'student_id' => $noteData['student_id'],
                            'performance' => $noteData['performance'] ?? null,
                            'notes' => $noteData['notes'] ?? null,
                        ]);
                        $totalNotes++;
                    }
                }
            }

            // Log activity
            ActivityLog::create([
                'user_id' => $teacher->id,
                'action' => 'updated_progress_sheet',
                'model_type' => 'ProgressSheet',
                'model_id' => $progressSheet->id,
                'description' => "Updated progress sheet: {$progressSheet->topic} for {$progressSheet->class->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('teacher.progress-sheets.show', $progressSheet)
                ->with('success', 'Progress sheet updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Progress sheet update failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update progress sheet. Please try again.');
        }
    }

    /**
     * Remove the specified progress sheet
     */
    public function destroy(ProgressSheet $progressSheet)
    {
        $teacher = auth()->user();

        // Verify progress sheet belongs to teacher
        if ($progressSheet->teacher_id !== $teacher->id) {
            abort(403, 'You do not have permission to delete this progress sheet.');
        }

        try {
            $topic = $progressSheet->topic;
            $id = $progressSheet->id;

            $progressSheet->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => $teacher->id,
                'action' => 'deleted_progress_sheet',
                'model_type' => 'ProgressSheet',
                'model_id' => $id,
                'description' => "Deleted progress sheet: {$topic}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('teacher.progress-sheets.index')
                ->with('success', 'Progress sheet deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Progress sheet deletion failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete progress sheet. Please try again.');
        }
    }

   /**
     * AJAX endpoint - Get students for a class
     */
    public function getStudents(Request $request)
    {
        try {
            $teacher = auth()->user();

            if (!$request->has('class_id')) {
                return response()->json(['error' => 'Class ID required'], 400);
            }

            $classId = $request->class_id;

            // ✅ FIXED: Verify class belongs to teacher
            $class = ClassModel::where('id', $classId)
                ->where('teacher_id', $teacher->id)
                ->first();

            if (!$class) {
                return response()->json(['error' => 'Unauthorized - Class not assigned to you'], 403);
            }

            // ✅ FIXED: Get students with profile_photo field (needed for avatar display)
            $students = Student::where('status', 'active')
                ->whereHas('enrollments', function($q) use ($classId) {
                    $q->where('class_id', $classId)
                      ->where('status', 'active');  // Only active enrollments
                })
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name', 'profile_photo']);  // ✅ Added profile_photo

            // ✅ FIXED: Get schedules for the class
            $schedules = Schedule::where('class_id', $classId)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get(['id', 'day_of_week', 'start_time', 'end_time']);

            // ✅ FIXED: Return in same format as admin (with 'students' and 'schedules' keys)
            return response()->json([
                'students' => $students,
                'schedules' => $schedules,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching students for teacher: ' . $e->getMessage(), [
                'teacher_id' => auth()->id(),
                'class_id' => $request->class_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Failed to load students'], 500);
        }
    }


    /**
     * Notify parents of progress sheet
     */
    private function notifyParentsOfProgressSheet(ProgressSheet $progressSheet, array $studentIds)
    {
        try {
            $students = Student::with('parent')->whereIn('id', $studentIds)->get();

            foreach ($students as $student) {
                if (!$student->parent) {
                    continue;
                }

                NotificationHelper::notifyStudentParent(
                    $student,
                    'Progress Report Available',
                    "New progress report for {$student->first_name} in {$progressSheet->class->name}",
                    'progress_report',
                    [
                        'progress_sheet_id' => $progressSheet->id,
                        'class_name' => $progressSheet->class->name,
                        'topic' => $progressSheet->topic,
                        'date' => $progressSheet->date,
                        'url' => route('parent.students.show', $student->id)
                    ]
                );
            }

        } catch (\Exception $e) {
            \Log::error('Failed to send progress sheet notifications: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics($dateFrom, $dateTo, $request, $teacherId)
    {
        $query = ProgressSheet::where('teacher_id', $teacherId)
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $totalSheets = $query->count();
        
        $notesQuery = ProgressNote::whereHas('progressSheet', function($q) use ($dateFrom, $dateTo, $request, $teacherId) {
            $q->where('teacher_id', $teacherId)
              ->whereBetween('date', [$dateFrom, $dateTo]);
            if ($request->filled('class_id')) {
                $q->where('class_id', $request->class_id);
            }
        });

        $totalNotes = $notesQuery->count();

        return [
            'total_sheets' => $totalSheets,
            'total_notes' => $totalNotes,
            'this_week' => ProgressSheet::where('teacher_id', $teacherId)
                ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'this_month' => ProgressSheet::where('teacher_id', $teacherId)
                ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
        ];
    }
}
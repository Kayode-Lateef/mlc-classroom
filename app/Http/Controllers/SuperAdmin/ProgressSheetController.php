<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ProgressSheet;
use App\Models\ProgressNote;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProgressSheetController extends Controller
{
    /**
     * Display a listing of all progress sheets
     */
    public function index(Request $request)
    {
        $query = ProgressSheet::with(['class', 'teacher', 'schedule', 'progressNotes']);

        // Date range filter
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $query->whereBetween('date', [$dateFrom, $dateTo]);

        // Class filter
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Teacher filter
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Search by topic/objective
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

        $progressSheets = $query->paginate(20);

        // Get filter options
        $classes = ClassModel::orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();

        // Statistics
        $stats = $this->calculateStatistics($dateFrom, $dateTo, $request);

        return view('superadmin.progress-sheets.index', compact(
            'progressSheets',
            'classes',
            'teachers',
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
        $classes = ClassModel::with('teacher')->orderBy('name')->get();
        
        return view('superadmin.progress-sheets.create', compact('classes'));
    }

    /**
     * Store a newly created progress sheet
     */
    public function store(Request $request)
    {
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below.');
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
                'teacher_id' => auth()->id(),
            ]);

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
                    }
                }
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created_progress_sheet',
                'model_type' => 'ProgressSheet',
                'model_id' => $progressSheet->id,
                'description' => "Created progress sheet: {$progressSheet->topic}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('superadmin.progress-sheets.index')
                ->with('success', 'Progress sheet created successfully!');

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
        $progressSheet->load([
            'class',
            'teacher',
            'schedule',
            'progressNotes.student'
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

        return view('superadmin.progress-sheets.show', compact(
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
        $progressSheet->load(['class', 'schedule', 'progressNotes.student']);
        $classes = ClassModel::with('teacher')->orderBy('name')->get();
        $students = Student::where('status', 'active')
            ->whereHas('classes', function($q) use ($progressSheet) {
                $q->where('classes.id', $progressSheet->class_id);
            })
            ->orderBy('first_name')
            ->get();

        return view('superadmin.progress-sheets.edit', compact(
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below.');
        }

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
                    }
                }
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated_progress_sheet',
                'model_type' => 'ProgressSheet',
                'model_id' => $progressSheet->id,
                'description' => "Updated progress sheet: {$progressSheet->topic}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('superadmin.progress-sheets.show', $progressSheet)
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
        try {
            $topic = $progressSheet->topic;
            $id = $progressSheet->id;

            $progressSheet->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'deleted_progress_sheet',
                'model_type' => 'ProgressSheet',
                'model_id' => $id,
                'description' => "Deleted progress sheet: {$topic}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('superadmin.progress-sheets.index')
                ->with('success', 'Progress sheet deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Progress sheet deletion failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete progress sheet. Please try again.');
        }
    }

    /**
     * Get students for a class (AJAX endpoint)
     */
    public function getStudents(Request $request)
    {
        $classId = $request->get('class_id');
        
        if (!$classId) {
            return response()->json(['error' => 'Class ID is required'], 400);
        }

        $students = Student::where('status', 'active')
            ->whereHas('classes', function($q) use ($classId) {
                $q->where('classes.id', $classId);
            })
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'profile_photo']);

        $schedules = Schedule::where('class_id', $classId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'students' => $students,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Calculate statistics for progress sheets
     */
    private function calculateStatistics($dateFrom, $dateTo, $request)
    {
        $query = ProgressSheet::whereBetween('date', [$dateFrom, $dateTo]);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $totalSheets = $query->count();
        $totalNotes = ProgressNote::whereHas('progressSheet', function($q) use ($dateFrom, $dateTo, $request) {
            $q->whereBetween('date', [$dateFrom, $dateTo]);
            if ($request->filled('class_id')) {
                $q->where('class_id', $request->class_id);
            }
            if ($request->filled('teacher_id')) {
                $q->where('teacher_id', $request->teacher_id);
            }
        })->count();

        return [
            'total_sheets' => $totalSheets,
            'total_notes' => $totalNotes,
            'this_week' => ProgressSheet::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => ProgressSheet::whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->count(),
        ];
    }
}
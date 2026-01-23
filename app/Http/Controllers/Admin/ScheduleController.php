<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules
     */
    public function index(Request $request)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('view schedules')) {
            abort(403, 'You do not have permission to view schedules.');
        }

        try {
            $query = Schedule::with(['class.teacher', 'class.enrollments']);

            // Filter by class
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            // Filter by day
            if ($request->filled('day_of_week')) {
                $query->where('day_of_week', $request->day_of_week);
            }

            // Filter by teacher
            if ($request->filled('teacher_id')) {
                $query->whereHas('class', function($q) use ($request) {
                    $q->where('teacher_id', $request->teacher_id);
                });
            }

            // ✅ ADDED: Filter by time range
            if ($request->filled('time_from')) {
                $query->where('start_time', '>=', $request->time_from . ':00');
            }
            if ($request->filled('time_to')) {
                $query->where('end_time', '<=', $request->time_to . ':59');
            }

            // View type (list or calendar)
            $viewType = $request->get('view', 'calendar');

            if ($viewType === 'list') {
                // List view - paginated
                $schedules = $query->orderBy('day_of_week')
                    ->orderBy('start_time')
                    ->paginate(config('app.pagination.schedules', 50));
                $schedulesByDay = null;
            } else {
                // Calendar view - group by day
                $schedules = $query->orderBy('day_of_week')
                    ->orderBy('start_time')
                    ->get();
                $schedulesByDay = $schedules->groupBy('day_of_week');
            }

            // Get filter options
            $classes = ClassModel::with('teacher')->orderBy('name')->get();
            $teachers = User::where('role', 'teacher')
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

            // ✅ ENHANCED: Statistics with additional metrics
            $stats = [
                'total_schedules' => Schedule::count(),
                'unique_classes' => Schedule::distinct('class_id')->count('class_id'),
                'this_week' => Schedule::whereIn('day_of_week', $this->getWeekDays())->count(),
                'conflicts' => $this->detectConflicts()->count(),
                'active_teachers' => User::where('role', 'teacher')
                    ->where('status', 'active')
                    ->whereHas('teachingClasses.schedules')
                    ->count(),
                'recurring_schedules' => Schedule::where('recurring', true)->count(),
            ];

            return view('admin.schedules.index', compact(
                'schedules',
                'schedulesByDay',
                'classes',
                'teachers',
                'stats',
                'viewType'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading schedules: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An error occurred while loading schedules. Please try again.');
        }
    }

    /**
     * Show the form for creating a new schedule
     */
    public function create()
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('create schedules')) {
            abort(403, 'You do not have permission to create schedules.');
        }

        try {
            // ✅ ENHANCED: Only show classes with active teachers
            $classes = ClassModel::with('teacher')
                ->whereHas('teacher', function($q) {
                    $q->where('status', 'active');
                })
                ->orderBy('name')
                ->get();

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

            // ✅ ADDED: Get existing schedules for conflict preview
            $existingSchedules = Schedule::with('class')
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week');

            return view('admin.schedules.create', compact('classes', 'days', 'existingSchedules'));

        } catch (\Exception $e) {
            Log::error('Error loading schedule creation form: ' . $e->getMessage());
            
            return back()->with('error', 'An error occurred while loading the form. Please try again.');
        }
    }

/**
     * Store a newly created schedule
     */
    public function store(Request $request)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('create schedules')) {
            abort(403, 'You do not have permission to create schedules.');
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'recurring' => 'boolean',
        ], [
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'day_of_week.required' => 'Please select a day of the week.',
            'day_of_week.in' => 'Invalid day selected. Please choose a valid day.',
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in HH:MM format (e.g., 09:00).',
            'end_time.required' => 'End time is required.',
            'end_time.date_format' => 'End time must be in HH:MM format (e.g., 14:00).',
            'end_time.after' => 'End time must be after start time.',
        ]);

        // ✅ FIXED: Pre-validation checks BEFORE transaction starts
        // This allows proper error messages to be shown via Toastr/SweetAlert

        // ✅ ENHANCED: Verify class exists and has active teacher
        $class = ClassModel::with('teacher')->find($validated['class_id']);
        
        if (!$class) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Class not found. Please select a valid class.');
        }
        
        if (!$class->teacher) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cannot create schedule: Class has no assigned teacher. Please assign a teacher to this class first.');
        }

        if ($class->teacher->status !== 'active') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cannot create schedule: The assigned teacher (' . $class->teacher->name . ') is not active.');
        }

        // ✅ ENHANCED: Time validation - minimum 30 minutes duration
        $startTime = Carbon::createFromFormat('H:i', $validated['start_time']);
        $endTime = Carbon::createFromFormat('H:i', $validated['end_time']);
        $durationMinutes = $startTime->diffInMinutes($endTime);

        if ($durationMinutes < 30) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Schedule duration must be at least 30 minutes. Current duration: ' . $durationMinutes . ' minutes.');
        }

        // ✅ FIXED: Check for conflicts BEFORE starting transaction
        $conflict = $this->checkScheduleConflict(
            $validated['class_id'],
            $validated['day_of_week'],
            $validated['start_time'],
            $validated['end_time']
        );

        if ($conflict) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Schedule conflict detected: ' . $conflict);
        }

        // ✅ NOW start the database transaction (only for actual creation)
        DB::beginTransaction();

        try {
            // Create schedule with proper time format
            $schedule = Schedule::create([
                'class_id' => $validated['class_id'],
                'day_of_week' => $validated['day_of_week'],
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'recurring' => $request->has('recurring') ? true : false,
            ]);

            // ✅ ENHANCED: Activity log with duration details
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created_schedule',
                'model_type' => 'Schedule',
                'model_id' => $schedule->id,
                'description' => "Created schedule for class: {$class->name} on {$schedule->day_of_week} ({$startTime->format('H:i')} - {$endTime->format('H:i')}, {$durationMinutes} minutes)",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // ✅ FIXED: Commit transaction BEFORE sending notifications
            // This ensures the schedule is saved even if notifications fail
            DB::commit();

            // ✅ ADDED: Notify teacher and enrolled students' parents
            // Do this AFTER commit so transaction doesn't interfere
            try {
                // Notify teacher
                if ($class->teacher) {
                    NotificationHelper::notifyUser(
                        $class->teacher,
                        'New Schedule Created',
                        "A new schedule has been created for {$class->name} on {$schedule->day_of_week} from {$startTime->format('H:i')} to {$endTime->format('H:i')}",
                        'schedule_created',
                        [
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'schedule_id' => $schedule->id,
                            'day_of_week' => $schedule->day_of_week,
                            'start_time' => $startTime->format('H:i'),
                            'end_time' => $endTime->format('H:i'),
                            'url' => route('teacher.classes.show', $class->id)
                        ]
                    );
                }

                // ✅ ADDED: Notify enrolled students' parents
                $enrolledStudents = $class->students()
                    ->wherePivot('status', 'active')
                    ->with('parent')
                    ->get();

                $parentsNotified = 0;
                foreach ($enrolledStudents as $student) {
                    if ($student->parent) {
                        NotificationHelper::notifyUser(
                            $student->parent,
                            'New Class Schedule',
                            "{$class->name} now meets on {$schedule->day_of_week}s from {$startTime->format('H:i')} to {$endTime->format('H:i')}",
                            'schedule_created',
                            [
                                'class_id' => $class->id,
                                'class_name' => $class->name,
                                'student_id' => $student->id,
                                'student_name' => $student->full_name,
                                'day_of_week' => $schedule->day_of_week,
                                'start_time' => $startTime->format('H:i'),
                                'end_time' => $endTime->format('H:i'),
                                'url' => route('parent.students.show', $student->id)
                            ]
                        );
                        $parentsNotified++;
                    }
                }

                Log::info("Schedule notifications sent for {$class->name} - {$schedule->day_of_week}: Teacher + {$parentsNotified} parents");

            } catch (\Exception $e) {
                Log::error('Failed to send schedule creation notifications: ' . $e->getMessage());
                // Don't fail the request if notifications fail - schedule is already created
            }

            // ✅ FIXED: Use 'success' key for Toastr success message
            return redirect()->route('admin.schedules.index')
                ->with('success', 'Schedule created successfully! Teacher and parents have been notified.');

        } catch (\Exception $e) {
            // ✅ FIXED: Rollback only happens if schedule creation fails
            DB::rollBack();
            
            Log::error('Error creating schedule: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);
            
            // ✅ FIXED: Use redirect()->back() with 'error' key for Toastr
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create schedule. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified schedule
     */
    public function show(Schedule $schedule)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('view schedules')) {
            abort(403, 'You do not have permission to view schedule details.');
        }

        try {
            $schedule->load(['class.teacher', 'class.enrollments.student.parent']);

            // Get other schedules for this class
            $classSchedules = Schedule::where('class_id', $schedule->class_id)
                ->where('id', '!=', $schedule->id)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            // ✅ ENHANCED: Comprehensive attendance statistics
            $attendanceStats = [
                'total_sessions' => $schedule->attendance()->distinct('date')->count(),
                'average_attendance' => $this->calculateAverageAttendance($schedule),
                'last_session' => $schedule->attendance()->max('date'),
                'present_count' => $schedule->attendance()->where('status', 'present')->count(),
                'absent_count' => $schedule->attendance()->where('status', 'absent')->count(),
                'late_count' => $schedule->attendance()->where('status', 'late')->count(),
            ];

            // ✅ ADDED: Calculate schedule duration
            $duration = Carbon::parse($schedule->start_time)
                ->diffInMinutes(Carbon::parse($schedule->end_time));

            return view('admin.schedules.show', compact(
                'schedule',
                'classSchedules',
                'attendanceStats',
                'duration'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading schedule details: ' . $e->getMessage(), [
                'schedule_id' => $schedule->id
            ]);
            
            return back()->with('error', 'An error occurred while loading schedule details.');
        }
    }

    /**
     * Show the form for editing the specified schedule
     */
    public function edit(Schedule $schedule)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('edit schedules')) {
            abort(403, 'You do not have permission to edit schedules.');
        }

        try {
            // ✅ ENHANCED: Only show classes with active teachers
            $classes = ClassModel::with('teacher')
                ->whereHas('teacher', function($q) {
                    $q->where('status', 'active');
                })
                ->orderBy('name')
                ->get();

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

            // ✅ ADDED: Check if schedule has attendance records
            $hasAttendance = $schedule->attendance()->exists();
            $attendanceCount = $schedule->attendance()->count();

            // ✅ ADDED: Get other schedules for conflict preview
            $otherSchedules = Schedule::with('class')
                ->where('id', '!=', $schedule->id)
                ->where('day_of_week', $schedule->day_of_week)
                ->orderBy('start_time')
                ->get();

            return view('admin.schedules.edit', compact(
                'schedule',
                'classes',
                'days',
                'hasAttendance',
                'attendanceCount',
                'otherSchedules'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading schedule edit form: ' . $e->getMessage());
            
            return back()->with('error', 'An error occurred while loading the edit form.');
        }
    }

    /**
     * Update the specified schedule
     */
    public function update(Request $request, Schedule $schedule)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('edit schedules')) {
            abort(403, 'You do not have permission to edit schedules.');
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'recurring' => 'boolean',
        ], [
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'day_of_week.required' => 'Please select a day of the week.',
            'day_of_week.in' => 'Invalid day selected. Please choose a valid day.',
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in HH:MM format (e.g., 09:00).',
            'end_time.required' => 'End time is required.',
            'end_time.date_format' => 'End time must be in HH:MM format (e.g., 14:00).',
            'end_time.after' => 'End time must be after start time.',
        ]);

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();

        try {
            // Store old values for change tracking
            $oldClass = $schedule->class;
            $oldDayOfWeek = $schedule->day_of_week;
            $oldStartTime = Carbon::parse($schedule->start_time)->format('H:i');
            $oldEndTime = Carbon::parse($schedule->end_time)->format('H:i');
            $oldTeacher = $oldClass->teacher;

            // ✅ ENHANCED: Verify new class exists and has active teacher
            $newClass = ClassModel::with('teacher')->findOrFail($validated['class_id']);
            
            if (!$newClass->teacher) {
                return back()
                    ->withInput()
                    ->with('error', 'Cannot update schedule: Class has no assigned teacher.');
            }

            if ($newClass->teacher->status !== 'active') {
                return back()
                    ->withInput()
                    ->with('error', 'Cannot update schedule: Teacher is not active.');
            }

            // ✅ ENHANCED: Time validation - minimum 30 minutes
            $startTime = Carbon::createFromFormat('H:i', $validated['start_time']);
            $endTime = Carbon::createFromFormat('H:i', $validated['end_time']);
            $durationMinutes = $startTime->diffInMinutes($endTime);

            if ($durationMinutes < 30) {
                return back()
                    ->withInput()
                    ->with('error', 'Schedule duration must be at least 30 minutes.');
            }

            // Check for conflicts (excluding current schedule)
            $conflict = $this->checkScheduleConflict(
                $validated['class_id'],
                $validated['day_of_week'],
                $validated['start_time'],
                $validated['end_time'],
                $schedule->id
            );

            if ($conflict) {
                return back()
                    ->withInput()
                    ->with('error', 'Schedule conflict detected: ' . $conflict);
            }

            // ✅ ENHANCED: Track what changed
            $changes = [];
            if ($validated['class_id'] != $schedule->class_id) {
                $changes[] = "class changed from {$oldClass->name} to {$newClass->name}";
            }
            if ($validated['day_of_week'] != $oldDayOfWeek) {
                $changes[] = "day changed from {$oldDayOfWeek} to {$validated['day_of_week']}";
            }
            if ($validated['start_time'] != $oldStartTime) {
                $changes[] = "start time changed from {$oldStartTime} to {$validated['start_time']}";
            }
            if ($validated['end_time'] != $oldEndTime) {
                $changes[] = "end time changed from {$oldEndTime} to {$validated['end_time']}";
            }

            // Update schedule with proper time format
            $schedule->update([
                'class_id' => $validated['class_id'],
                'day_of_week' => $validated['day_of_week'],
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'recurring' => $request->has('recurring') ? true : false,
            ]);

            // ✅ ENHANCED: Activity log with detailed changes
            $changeDescription = !empty($changes) ? ' (' . implode(', ', $changes) . ')' : '';
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated_schedule',
                'model_type' => 'Schedule',
                'model_id' => $schedule->id,
                'description' => "Updated schedule for class: {$newClass->name}{$changeDescription}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // ✅ ADDED: Send notifications if significant changes
            try {
                $notifyTeachers = [];
                $notifyParents = false;

                // Notify if class changed
                if ($validated['class_id'] != $schedule->class_id) {
                    // Notify old teacher
                    if ($oldTeacher) {
                        $notifyTeachers[] = $oldTeacher;
                    }
                    // Notify new teacher
                    if ($newClass->teacher && $newClass->teacher->id !== $oldTeacher?->id) {
                        $notifyTeachers[] = $newClass->teacher;
                    }
                    $notifyParents = true;
                }

                // Notify if day or time changed significantly (more than 30 minutes)
                if ($validated['day_of_week'] != $oldDayOfWeek || 
                    abs($startTime->diffInMinutes(Carbon::createFromFormat('H:i', $oldStartTime))) >= 30) {
                    if ($newClass->teacher && !in_array($newClass->teacher, $notifyTeachers)) {
                        $notifyTeachers[] = $newClass->teacher;
                    }
                    $notifyParents = true;
                }

                // Send teacher notifications
                foreach ($notifyTeachers as $teacher) {
                    NotificationHelper::notifyUser(
                        $teacher,
                        'Schedule Updated',
                        "Schedule for {$newClass->name} has been updated. New schedule: {$validated['day_of_week']} from {$validated['start_time']} to {$validated['end_time']}",
                        'schedule_updated',
                        [
                            'class_id' => $newClass->id,
                            'class_name' => $newClass->name,
                            'schedule_id' => $schedule->id,
                            'changes' => $changes,
                            'url' => route('teacher.schedules.index')
                        ]
                    );
                }

                // Send parent notifications if major changes
                if ($notifyParents) {
                    $enrolledStudents = $newClass->students()
                        ->wherePivot('status', 'active')
                        ->with('parent')
                        ->get();

                    foreach ($enrolledStudents as $student) {
                        if ($student->parent) {
                            NotificationHelper::notifyUser(
                                $student->parent,
                                'Class Schedule Updated',
                                "{$newClass->name} schedule has been updated. New schedule: {$validated['day_of_week']}s from {$validated['start_time']} to {$validated['end_time']}",
                                'schedule_updated',
                                [
                                    'class_id' => $newClass->id,
                                    'class_name' => $newClass->name,
                                    'student_id' => $student->id,
                                    'student_name' => $student->full_name,
                                    'day_of_week' => $validated['day_of_week'],
                                    'start_time' => $validated['start_time'],
                                    'end_time' => $validated['end_time'],
                                    'url' => route('parent.students.show', $student->id)
                                ]
                            );
                        }
                    }
                }

                if (!empty($changes)) {
                    Log::info("Schedule update notifications sent for {$newClass->name}");
                }

            } catch (\Exception $e) {
                Log::error('Failed to send schedule update notifications: ' . $e->getMessage());
            }

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Schedule updated successfully!' . 
                    (!empty($changes) ? ' Notifications sent to affected parties.' : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating schedule: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'schedule_id' => $schedule->id
            ]);
            
            return back()
                ->withErrors(['error' => 'Failed to update schedule. Please try again.'])
                ->withInput();
        }
    }

/**
     * Remove the specified schedule
     */
    public function destroy(Schedule $schedule)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('delete schedules')) {
            abort(403, 'You do not have permission to delete schedules.');
        }

        // ✅ FIXED: Check attendance BEFORE starting transaction
        $attendanceCount = $schedule->attendance()->count();
        
        if ($attendanceCount > 0) {
            // ✅ FIXED: Use redirect()->back() instead of back()
            return redirect()->back()
                ->with('error', "Cannot delete schedule with {$attendanceCount} attendance record(s). Historical data must be preserved. Please contact SuperAdmin if deletion is absolutely necessary.");
        }

        // ✅ NOW start transaction only if we're actually deleting
        DB::beginTransaction();

        try {
            $className = $schedule->class->name;
            $dayOfWeek = $schedule->day_of_week;
            $scheduleId = $schedule->id;
            $teacher = $schedule->class->teacher;

            // Get enrolled students before deletion for notifications
            $enrolledStudents = $schedule->class->students()
                ->wherePivot('status', 'active')
                ->with('parent')
                ->get();

            $schedule->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'deleted_schedule',
                'model_type' => 'Schedule',
                'model_id' => $scheduleId,
                'description' => "Deleted schedule for class: {$className} on {$dayOfWeek}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            // ✅ ADDED: Send notifications AFTER commit
            try {
                // Notify teacher
                if ($teacher) {
                    NotificationHelper::notifyUser(
                        $teacher,
                        'Schedule Deleted',
                        "The {$dayOfWeek} schedule for {$className} has been deleted",
                        'schedule_deleted',
                        [
                            'class_name' => $className,
                            'day_of_week' => $dayOfWeek
                        ]
                    );
                }

                // Notify enrolled students' parents
                foreach ($enrolledStudents as $student) {
                    if ($student->parent) {
                        NotificationHelper::notifyUser(
                            $student->parent,
                            'Class Schedule Removed',
                            "The {$dayOfWeek} schedule for {$className} has been removed",
                            'schedule_deleted',
                            [
                                'class_name' => $className,
                                'student_name' => $student->full_name,
                                'day_of_week' => $dayOfWeek,
                                'url' => route('parent.students.show', $student->id)
                            ]
                        );
                    }
                }

                Log::info("Schedule deletion notifications sent for {$className} - {$dayOfWeek}");

            } catch (\Exception $e) {
                Log::error('Failed to send schedule deletion notifications: ' . $e->getMessage());
            }

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Schedule deleted successfully! Notifications sent to affected parties.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting schedule: ' . $e->getMessage(), [
                'schedule_id' => $schedule->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to delete schedule. Please try again.');
        }
    }

    /**
     * Check for schedule conflicts
     * 
     * Detects if a new/updated schedule overlaps with existing schedules
     * for the same class on the same day
     */
    protected function checkScheduleConflict($classId, $dayOfWeek, $startTime, $endTime, $excludeId = null)
    {
        try {
            // Normalize time format
            $startTime = Carbon::createFromFormat('H:i', $startTime)->format('H:i:s');
            $endTime = Carbon::createFromFormat('H:i', $endTime)->format('H:i:s');

            $query = Schedule::where('class_id', $classId)
                ->where('day_of_week', $dayOfWeek)
                ->where(function($q) use ($startTime, $endTime) {
                    // Check for any time overlap
                    $q->where(function($q2) use ($startTime, $endTime) {
                        // New schedule starts during existing schedule
                        $q2->where('start_time', '<=', $startTime)
                           ->where('end_time', '>', $startTime);
                    })
                    ->orWhere(function($q2) use ($startTime, $endTime) {
                        // New schedule ends during existing schedule
                        $q2->where('start_time', '<', $endTime)
                           ->where('end_time', '>=', $endTime);
                    })
                    ->orWhere(function($q2) use ($startTime, $endTime) {
                        // New schedule completely contains existing schedule
                        $q2->where('start_time', '>=', $startTime)
                           ->where('end_time', '<=', $endTime);
                    });
                });

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            $conflict = $query->first();

            if ($conflict) {
                $conflictStart = Carbon::parse($conflict->start_time)->format('H:i');
                $conflictEnd = Carbon::parse($conflict->end_time)->format('H:i');
                return "Class already scheduled on {$dayOfWeek} from {$conflictStart} to {$conflictEnd}";
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error checking schedule conflict: ' . $e->getMessage());
            return 'Unable to verify schedule conflicts. Please try again.';
        }
    }

    /**
     * Detect all conflicts in the system
     */
    protected function detectConflicts()
    {
        try {
            $conflicts = collect();
            $schedules = Schedule::with('class')->get();

            foreach ($schedules as $schedule) {
                $conflict = $this->checkScheduleConflict(
                    $schedule->class_id,
                    $schedule->day_of_week,
                    Carbon::parse($schedule->start_time)->format('H:i'),
                    Carbon::parse($schedule->end_time)->format('H:i'),
                    $schedule->id
                );

                if ($conflict) {
                    $conflicts->push($schedule);
                }
            }

            return $conflicts;

        } catch (\Exception $e) {
            Log::error('Error detecting conflicts: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Calculate average attendance for a schedule
     */
    protected function calculateAverageAttendance($schedule)
    {
        try {
            $totalRecords = $schedule->attendance()->count();
            
            if ($totalRecords === 0) {
                return 0;
            }

            $presentCount = $schedule->attendance()->where('status', 'present')->count();
            
            return round(($presentCount / $totalRecords) * 100, 1);

        } catch (\Exception $e) {
            Log::error('Error calculating average attendance: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get current week days
     */
    protected function getWeekDays()
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }
}
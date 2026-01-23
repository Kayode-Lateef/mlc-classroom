<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['class', 'class.teacher']);

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

        // View type (list or calendar)
        $viewType = $request->get('view', 'calendar');

        if ($viewType === 'list') {
            // List view - paginated
            $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->paginate(50);
            $schedulesByDay = null;
        } else {
            // Calendar view - group by day
            $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->get();
            $schedulesByDay = $schedules->groupBy('day_of_week');
        }

        // Get filter options
        $classes = ClassModel::with('teacher')->orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();

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

        return view('superadmin.schedules.index', compact(
            'schedules',
            'schedulesByDay',
            'classes',
            'teachers',
            'stats',
            'viewType'
        ));
    }

    /**
     * Show the form for creating a new schedule
     */
    public function create()
    {
        $classes = ClassModel::with('teacher')->orderBy('name')->get();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return view('superadmin.schedules.create', compact('classes', 'days'));
    }

    /**
     * Store a newly created schedule
     */
    public function store(Request $request)
    {
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
            return redirect()->route('superadmin.schedules.index')
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
        $schedule->load(['class.teacher', 'class.enrollments.student']);

        // Get other schedules for this class
        $classSchedules = Schedule::where('class_id', $schedule->class_id)
            ->where('id', '!=', $schedule->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Get attendance statistics for this schedule
        $attendanceStats = [
            'total_sessions' => $schedule->attendance()->distinct('date')->count(),
            'average_attendance' => $this->calculateAverageAttendance($schedule),
        ];

        return view('superadmin.schedules.show', compact('schedule', 'classSchedules', 'attendanceStats'));
    }

    /**
     * Show the form for editing the specified schedule
     */
    public function edit(Schedule $schedule)
    {
        $classes = ClassModel::with('teacher')->orderBy('name')->get();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return view('superadmin.schedules.edit', compact('schedule', 'classes', 'days'));
    }

    /**
     * Update the specified schedule
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'recurring' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check for conflicts (excluding current schedule)
        $conflict = $this->checkScheduleConflict(
            $request->class_id,
            $request->day_of_week,
            $request->start_time,
            $request->end_time,
            $schedule->id
        );

        if ($conflict) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Schedule conflict detected: ' . $conflict);
        }

        // FIX: Store old values as formatted strings (not Carbon objects)
        $oldTime = $schedule->start_time->format('H:i');
        $oldDay = $schedule->day_of_week;

        // Update schedule with proper time format
        $schedule->update([
            'class_id' => $request->class_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => Carbon::createFromFormat('H:i', $request->start_time)->format('H:i:s'),
            'end_time' => Carbon::createFromFormat('H:i', $request->end_time)->format('H:i:s'),
            'recurring' => $request->has('recurring') ? true : false,
        ]);

        // FIX: Compare formatted strings and format in messages
        if ($oldTime !== $schedule->start_time->format('H:i') || $oldDay !== $schedule->day_of_week) {
            // NOTIFY ALL PARENTS IN CLASS
            NotificationHelper::notifyClassParents(
                $schedule->class,
                'Schedule Change',
                "Schedule updated for {$schedule->class->name}: {$schedule->day_of_week} at " . $schedule->start_time->format('H:i'),
                'schedule_change',
                [
                    'schedule_id' => $schedule->id,
                    'old_time' => $oldTime,
                    'new_time' => $schedule->start_time->format('H:i'),
                    'old_day' => $oldDay,
                    'new_day' => $schedule->day_of_week,
                    'class_name' => $schedule->class->name,
                    'url' => null  // Parents don't have schedule detail page
                ]
            );
            
            // NOTIFY TEACHER IF EXISTS
            if ($schedule->class->teacher) {
                NotificationHelper::notifyTeacher(
                    $schedule->class->teacher,
                    'Your Class Schedule Changed',
                    "Schedule for {$schedule->class->name} changed to {$schedule->day_of_week} at " . $schedule->start_time->format('H:i'),
                    'schedule_change',
                    [
                        'schedule_id' => $schedule->id,
                        'class_name' => $schedule->class->name,
                        'url' => route('teacher.classes.show', $schedule->class_id)
                    ]
                );
            }
        }

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated_schedule',
            'model_type' => 'Schedule',
            'model_id' => $schedule->id,
            'description' => "Updated schedule for class: {$schedule->class->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.schedules.index')
            ->with('success', 'Schedule updated and notifications sent!');
    }


    /**
     * Remove the specified schedule
     */
    public function destroy(Schedule $schedule)
    {
        // ✅ ENHANCED: Check attendance BEFORE starting transaction
        $attendanceCount = $schedule->attendance()->count();
        
        if ($attendanceCount > 0) {
            return redirect()->back()
                ->with('error', "Cannot delete schedule with {$attendanceCount} attendance record(s). Historical data must be preserved.");
        }

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();

        try {
            $className = $schedule->class->name;
            $dayOfWeek = $schedule->day_of_week;
            $scheduleId = $schedule->id;
            $teacher = $schedule->class->teacher;

            // ✅ ADDED: Get enrolled students before deletion for notifications
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

            // ✅ ADDED: Send notifications AFTER successful commit
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
                // Don't fail the deletion if notifications fail
            }

            return redirect()->route('superadmin.schedules.index')
                ->with('success', 'Schedule deleted successfully! Notifications sent to affected parties.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting schedule: ' . $e->getMessage(), [
                'schedule_id' => $schedule->id,
                'trace' => $e->getTraceAsString()
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
    }

    /**
     * Detect all conflicts in the system
     */
    protected function detectConflicts()
    {
        $conflicts = collect();
        $schedules = Schedule::with('class')->get();

        foreach ($schedules as $schedule) {
            $conflict = $this->checkScheduleConflict(
                $schedule->class_id,
                $schedule->day_of_week,
                $schedule->start_time->format('H:i'),
                $schedule->end_time->format('H:i'),
                $schedule->id
            );

            if ($conflict) {
                $conflicts->push($schedule);
            }
        }

        return $conflicts;
    }

    /**
     * Calculate average attendance for a schedule
     */
    protected function calculateAverageAttendance($schedule)
    {
        $totalSessions = $schedule->attendance()->distinct('date')->count();
        
        if ($totalSessions === 0) {
            return 0;
        }

        $presentCount = $schedule->attendance()->where('status', 'present')->count();
        
        return round(($presentCount / $totalSessions) * 100, 2);
    }

    /**
     * Get current week days
     */
    protected function getWeekDays()
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }

    /**
     * Bulk delete schedules (SuperAdmin only)
     */
    public function bulkDestroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_ids' => 'required|array',
            'schedule_ids.*' => 'exists:schedules,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Invalid schedule selection.');
        }

        $schedules = Schedule::whereIn('id', $request->schedule_ids)->get();
        $deletedCount = 0;
        $errors = [];

        foreach ($schedules as $schedule) {
            // Check if schedule has attendance records
            if ($schedule->attendance()->count() > 0) {
                $errors[] = "Cannot delete {$schedule->class->name} ({$schedule->day_of_week}) - has attendance records";
                continue;
            }

            $schedule->delete();
            $deletedCount++;
        }

        // Log bulk deletion
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'bulk_deleted_schedules',
            'model_type' => 'Schedule',
            'model_id' => null,
            'description' => "Bulk deleted {$deletedCount} schedules",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if (count($errors) > 0) {
            return redirect()->back()
                ->with('warning', "Deleted {$deletedCount} schedules. Errors: " . implode(', ', $errors));
        }

        return redirect()->route('superadmin.schedules.index')
            ->with('success', "Successfully deleted {$deletedCount} schedules!");
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\ActivityLog;
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

        // Statistics
        $stats = [
            'total_schedules' => Schedule::count(),
            'unique_classes' => Schedule::distinct('class_id')->count('class_id'),
            'this_week' => Schedule::whereIn('day_of_week', $this->getWeekDays())->count(),
            'conflicts' => $this->detectConflicts()->count(),
        ];

        return view('admin.schedules.index', compact(
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

        return view('admin.schedules.create', compact('classes', 'days'));
    }

    /**
     * Store a newly created schedule
     */
    public function store(Request $request)
    {
        // Custom validation messages
        $messages = [
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'day_of_week.required' => 'Please select a day of the week.',
            'day_of_week.in' => 'Invalid day selected. Please choose a valid day.',
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in HH:MM format (e.g., 09:00).',
            'end_time.required' => 'End time is required.',
            'end_time.date_format' => 'End time must be in HH:MM format (e.g., 14:00).',
            'end_time.after' => 'End time must be after start time.',
        ];

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'recurring' => 'boolean',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check for conflicts
        $conflict = $this->checkScheduleConflict(
            $request->class_id,
            $request->day_of_week,
            $request->start_time,
            $request->end_time
        );

        if ($conflict) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Schedule conflict detected: ' . $conflict);
        }

        // Create schedule with proper time format
        $schedule = Schedule::create([
            'class_id' => $request->class_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => Carbon::createFromFormat('H:i', $request->start_time)->format('H:i:s'),
            'end_time' => Carbon::createFromFormat('H:i', $request->end_time)->format('H:i:s'),
            'recurring' => $request->has('recurring') ? true : false,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'created_schedule',
            'model_type' => 'Schedule',
            'model_id' => $schedule->id,
            'description' => "Created schedule for class: {$schedule->class->name} on {$schedule->day_of_week}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule created successfully!');
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

        return view('admin.schedules.show', compact('schedule', 'classSchedules', 'attendanceStats'));
    }

    /**
     * Show the form for editing the specified schedule
     */
    public function edit(Schedule $schedule)
    {
        $classes = ClassModel::with('teacher')->orderBy('name')->get();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return view('admin.schedules.edit', compact('schedule', 'classes', 'days'));
    }

    /**
     * Update the specified schedule
     */
    public function update(Request $request, Schedule $schedule)
    {
        // Custom validation messages
        $messages = [
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'day_of_week.required' => 'Please select a day of the week.',
            'day_of_week.in' => 'Invalid day selected. Please choose a valid day.',
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in HH:MM format (e.g., 09:00).',
            'end_time.required' => 'End time is required.',
            'end_time.date_format' => 'End time must be in HH:MM format (e.g., 14:00).',
            'end_time.after' => 'End time must be after start time.',
        ];

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'recurring' => 'boolean',
        ], $messages);

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

        // Update schedule with proper time format
        $schedule->update([
            'class_id' => $request->class_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => Carbon::createFromFormat('H:i', $request->start_time)->format('H:i:s'),
            'end_time' => Carbon::createFromFormat('H:i', $request->end_time)->format('H:i:s'),
            'recurring' => $request->has('recurring') ? true : false,
        ]);

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

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule updated successfully!');
    }

    /**
     * Remove the specified schedule
     */
    public function destroy(Schedule $schedule)
    {
        // Check if schedule has attendance records
        $attendanceCount = $schedule->attendance()->count();
        
        if ($attendanceCount > 0) {
            return redirect()->back()
                ->with('error', "Cannot delete schedule with {$attendanceCount} attendance records. Please contact SuperAdmin if deletion is necessary.");
        }

        $className = $schedule->class->name;
        $dayOfWeek = $schedule->day_of_week;
        $scheduleId = $schedule->id;

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

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule deleted successfully!');
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
}
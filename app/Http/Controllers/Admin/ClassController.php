<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\Student;
use App\Models\ActivityLog;
use App\Models\ClassEnrollment;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClassController extends Controller
{
    /**
     * Display a listing of classes
     */
    public function index(Request $request)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('view classes')) {
            abort(403, 'You do not have permission to view classes.');
        }

        try {
            $query = ClassModel::with(['teacher', 'schedules']);

            // Filter by subject
            if ($request->filled('subject')) {
                $query->where('subject', $request->subject);
            }

            // Filter by level
            if ($request->filled('level')) {
                $query->where('level', $request->level);
            }

            // Filter by teacher
            if ($request->filled('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
            }

            // Search by name or room
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('room_number', 'like', "%{$search}%")
                      ->orWhere('subject', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $classes = $query->paginate(config('app.pagination.classes', 20));
            $teachers = User::where('role', 'teacher')
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
            $subjects = ClassModel::distinct()->pluck('subject');
            $levels = ClassModel::distinct()->whereNotNull('level')->pluck('level');

            // ✅ ENHANCED: Statistics
            $stats = [
                'total' => ClassModel::count(),
                'with_teacher' => ClassModel::whereNotNull('teacher_id')->count(),
                'without_teacher' => ClassModel::whereNull('teacher_id')->count(),
                'total_capacity' => ClassModel::sum('capacity'),
                'total_enrolled' => ClassEnrollment::where('status', 'active')->count(),
            ];

            return view('admin.classes.index', compact('classes', 'teachers', 'subjects', 'levels', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Error loading classes list: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An error occurred while loading classes. Please try again.');
        }
    }

    /**
     * Show the form for creating a new class
     */
    public function create()
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('create classes')) {
            abort(403, 'You do not have permission to create classes.');
        }

        $teachers = User::where('role', 'teacher')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
            
        return view('admin.classes.create', compact('teachers'));
    }

    /**
     * Store a newly created class
     */
    public function store(Request $request)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('create classes')) {
            abort(403, 'You do not have permission to create classes.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:100',
            'level' => 'nullable|string|max:100',
            'room_number' => 'nullable|string|max:50',
            'teacher_id' => 'nullable|exists:users,id',
            'capacity' => 'required|integer|min:1|max:' . (int) \App\Models\SystemSetting::get('max_class_capacity', 100),
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Class name is required.',
            'name.max' => 'Class name must not exceed 255 characters.',
            'subject.required' => 'Subject is required.',
            'subject.max' => 'Subject must not exceed 100 characters.',
            'level.max' => 'Level must not exceed 100 characters.',
            'room_number.max' => 'Room number must not exceed 50 characters.',
            'teacher_id.exists' => 'Selected teacher does not exist.',
            'capacity.required' => 'Class capacity is required.',
            'capacity.integer' => 'Capacity must be a number.',
            'capacity.min' => 'Capacity must be at least 1 student.',
            'capacity.max' => 'Capacity cannot exceed ' . (int) \App\Models\SystemSetting::get('max_class_capacity', 100) . ' students.',
        ]);

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();
        
        try {
            // Verify teacher role if provided
            if (isset($validated['teacher_id'])) {
                $teacher = User::find($validated['teacher_id']);
                if (!$teacher || !$teacher->isTeacher()) {
                    return back()
                        ->withErrors(['teacher_id' => 'Selected user must be a teacher.'])
                        ->withInput();
                }
                
                // ✅ ADDED: Verify teacher is active
                if ($teacher->status !== 'active') {
                    return back()
                        ->withErrors(['teacher_id' => 'Selected teacher account is not active.'])
                        ->withInput();
                }
            }

            $class = ClassModel::create($validated);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created_class',
                'model_type' => 'ClassModel',
                'model_id' => $class->id,
                'description' => "Created class: {$class->name} ({$class->subject})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // ✅ ADDED: Notifications
            try {
                // Notify teacher if assigned
                if ($class->teacher) {
                    NotificationHelper::notifyUser(
                        $class->teacher,
                        'New Class Assigned',
                        "You have been assigned to teach: {$class->name} ({$class->subject})",
                        'class_assignment',
                        [
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'subject' => $class->subject,
                            'capacity' => $class->capacity,
                            'url' => route('teacher.classes.show', $class->id)
                        ]
                    );
                }

                // Notify other admins
                NotificationHelper::notifyAdmins(
                    'New Class Created',
                    "{$class->name} ({$class->subject}) has been created" . 
                    ($class->teacher ? " and assigned to {$class->teacher->name}" : ""),
                    'class_created',
                    [
                        'class_id' => $class->id,
                        'class_name' => $class->name,
                        'subject' => $class->subject,
                        'teacher_id' => $class->teacher_id,
                        'url' => route('admin.classes.show', $class->id)
                    ],
                    auth()->id() // Exclude current admin
                );
                
            } catch (\Exception $e) {
                Log::error('Failed to send class creation notifications: ' . $e->getMessage());
            }

            return redirect()->route('admin.classes.index')
                ->with('success', 'Class created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating class: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);
            
            return back()
                ->withErrors(['error' => 'Failed to create class. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Display the specified class
     */
    public function show(ClassModel $class)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('view classes')) {
            abort(403, 'You do not have permission to view classes.');
        }

        try {
            $class->load([
                'teacher',
                'schedules',
                'students' => function($query) {
                // Use the students relationship (many-to-many) and filter by active status
                $query->wherePivot('status', 'active')
                    ->orderBy('first_name', 'asc');
                },
                'attendance' => function($query) {
                    $query->orderBy('date', 'desc')
                        ->limit(config('app.attendance_history_limit', 30));
                },
                'homeworkAssignments' => function($query) {
                    $query->orderBy('due_date', 'desc')->limit(10);
                }
            ]);

            // Calculate statistics
            $totalEnrolled = $class->enrollments()->where('status', 'active')->count();
            $availableSeats = $class->capacity - $totalEnrolled;
            $utilizationRate = $class->capacity > 0 ? round(($totalEnrolled / $class->capacity) * 100, 2) : 0;

            // Attendance statistics
            $totalAttendance = $class->attendance()->count();
            $presentCount = $class->attendance()->where('status', 'present')->count();
            $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 2) : 0;

            // Homework statistics
            $totalHomework = $class->homeworkAssignments()->count();
            $pendingHomework = $class->homeworkAssignments()->where('due_date', '>', now())->count();

            $stats = [
                'enrolled' => $totalEnrolled,
                'capacity' => $class->capacity,
                'available_seats' => $availableSeats,
                'utilization_rate' => $utilizationRate,
                'attendance_rate' => $attendanceRate,
                'total_homework' => $totalHomework,
                'pending_homework' => $pendingHomework,
            ];

            // Get unenrolled active students for enrollment
            $enrolledStudentIds = $class->enrollments()->where('status', 'active')->pluck('student_id');
            $availableStudents = Student::where('status', 'active')
                ->whereNotIn('id', $enrolledStudentIds)
                ->with('parent')
                ->orderBy('first_name')
                ->get();

            // ✅ NEW: Get complete enrollment history (all statuses)
            $enrollmentHistory = $class->enrollments()
                ->with('student.parent')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('student_id')
                ->map(function($studentEnrollments) {
                    return [
                        'student' => $studentEnrollments->first()->student,
                        'enrollments' => $studentEnrollments,
                        'total_enrollments' => $studentEnrollments->count(),
                        'current_status' => $studentEnrollments->first()->status,
                        'first_enrollment' => $studentEnrollments->last()->enrollment_date,
                        'last_enrollment' => $studentEnrollments->first()->enrollment_date,
                    ];
                });


            return view('admin.classes.show', compact('class', 'availableStudents', 'stats', 'enrollmentHistory'));
            
        } catch (\Exception $e) {
            Log::error('Error loading class details: ' . $e->getMessage(), [
                'class_id' => $class->id
            ]);
            
            return back()->with('error', 'An error occurred while loading class details.');
        }
    }

    /**
     * Show the form for editing the specified class
     */
    public function edit(ClassModel $class)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('edit classes')) {
            abort(403, 'You do not have permission to edit classes.');
        }

        $teachers = User::where('role', 'teacher')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
            
        return view('admin.classes.edit', compact('class', 'teachers'));
    }

    /**
     * Update the specified class
     */
    public function update(Request $request, ClassModel $class)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('edit classes')) {
            abort(403, 'You do not have permission to edit classes.');
        }

        // ✅ ADDED: Store old values for comparison
        $oldTeacherId = $class->teacher_id;
        $oldCapacity = $class->capacity;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:100',
            'level' => 'nullable|string|max:100',
            'room_number' => 'nullable|string|max:50',
            'teacher_id' => 'nullable|exists:users,id',
            'capacity' => 'required|integer|min:1|max:' . (int) \App\Models\SystemSetting::get('max_class_capacity', 100),
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Class name is required.',
            'name.max' => 'Class name must not exceed 255 characters.',
            'subject.required' => 'Subject is required.',
            'subject.max' => 'Subject must not exceed 100 characters.',
            'level.max' => 'Level must not exceed 100 characters.',
            'room_number.max' => 'Room number must not exceed 50 characters.',
            'teacher_id.exists' => 'Selected teacher does not exist.',
            'capacity.required' => 'Class capacity is required.',
            'capacity.integer' => 'Capacity must be a number.',
            'capacity.min' => 'Capacity must be at least 1 student.',
            'capacity.max' => 'Capacity cannot exceed ' . (int) \App\Models\SystemSetting::get('max_class_capacity', 100) . ' students.',
        ]);

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();
        
        try {
            // Verify teacher role if provided
            if (isset($validated['teacher_id'])) {
                $teacher = User::find($validated['teacher_id']);
                if (!$teacher || !$teacher->isTeacher()) {
                    return back()
                        ->withErrors(['teacher_id' => 'Selected user must be a teacher.'])
                        ->withInput();
                }
                
                // ✅ ADDED: Verify teacher is active
                if ($teacher->status !== 'active') {
                    return back()
                        ->withErrors(['teacher_id' => 'Selected teacher account is not active.'])
                        ->withInput();
                }
            }

            // Check if reducing capacity below current enrollment
            $currentEnrollment = $class->enrollments()->where('status', 'active')->count();
            if ($validated['capacity'] < $currentEnrollment) {
                return back()->withErrors([
                    'capacity' => "Cannot reduce capacity below current enrollment ({$currentEnrollment} students)."
                ])->withInput();
            }

            $class->update($validated);

            // ✅ ENHANCED: Activity log with change tracking
            $changes = [];
            if ($oldTeacherId != $validated['teacher_id']) {
                $oldTeacher = $oldTeacherId ? User::find($oldTeacherId)->name : 'None';
                $newTeacher = $validated['teacher_id'] ? User::find($validated['teacher_id'])->name : 'None';
                $changes[] = "teacher changed from {$oldTeacher} to {$newTeacher}";
            }
            if ($oldCapacity != $validated['capacity']) {
                $changes[] = "capacity changed from {$oldCapacity} to {$validated['capacity']}";
            }
            
            $changeDescription = count($changes) > 0 ? ' (' . implode(', ', $changes) . ')' : '';

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated_class',
                'model_type' => 'ClassModel',
                'model_id' => $class->id,
                'description' => "Updated class: {$class->name} ({$class->subject}){$changeDescription}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // ✅ ADDED: Send notifications after successful update
            try {
                // Notify old teacher if teacher was changed and removed
                if ($oldTeacherId && $oldTeacherId != $validated['teacher_id']) {
                    $oldTeacher = User::find($oldTeacherId);
                    if ($oldTeacher) {
                        NotificationHelper::notifyUser(
                            $oldTeacher,
                            'Class Assignment Removed',
                            "You have been unassigned from class: {$class->name} ({$class->subject})",
                            'class_unassignment',
                            [
                                'class_id' => $class->id,
                                'class_name' => $class->name,
                                'subject' => $class->subject,
                            ]
                        );
                    }
                }

                // Notify new teacher if teacher was changed or assigned
                if ($validated['teacher_id'] && $oldTeacherId != $validated['teacher_id']) {
                    $newTeacher = User::find($validated['teacher_id']);
                    if ($newTeacher) {
                        NotificationHelper::notifyUser(
                            $newTeacher,
                            'New Class Assigned',
                            "You have been assigned to teach: {$class->name} ({$class->subject})",
                            'class_assignment',
                            [
                                'class_id' => $class->id,
                                'class_name' => $class->name,
                                'subject' => $class->subject,
                                'capacity' => $class->capacity,
                                'url' => route('teacher.classes.show', $class->id)
                            ]
                        );
                    }
                }

                // Notify enrolled students' parents if capacity was reduced significantly
                if ($oldCapacity > $validated['capacity'] && ($oldCapacity - $validated['capacity']) >= 5) {
                    $enrolledStudents = $class->enrollments()
                        ->where('status', 'active')
                        ->with('student.parent')
                        ->get();
                    
                    foreach ($enrolledStudents as $enrollment) {
                        if ($enrollment->student && $enrollment->student->parent) {
                            NotificationHelper::notifyUser(
                                $enrollment->student->parent,
                                'Class Capacity Updated',
                                "The capacity for {$class->name} has been reduced from {$oldCapacity} to {$validated['capacity']} students",
                                'class_updated',
                                [
                                    'class_id' => $class->id,
                                    'class_name' => $class->name,
                                    'student_id' => $enrollment->student_id,
                                ]
                            );
                        }
                    }
                }
                
            } catch (\Exception $e) {
                Log::error('Failed to send class update notifications: ' . $e->getMessage());
            }

            return redirect()->route('admin.classes.index')
                ->with('success', 'Class updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating class: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'class_id' => $class->id,
                'data' => $validated
            ]);
            
            return back()
                ->withErrors(['error' => 'Failed to update class. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified class
     */
    public function destroy(ClassModel $class)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('delete classes')) {
            abort(403, 'You do not have permission to delete classes.');
        }

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();
        
        try {
            // ✅ ENHANCED: Check for ALL relationships before deleting
            $hasActiveEnrollments = $class->enrollments()->where('status', 'active')->count() > 0;
            $hasSchedules = $class->schedules()->count() > 0;
            $hasAttendance = $class->attendance()->count() > 0;
            $hasHomework = $class->homeworkAssignments()->count() > 0;

            if ($hasActiveEnrollments || $hasSchedules || $hasAttendance || $hasHomework) {
                $relationships = [];
                if ($hasActiveEnrollments) {
                    $relationships[] = $class->enrollments()->where('status', 'active')->count() . ' active enrollment(s)';
                }
                if ($hasSchedules) $relationships[] = $class->schedules()->count() . ' schedule(s)';
                if ($hasAttendance) $relationships[] = $class->attendance()->count() . ' attendance record(s)';
                if ($hasHomework) $relationships[] = $class->homeworkAssignments()->count() . ' homework assignment(s)';

                return back()->with('error', 
                    'Cannot delete class with existing data: ' . implode(', ', $relationships) . 
                    '. Please remove these relationships first or archive the class instead.');
            }

            $className = $class->name;
            $classId = $class->id;
            $teacherId = $class->teacher_id;
            
            $class->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'deleted_class',
                'model_type' => 'ClassModel',
                'model_id' => $classId,
                'description' => "Deleted class: {$className}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            // ✅ ADDED: Notify teacher if class was assigned
            try {
                if ($teacherId) {
                    $teacher = User::find($teacherId);
                    if ($teacher) {
                        NotificationHelper::notifyUser(
                            $teacher,
                            'Class Deleted',
                            "The class '{$className}' has been deleted from the system",
                            'class_deleted',
                            []
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to send class deletion notification: ' . $e->getMessage());
            }

            return redirect()->route('admin.classes.index')
                ->with('success', 'Class deleted successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting class: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'class_id' => $class->id
            ]);
            
            return back()->with('error', 'Failed to delete class. Please try again.');
        }
    }

    /**
     * Enroll student in class
     */
    public function enrollStudent(Request $request, ClassModel $class)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        // if (!auth()->user()->can('manage enrollments')) {
        //     abort(403, 'You do not have permission to manage enrollments.');
        // }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'enrollment_date' => 'required|date|before_or_equal:today',
        ], [
            'student_id.required' => 'Please select a student to enroll.',
            'student_id.exists' => 'Selected student does not exist.',
            'enrollment_date.required' => 'Enrollment date is required.',
            'enrollment_date.date' => 'Please enter a valid enrollment date.',
            'enrollment_date.before_or_equal' => 'Enrollment date cannot be in the future.',
        ]);

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();
        
        try {
            // Verify student is active
            $student = Student::find($validated['student_id']);
            if (!$student || $student->status !== 'active') {
                return back()->with('error', 'Selected student is not active.');
            }

            // Check if class is full
            $currentEnrollment = $class->enrollments()->where('status', 'active')->count();
            if ($currentEnrollment >= $class->capacity) {
                return back()->with('error', 'Class is at full capacity!');
            }

            // Check if already enrolled
            $existingEnrollment = $class->enrollments()
                ->where('student_id', $validated['student_id'])
                ->where('status', 'active')
                ->first();
                
            if ($existingEnrollment) {
                return back()->with('error', 'Student is already enrolled in this class!');
            }

            $enrollment = ClassEnrollment::create([
                'student_id' => $validated['student_id'],
                'class_id' => $class->id,
                'enrollment_date' => $validated['enrollment_date'],
                'status' => 'active',
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'enrolled_student',
                'model_type' => 'ClassEnrollment',
                'model_id' => $enrollment->id,
                'description' => "Enrolled {$student->first_name} {$student->last_name} in class: {$class->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // ✅ ENHANCED: Comprehensive notifications after successful enrollment
            try {
                // Notify parent with email
                if ($student->parent) {
                    NotificationHelper::notifyUser(
                        $student->parent,
                        'Student Enrolled in Class',
                        "{$student->full_name} has been enrolled in {$class->name} ({$class->subject})",
                        'enrollment',
                        [
                            'student_id' => $student->id,
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'subject' => $class->subject,
                            'teacher' => $class->teacher ? $class->teacher->name : 'TBA',
                            'enrolled_by' => auth()->user()->name,
                            'enrolled_at' => now()->format('d M Y, H:i'),
                            'url' => route('parent.students.show', $student->id)
                        ],
                        true // ✅ Send email immediately
                    );
                }

                // Notify teacher with email
                if ($class->teacher) {
                    NotificationHelper::notifyUser(
                        $class->teacher,
                        'New Student Enrolled',
                        "{$student->full_name} has been enrolled in your class: {$class->name}",
                        'enrollment',
                        [
                            'student_id' => $student->id,
                            'student_name' => $student->full_name,
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'enrolled_by' => auth()->user()->name,
                            'enrolled_at' => now()->format('d M Y, H:i'),
                            'url' => route('teacher.classes.show', $class->id)
                        ],
                        true // ✅ Send email immediately
                    );
                }

                // ✅ ADDED: Notify SuperAdmins about the enrollment
                $superAdmins = User::where('role', 'superadmin')
                    ->where('status', 'active')
                    ->get();
                
                foreach ($superAdmins as $superAdmin) {
                    NotificationHelper::notifyUser(
                        $superAdmin,
                        'Student Enrolled in Class',
                        "{$student->full_name} has been enrolled in {$class->name} by " . auth()->user()->name,
                        'enrollment',
                        [
                            'student_id' => $student->id,
                            'student_name' => $student->full_name,
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'subject' => $class->subject,
                            'teacher' => $class->teacher ? $class->teacher->name : 'TBA',
                            'enrolled_by' => auth()->user()->name,
                            'enrolled_at' => now()->format('d M Y, H:i'),
                            'url' => route('superadmin.classes.show', $class->id)
                        ]
                        // ✅ No immediate email for SuperAdmins (will be batched)
                    );
                }

                // ✅ ADDED: Notify other Admins about the enrollment
                $otherAdmins = User::where('role', 'admin')
                    ->where('status', 'active')
                    ->where('id', '!=', auth()->id())
                    ->get();
                
                foreach ($otherAdmins as $admin) {
                    NotificationHelper::notifyUser(
                        $admin,
                        'Student Enrolled in Class',
                        "{$student->full_name} has been enrolled in {$class->name} by " . auth()->user()->name,
                        'enrollment',
                        [
                            'student_id' => $student->id,
                            'student_name' => $student->full_name,
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'subject' => $class->subject,
                            'teacher' => $class->teacher ? $class->teacher->name : 'TBA',
                            'enrolled_by' => auth()->user()->name,
                            'enrolled_at' => now()->format('d M Y, H:i'),
                            'url' => route('admin.classes.show', $class->id)
                        ]
                        // ✅ No immediate email for other Admins (will be batched)
                    );
                }

                // ✅ Check class capacity and notify all admins if 90%+ full
                $enrollmentCount = $class->enrollments()->where('status', 'active')->count();
                
                if ($enrollmentCount >= ($class->capacity * 0.9)) {
                    $message = $enrollmentCount >= $class->capacity 
                        ? "Class {$class->name} is now at full capacity ({$enrollmentCount}/{$class->capacity})"
                        : "Class {$class->name} is at {$enrollmentCount}/{$class->capacity} capacity (90%+ full)";
                    
                    // Notify SuperAdmins
                    NotificationHelper::notifySuperAdmins(
                        'Class Capacity Alert',
                        $message,
                        'capacity_alert',
                        [
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'current_enrollment' => $enrollmentCount,
                            'capacity' => $class->capacity,
                            'percentage' => round(($enrollmentCount / $class->capacity) * 100),
                            'enrolled_student' => $student->full_name,
                            'url' => route('superadmin.classes.show', $class->id)
                        ]
                    );

                    // Notify Admins
                    NotificationHelper::notifyAdmins(
                        'Class Capacity Alert',
                        $message,
                        'capacity_alert',
                        [
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'current_enrollment' => $enrollmentCount,
                            'capacity' => $class->capacity,
                            'percentage' => round(($enrollmentCount / $class->capacity) * 100),
                            'enrolled_student' => $student->full_name,
                            'url' => route('admin.classes.show', $class->id)
                        ]
                    );
                }
                
            } catch (\Exception $e) {
                Log::error('Failed to send enrollment notifications: ' . $e->getMessage());
            }

            return back()->with('success', 'Student enrolled successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error enrolling student: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'class_id' => $class->id,
                'student_id' => $validated['student_id']
            ]);
            
            return back()->with('error', 'Failed to enroll student. Please try again.');
        }
    }

    /**
     * Remove student from class 
     */
    public function unenrollStudent(ClassModel $class, Student $student)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        // if (!auth()->user()->can('manage enrollments')) {
        //     abort(403, 'You do not have permission to manage enrollments.');
        // }

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();
        
        try {
            $enrollment = $class->enrollments()
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->first();
            
            if (!$enrollment) {
                return back()->with('error', 'Student is not actively enrolled in this class!');
            }

            // ✅ ENHANCED: Set dropped date when unenrolling
            $enrollment->update([
                'status' => 'dropped',
                'dropped_date' => now(),
            ]);

            // ✅ ENHANCED: Activity log with proper model_id reference
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'unenrolled_student',
                'model_type' => 'ClassEnrollment',
                'model_id' => $enrollment->id, // ✅ Reference the enrollment, not the class
                'description' => "Removed {$student->first_name} {$student->last_name} from class: {$class->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            // ✅ ENHANCED: Comprehensive notifications after successful unenrollment
            try {
                // Notify parent with email
                if ($student->parent) {
                    NotificationHelper::notifyUser(
                        $student->parent,
                        'Student Removed from Class',
                        "{$student->full_name} has been removed from {$class->name} ({$class->subject})",
                        'unenrollment',
                        [
                            'student_id' => $student->id,
                            'student_name' => $student->full_name,
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'subject' => $class->subject,
                            'removed_by' => auth()->user()->name,
                            'removed_at' => now()->format('d M Y, H:i'),
                            'url' => route('parent.students.show', $student->id)
                        ],
                        true // ✅ Send email immediately
                    );
                }

                // Notify teacher with email
                if ($class->teacher) {
                    NotificationHelper::notifyUser(
                        $class->teacher,
                        'Student Removed from Class',
                        "{$student->full_name} has been removed from your class: {$class->name}",
                        'unenrollment',
                        [
                            'student_id' => $student->id,
                            'student_name' => $student->full_name,
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'removed_by' => auth()->user()->name,
                            'removed_at' => now()->format('d M Y, H:i'),
                            'url' => route('teacher.classes.show', $class->id)
                        ],
                        true // ✅ Send email immediately
                    );
                }

                // ✅ ADDED: Notify SuperAdmins about the unenrollment
                $superAdmins = User::where('role', 'superadmin')
                    ->where('status', 'active')
                    ->get();
                
                foreach ($superAdmins as $superAdmin) {
                    NotificationHelper::notifyUser(
                        $superAdmin,
                        'Student Removed from Class',
                        "{$student->full_name} has been removed from {$class->name} by " . auth()->user()->name,
                        'unenrollment',
                        [
                            'student_id' => $student->id,
                            'student_name' => $student->full_name,
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'subject' => $class->subject,
                            'removed_by' => auth()->user()->name,
                            'removed_at' => now()->format('d M Y, H:i'),
                            'url' => route('superadmin.classes.show', $class->id)
                        ]
                        // ✅ No immediate email for SuperAdmins (will be batched)
                    );
                }

                // ✅ ADDED: Notify other Admins about the unenrollment
                $otherAdmins = User::where('role', 'admin')
                    ->where('status', 'active')
                    ->where('id', '!=', auth()->id())
                    ->get();
                
                foreach ($otherAdmins as $admin) {
                    NotificationHelper::notifyUser(
                        $admin,
                        'Student Removed from Class',
                        "{$student->full_name} has been removed from {$class->name} by " . auth()->user()->name,
                        'unenrollment',
                        [
                            'student_id' => $student->id,
                            'student_name' => $student->full_name,
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'subject' => $class->subject,
                            'removed_by' => auth()->user()->name,
                            'removed_at' => now()->format('d M Y, H:i'),
                            'url' => route('admin.classes.show', $class->id)
                        ]
                        // ✅ No immediate email for other Admins (will be batched)
                    );
                }
                
            } catch (\Exception $e) {
                Log::error('Failed to send unenrollment notifications: ' . $e->getMessage());
            }

            return back()->with('success', "Student '{$student->full_name}' has been removed from '{$class->name}' successfully!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error unenrolling student: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'class_id' => $class->id,
                'student_id' => $student->id
            ]);
            
            return back()->with('error', 'Failed to remove student from class. Please try again.');
        }
    }
}
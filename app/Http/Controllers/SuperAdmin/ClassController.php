<?php

namespace App\Http\Controllers\SuperAdmin;

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
            $search = $request->search;
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

        $classes = $query->paginate(20);
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $subjects = ClassModel::distinct()->pluck('subject');
        $levels = ClassModel::distinct()->whereNotNull('level')->pluck('level');

        // Statistics
        $stats = [
            'total' => ClassModel::count(),
            'with_teacher' => ClassModel::whereNotNull('teacher_id')->count(),
            'without_teacher' => ClassModel::whereNull('teacher_id')->count(),
            'total_capacity' => ClassModel::sum('capacity'),
            'total_enrolled' => ClassEnrollment::where('status', 'active')->count(),
        ];

        return view('superadmin.classes.index', compact('classes', 'teachers', 'subjects', 'levels', 'stats'));
    }

    /**
     * Show the form for creating a new class
     */
    public function create()
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        return view('superadmin.classes.create', compact('teachers'));
    }

    /**
     * Store a newly created class
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:100',
            'level' => 'nullable|string|max:100',
            'room_number' => 'nullable|string|max:50',
            'teacher_id' => 'nullable|exists:users,id',
            'capacity' => 'required|integer|min:1|max:' . (int) \App\Models\SystemSetting::get('max_class_capacity', 100),
            'description' => 'nullable|string',
        ]);

        // Verify teacher role if provided
        if (isset($validated['teacher_id'])) {
            $teacher = User::find($validated['teacher_id']);
            if (!$teacher->isTeacher()) {
                return back()->withErrors(['teacher_id' => 'Selected user must be a teacher.'])->withInput();
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

        return redirect()->route('superadmin.classes.index')
            ->with('success', 'Class created successfully!');
    }

    /**
     * Display the specified class
     */
    public function show(ClassModel $class)
    {
        // Load relationships - ONLY load ACTIVE enrollments for the students relationship
        $class->load([
            'teacher',
            'schedules',
            'students' => function($query) {
                // Use the students relationship (many-to-many) and filter by active status
                $query->wherePivot('status', 'active')
                    ->orderBy('first_name', 'asc');
            },
            'attendance' => function($query) {
                $query->orderBy('date', 'desc')->limit(config('app.attendance_history_limit', 30));

            },
            'homeworkAssignments' => function($query) {
                $query->orderBy('due_date', 'desc')->limit(10);
            }
        ]);

        // Calculate statistics - Use ACTIVE enrollments only
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

        // Get available students for enrollment (not currently ACTIVE in this class)
        $enrolledStudentIds = $class->enrollments()
            ->where('status', 'active')
            ->pluck('student_id');
        
        $availableStudents = Student::where('status', 'active')
            ->whereNotIn('id', $enrolledStudentIds)
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

        return view('superadmin.classes.show', compact('class', 'availableStudents', 'stats', 'enrollmentHistory'));
    }


    /**
     * Show the form for editing the specified class
     */
    public function edit(ClassModel $class)
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        return view('superadmin.classes.edit', compact('class', 'teachers'));
    }

    /**
     * Update the specified class
     */
    public function update(Request $request, ClassModel $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:100',
            'level' => 'nullable|string|max:100',
            'room_number' => 'nullable|string|max:50',
            'teacher_id' => 'nullable|exists:users,id',
            'capacity' => 'required|integer|min:1|max:' . (int) \App\Models\SystemSetting::get('max_class_capacity', 100),
            'description' => 'nullable|string',
        ]);

        // Verify teacher role if provided
        if (isset($validated['teacher_id'])) {
            $teacher = User::find($validated['teacher_id']);
            if (!$teacher->isTeacher()) {
                return back()->withErrors(['teacher_id' => 'Selected user must be a teacher.'])->withInput();
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

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated_class',
            'model_type' => 'ClassModel',
            'model_id' => $class->id,
            'description' => "Updated class: {$class->name} ({$class->subject})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.classes.index')
            ->with('success', 'Class updated successfully!');
    }

    /**
     * Remove the specified class
     */
    public function destroy(ClassModel $class)
    {
        // Check if class has active enrollments
        if ($class->enrollments()->where('status', 'active')->count() > 0) {
            return back()->with('error', 'Cannot delete class with active students!');
        }

        $className = $class->name;
        $classId = $class->id;
        $class->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted_class',
            'model_type' => 'ClassModel',
            'model_id' => $classId,
            'description' => "Deleted class: {$className}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('superadmin.classes.index')
            ->with('success', 'Class deleted successfully!');
    }

    /**
     * Enroll student in class
     */
    public function enrollStudent(Request $request, ClassModel $class)
    {

        // ✅ ENHANCED: Comprehensive validation with custom messages
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
            // ✅ ENHANCED: Verify student is active
            $student = Student::find($validated['student_id']);
            if (!$student || $student->status !== 'active') {
                return back()->with('error', 'Selected student is not active.');
            }

            // ✅ ENHANCED: Check if class is full with explicit count
            $currentEnrollment = $class->enrollments()->where('status', 'active')->count();
            if ($currentEnrollment >= $class->capacity) {
                return back()->with('error', 'Class is at full capacity!');
            }

            // ✅ ENHANCED: Check if already enrolled with proper relationship
            $existingEnrollment = $class->enrollments()
                ->where('student_id', $validated['student_id'])
                ->where('status', 'active')
                ->first();
                
            if ($existingEnrollment) {
                return back()->with('error', 'Student is already enrolled in this class!');
            }

            // ✅ Create enrollment
            $enrollment = ClassEnrollment::create([
                'student_id' => $validated['student_id'],
                'class_id' => $class->id,
                'enrollment_date' => $validated['enrollment_date'],
                'status' => 'active',
            ]);

            // ✅ ENHANCED: Activity log with proper model_id reference
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'enrolled_student',
                'model_type' => 'ClassEnrollment',
                'model_id' => $enrollment->id, // ✅ Reference the enrollment, not the class
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

                // Notify teacher
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

                // ✅ ENHANCED: Capacity alert - Notify ALL admins (including SuperAdmins)
                $enrollmentCount = $class->enrollments()->where('status', 'active')->count();
                
                if ($enrollmentCount >= ($class->capacity * 0.9)) {
                    $message = $enrollmentCount >= $class->capacity 
                        ? "Class {$class->name} is now at full capacity ({$enrollmentCount}/{$class->capacity})"
                        : "Class {$class->name} is at {$enrollmentCount}/{$class->capacity} capacity (90%+ full)";
                    
                    // Notify SuperAdmins
                    NotificationHelper::notifySuperAdmins(
                        'Class Capacity Alert',
                        $message,
                        'capacity_alert', // ✅ Changed from 'schedule_change' to 'capacity_alert'
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

                    // ✅ ADDED: Also notify regular admins
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
                // ✅ Don't fail the entire enrollment if notifications fail
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

                // ✅ ADDED: Notify other SuperAdmins about the unenrollment
                $otherSuperAdmins = User::where('role', 'superadmin')
                    ->where('status', 'active')
                    ->where('id', '!=', auth()->id())
                    ->get();
                
                foreach ($otherSuperAdmins as $superAdmin) {
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
                        // ✅ No immediate email for other admins (will be batched)
                    );
                }

                // ✅ ADDED: Also notify regular admins
                $admins = User::where('role', 'admin')
                    ->where('status', 'active')
                    ->get();
                
                foreach ($admins as $admin) {
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
                        // ✅ No immediate email for admins (will be batched)
                    );
                }
                
            } catch (\Exception $e) {
                Log::error('Failed to send unenrollment notifications: ' . $e->getMessage());
                // ✅ Don't fail the entire unenrollment if notifications fail
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
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\Student;
use App\Models\ActivityLog;
use App\Models\ClassEnrollment;
use Illuminate\Http\Request;

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

        // Filter by teacher
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('room_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $classes = $query->orderBy('name')->paginate(20);
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $subjects = ClassModel::distinct()->pluck('subject');

        // Statistics
        $stats = [
            'total' => ClassModel::count(),
            'with_teacher' => ClassModel::whereNotNull('teacher_id')->count(),
            'total_enrolled' => ClassEnrollment::where('status', 'active')->count(),
        ];

        return view('admin.classes.index', compact('classes', 'teachers', 'subjects', 'stats'));
    }

    /**
     * Show the form for creating a new class
     */
    public function create()
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        return view('admin.classes.create', compact('teachers'));
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
            'capacity' => 'required|integer|min:1|max:100',
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

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class created successfully!');
    }

    /**
     * Display the specified class
     */
    public function show(ClassModel $class)
    {
        $class->load([
            'teacher',
            'schedules',
            'enrollments' => function($query) {
                $query->where('status', 'active')->with('student');
            },
            'attendance' => function($query) {
                $query->orderBy('date', 'desc')->limit(20);
            }
        ]);

        // Calculate statistics
        $totalEnrolled = $class->enrollments()->where('status', 'active')->count();
        $availableSeats = $class->capacity - $totalEnrolled;
        
        $stats = [
            'enrolled' => $totalEnrolled,
            'capacity' => $class->capacity,
            'available_seats' => $availableSeats,
        ];

        // Get unenrolled active students for enrollment
        $enrolledStudentIds = $class->enrollments()->where('status', 'active')->pluck('student_id');
        $availableStudents = Student::where('status', 'active')
            ->whereNotIn('id', $enrolledStudentIds)
            ->orderBy('first_name')
            ->get();

        return view('admin.classes.show', compact('class', 'availableStudents', 'stats'));
    }

    /**
     * Show the form for editing the specified class
     */
    public function edit(ClassModel $class)
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        return view('admin.classes.edit', compact('class', 'teachers'));
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
            'capacity' => 'required|integer|min:1|max:100',
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

        return redirect()->route('admin.classes.index')
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

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class deleted successfully!');
    }

    /**
     * Enroll student in class
     */
    public function enrollStudent(Request $request, ClassModel $class)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'enrollment_date' => 'required|date',
        ]);

        // Check if class is full
        $currentEnrollment = $class->enrollments()->where('status', 'active')->count();
        if ($currentEnrollment >= $class->capacity) {
            return back()->with('error', 'Class is at full capacity!');
        }

        // Check if already enrolled
        if ($class->enrollments()->where('student_id', $validated['student_id'])->where('status', 'active')->exists()) {
            return back()->with('error', 'Student is already enrolled in this class!');
        }

        ClassEnrollment::create([
            'student_id' => $validated['student_id'],
            'class_id' => $class->id,
            'enrollment_date' => $validated['enrollment_date'],
            'status' => 'active',
        ]);

        $student = Student::find($validated['student_id']);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'enrolled_student',
            'model_type' => 'ClassEnrollment',
            'model_id' => $class->id,
            'description' => "Enrolled {$student->first_name} {$student->last_name} in class: {$class->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Student enrolled successfully!');
    }

    /**
     * Remove student from class
     */
    public function unenrollStudent(ClassModel $class, Student $student)
    {
        $enrollment = $class->enrollments()->where('student_id', $student->id)->first();
        
        if (!$enrollment) {
            return back()->with('error', 'Student is not enrolled in this class!');
        }

        $enrollment->update(['status' => 'dropped']);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'unenrolled_student',
            'model_type' => 'ClassEnrollment',
            'model_id' => $class->id,
            'description' => "Removed {$student->first_name} {$student->last_name} from class: {$class->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Student removed from class!');
    }
}
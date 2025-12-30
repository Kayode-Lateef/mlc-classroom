<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Notifications\GeneralNotification;

class StudentController extends Controller
{
    /**
     * Display a listing of students
     */
    public function index(Request $request)
    {
        $query = Student::with('parent');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or parent
        if ($request->filled('search')) {
            $search = trim($request->search);
            
            $query->where(function($q) use ($search) {
                // Direct matches on first or last name
                $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%");
                
                // Full name search (handles "Minerva Harvey")
                $q->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                
                // Reverse full name search (handles "Harvey Minerva")
                $q->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$search}%"]);
                
                // Search by parent name
                $q->orWhereHas('parent', function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by parent
        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        // Filter by enrollment year
        if ($request->filled('enrollment_year')) {
            $query->whereYear('enrollment_date', $request->enrollment_year);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'first_name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $students = $query->paginate(20);
        $parents = User::where('role', 'parent')->orderBy('name')->get();

        // Statistics
        $stats = [
            'total' => Student::count(),
            'active' => Student::where('status', 'active')->count(),
            'inactive' => Student::where('status', 'inactive')->count(),
            'graduated' => Student::where('status', 'graduated')->count(),
            'withdrawn' => Student::where('status', 'withdrawn')->count(),
        ];

        return view('superadmin.students.index', compact('students', 'parents', 'stats'));
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        $parents = User::where('role', 'parent')->orderBy('name')->get();
        return view('superadmin.students.create', compact('parents'));
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'parent_id' => 'required|exists:users,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,inactive,graduated,withdrawn',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => ['nullable', 'string', 'max:20', 'regex:/^[+]?[0-9\s\-\(\)]+$/'],
            'medical_info' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'parent_id.required' => 'Please select a parent/guardian.',
            'parent_id.exists' => 'Selected parent does not exist.',
            'enrollment_date.required' => 'Enrollment date is required.',
            'status.required' => 'Status is required.',
            'emergency_phone.regex' => 'Emergency phone format is invalid. Only numbers, spaces, hyphens, parentheses, and + are allowed.',
            'profile_photo.image' => 'Profile photo must be an image.',
            'profile_photo.mimes' => 'Profile photo must be a file of type: jpeg, png, jpg, gif.',
            'profile_photo.max' => 'Profile photo size must not exceed 2MB.',
            
            ]);

        // Verify parent role
        $parent = User::find($validated['parent_id']);
        if (!$parent->isParent()) {
            return back()->withErrors(['parent_id' => 'Selected user must be a parent.'])->withInput();
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('student_photos', 'public');
        }

        $student = Student::create($validated);

        // Notify all superadmins
        $superadmins = User::where('role', 'superadmin')->get();
        
        foreach ($superadmins as $admin) {
            $admin->notify(new GeneralNotification([
                'type' => 'general',
                'title' => 'New Student Enrolled',
                'message' => "{$student->full_name} has been enrolled by {$student->parent->name}",
                'sent_by' => auth()->user()->name,
                'sent_at' => now()->format('d M Y, H:i'),
                'data' => [
                    'student_id' => $student->id,
                    'parent_id' => $student->parent_id,
                    'url' => route('superadmin.students.show', $student->id)
                ]
            ]));
        }

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'created_student',
            'model_type' => 'Student',
            'model_id' => $student->id,
            'description' => "Created student: {$student->first_name} {$student->last_name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.students.index')
            ->with('success', 'Student created successfully!');
    }

    /**
     * Display the specified student
     */
    public function show(Student $student)
    {
        $student->load([
            'parent',
            'enrollments.class.teacher',
            'attendance' => function($query) {
                $query->orderBy('date', 'desc')->limit(30);
            },
            'homeworkSubmissions.homeworkAssignment',
            'progressNotes.progressSheet'
        ]);

        // Calculate statistics
        $totalAttendance = $student->attendance()->count();
        $presentCount = $student->attendance()->where('status', 'present')->count();
        $lateCount = $student->attendance()->where('status', 'late')->count();
        $absentCount = $student->attendance()->where('status', 'absent')->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 2) : 0;

        // Homework statistics
        $totalHomework = $student->homeworkSubmissions()->count();
        $submittedHomework = $student->homeworkSubmissions()->where('status', 'submitted')->count();
        $gradedHomework = $student->homeworkSubmissions()->whereNotNull('grade')->count();

        $stats = [
            'attendance_rate' => $attendanceRate,
            'total_attendance' => $totalAttendance,
            'present' => $presentCount,
            'late' => $lateCount,
            'absent' => $absentCount,
            'total_homework' => $totalHomework,
            'submitted_homework' => $submittedHomework,
            'graded_homework' => $gradedHomework,
            'enrolled_classes' => $student->enrollments()->where('status', 'active')->count(),
        ];

        return view('superadmin.students.show', compact('student', 'stats'));
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit(Student $student)
    {
        $parents = User::where('role', 'parent')->orderBy('name')->get();
        return view('superadmin.students.edit', compact('student', 'parents'));
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'parent_id' => 'required|exists:users,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,inactive,graduated,withdrawn',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => ['nullable', 'string', 'max:20', 'regex:/^[+]?[0-9\s\-\(\)]+$/'],
            'medical_info' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'parent_id.required' => 'Please select a parent/guardian.',
            'parent_id.exists' => 'Selected parent does not exist.',
            'enrollment_date.required' => 'Enrollment date is required.',
            'status.required' => 'Status is required.',
            'emergency_phone.regex' => 'Emergency phone format is invalid. Only numbers, spaces, hyphens, parentheses, and + are allowed.',
            'profile_photo.image' => 'Profile photo must be an image.',
            'profile_photo.mimes' => 'Profile photo must be a file of type: jpeg, png, jpg, gif.',
            'profile_photo.max' => 'Profile photo must not be greater than 2MB.',
        ]);

        // Verify parent role
        $parent = User::find($validated['parent_id']);
        if (!$parent->isParent()) {
            return back()->withErrors(['parent_id' => 'Selected user must be a parent.'])->withInput();
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($student->profile_photo) {
                Storage::disk('public')->delete($student->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('student_photos', 'public');
        }

        $student->update($validated);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated_student',
            'model_type' => 'Student',
            'model_id' => $student->id,
            'description' => "Updated student: {$student->first_name} {$student->last_name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.students.index')
            ->with('success', 'Student updated successfully!');
    }

    /**
     * Remove the specified student
     */
    public function destroy(Student $student)
    {
        // Check if student has enrollments
        if ($student->enrollments()->count() > 0) {
            return back()->with('error', 'Cannot delete student with active class enrollments!');
        }

        $studentName = "{$student->first_name} {$student->last_name}";
        $studentId = $student->id;
        
        // Delete profile photo if exists
        if ($student->profile_photo) {
            Storage::disk('public')->delete($student->profile_photo);
        }

        $student->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted_student',
            'model_type' => 'Student',
            'model_id' => $studentId,
            'description' => "Deleted student: {$studentName}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('superadmin.students.index')
            ->with('success', 'Student deleted successfully!');
    }
}

<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\GeneralNotification;
use Carbon\Carbon;

class StudentController extends Controller
{
    /**
     * Display a listing of students
     */
    public function index(Request $request)
    {
        try {
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

            $students = $query->paginate(config('app.pagination.students', 20));
            $parents = User::where('role', 'parent')->orderBy('name')->get();

            // Statistics - Consider caching these in production
            $stats = [
                'total' => Student::count(),
                'active' => Student::where('status', 'active')->count(),
                'inactive' => Student::where('status', 'inactive')->count(),
                'graduated' => Student::where('status', 'graduated')->count(),
                'withdrawn' => Student::where('status', 'withdrawn')->count(),
            ];

            return view('superadmin.students.index', compact('students', 'parents', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Error loading students list: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An error occurred while loading students. Please try again.');
        }
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        // Check permission
        if (!auth()->user()->can('create-student')) {
            abort(403, 'Unauthorized action.');
        }

        $parents = User::where('role', 'parent')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
            
        return view('superadmin.students.create', compact('parents'));
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request)
    {
        // Check permission
        if (!auth()->user()->can('create-student')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => [
                'required',
                'date',
                'before:today',
                'after:' . now()->subYears(18)->format('Y-m-d'), // Not older than 18
                'before:' . now()->subYears(4)->format('Y-m-d')  // Not younger than 4
            ],
            'parent_id' => 'required|exists:users,id',
            'enrollment_date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:active,inactive,graduated,withdrawn',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => ['required', 'string', 'min:10', 'max:20', 'regex:/^(\+44\s?|0)[0-9\s\-\(\)]{9,}$/'],
            'medical_info' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'date_of_birth.after' => 'Student must be at least 4 years old.',
            'date_of_birth.before' => 'Student must be under 18 years old.',
            'parent_id.required' => 'Please select a parent/guardian.',
            'parent_id.exists' => 'Selected parent does not exist.',
            'enrollment_date.required' => 'Enrollment date is required.',
            'enrollment_date.before_or_equal' => 'Enrollment date cannot be in the future.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'emergency_phone.regex' => 'Emergency phone format is invalid. Only numbers, spaces, hyphens, parentheses, and + are allowed.',
            'emergency_phone.max' => 'Emergency phone number must not exceed 20 characters.',
            'profile_photo.image' => 'Profile photo must be an image.',
            'profile_photo.mimes' => 'Profile photo must be a file of type: jpeg, png, jpg, gif.',
            'profile_photo.max' => 'Profile photo size must not exceed 2MB.',
        ]);

        DB::beginTransaction();
        
        try {
            // Verify parent role
            $parent = User::find($validated['parent_id']);
            if (!$parent || !$parent->isParent()) {
                return back()
                    ->withErrors(['parent_id' => 'Selected user must be a parent.'])
                    ->withInput();
            }

            // Check for duplicate students
            $duplicate = Student::where('first_name', $validated['first_name'])
                ->where('last_name', $validated['last_name'])
                ->where('date_of_birth', $validated['date_of_birth'])
                ->where('parent_id', $validated['parent_id'])
                ->first();

            if ($duplicate) {
                return back()
                    ->withErrors(['duplicate' => 'A student with the same name, date of birth, and parent already exists.'])
                    ->withInput();
            }

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                try {
                    $validated['profile_photo'] = $request->file('profile_photo')
                        ->store('student_photos', 'public');
                } catch (\Exception $e) {
                    Log::error('Failed to upload student photo: ' . $e->getMessage());
                    return back()
                        ->withErrors(['profile_photo' => 'Failed to upload photo. Please try again.'])
                        ->withInput();
                }
            }

            // Create student
            $student = Student::create($validated);

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

            DB::commit();

            // Notify superadmins (after transaction commits)
            try {
                $superadmins = User::where('role', 'superadmin')
                    ->where('status', 'active')
                    ->where('id', '!=', auth()->id()) // Don't notify self
                    ->get();
                
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

                // Notify parent
                $parent->notify(new GeneralNotification([
                    'type' => 'general',
                    'title' => 'Student Profile Created',
                    'message' => "A student profile has been created for {$student->full_name}",
                    'sent_by' => auth()->user()->name,
                    'sent_at' => now()->format('d M Y, H:i'),
                    'data' => [
                        'student_id' => $student->id,
                        'url' => route('parent.students.show', $student->id)
                    ]
                ]));
                
            } catch (\Exception $e) {
                // Log notification failure but don't fail the request
                Log::error('Failed to send student creation notifications: ' . $e->getMessage());
            }

            return redirect()->route('superadmin.students.index')
                ->with('success', 'Student created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded file if exists
            if (isset($validated['profile_photo'])) {
                Storage::disk('public')->delete($validated['profile_photo']);
            }
            
            Log::error('Error creating student: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);
            
            return back()
                ->withErrors(['error' => 'Failed to create student. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Display the specified student
     */
    public function show(Student $student)
    {
        try {
            $student->load([
                'parent',
                'enrollments.class.teacher',
                'enrollments.class.schedules',
                'attendance' => function($query) {
                    $query->orderBy('date', 'desc')
                        ->limit(config('app.attendance_history_limit', 30));
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

            // Calculate age
            $age = Carbon::parse($student->date_of_birth)->age;

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
                'age' => $age,
            ];

            return view('superadmin.students.show', compact('student', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Error loading student details: ' . $e->getMessage(), [
                'student_id' => $student->id
            ]);
            
            return back()->with('error', 'An error occurred while loading student details.');
        }
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit(Student $student)
    {
        // Check permission
        if (!auth()->user()->can('edit-student')) {
            abort(403, 'Unauthorized action.');
        }

        $parents = User::where('role', 'parent')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
            
        return view('superadmin.students.edit', compact('student', 'parents'));
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, Student $student)
    {
        // Check permission
        if (!auth()->user()->can('edit-student')) {
            abort(403, 'Unauthorized action.');
        }

        // Store old values for comparison
        $oldStatus = $student->status;
        $oldParentId = $student->parent_id;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => [
                'required',
                'date',
                'before:today',
                'after:' . now()->subYears(18)->format('Y-m-d'),
                'before:' . now()->subYears(4)->format('Y-m-d')
            ],
            'parent_id' => 'required|exists:users,id',
            'enrollment_date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:active,inactive,graduated,withdrawn',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => ['required', 'string', 'min:10', 'max:20', 'regex:/^(\+44\s?|0)[0-9\s\-\(\)]{9,}$/'],
            'medical_info' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'date_of_birth.after' => 'Student must be at least 4 years old.',
            'date_of_birth.before' => 'Student must be under 18 years old.',
            'parent_id.required' => 'Please select a parent/guardian.',
            'parent_id.exists' => 'Selected parent does not exist.',
            'enrollment_date.required' => 'Enrollment date is required.',
            'enrollment_date.before_or_equal' => 'Enrollment date cannot be in the future.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'emergency_phone.regex' => 'Emergency phone format is invalid. Only numbers, spaces, hyphens, parentheses, and + are allowed.',
            'emergency_phone.max' => 'Emergency phone number must not exceed 20 characters.',
            'profile_photo.image' => 'Profile photo must be an image.',
            'profile_photo.mimes' => 'Profile photo must be a file of type: jpeg, png, jpg, gif.',
            'profile_photo.max' => 'Profile photo size must not exceed 2MB.',
        ]);

        DB::beginTransaction();
        
        try {
            // Verify parent role
            $parent = User::find($validated['parent_id']);
            if (!$parent || !$parent->isParent()) {
                return back()
                    ->withErrors(['parent_id' => 'Selected user must be a parent.'])
                    ->withInput();
            }

            // Check for duplicate students (excluding current student)
            $duplicate = Student::where('id', '!=', $student->id)
                ->where('first_name', $validated['first_name'])
                ->where('last_name', $validated['last_name'])
                ->where('date_of_birth', $validated['date_of_birth'])
                ->where('parent_id', $validated['parent_id'])
                ->first();

            if ($duplicate) {
                return back()
                    ->withErrors(['duplicate' => 'Another student with the same name, date of birth, and parent already exists.'])
                    ->withInput();
            }

            $oldPhotoPath = $student->profile_photo;

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                try {
                    $validated['profile_photo'] = $request->file('profile_photo')
                        ->store('student_photos', 'public');
                } catch (\Exception $e) {
                    Log::error('Failed to upload student photo: ' . $e->getMessage());
                    return back()
                        ->withErrors(['profile_photo' => 'Failed to upload photo. Please try again.'])
                        ->withInput();
                }
            }

            // Update student
            $student->update($validated);

            // Delete old photo if new one was uploaded
            if ($request->hasFile('profile_photo') && $oldPhotoPath) {
                try {
                    Storage::disk('public')->delete($oldPhotoPath);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old student photo: ' . $e->getMessage());
                }
            }

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

            DB::commit();

            // Send notifications after successful update
            try {
                // Notify parent if status changed or parent changed
                if ($oldStatus !== $validated['status'] || $oldParentId !== $validated['parent_id']) {
                    $parent->notify(new GeneralNotification([
                        'type' => 'general',
                        'title' => 'Student Information Updated',
                        'message' => "Information for {$student->full_name} has been updated",
                        'sent_by' => auth()->user()->name,
                        'sent_at' => now()->format('d M Y, H:i'),
                        'data' => [
                            'student_id' => $student->id,
                            'url' => route('parent.students.show', $student->id)
                        ]
                    ]));
                }

                // If status changed to graduated or withdrawn, notify superadmins
                if ($oldStatus !== $validated['status'] && 
                    in_array($validated['status'], ['graduated', 'withdrawn'])) {
                    
                    $superadmins = User::where('role', 'superadmin')
                        ->where('status', 'active')
                        ->where('id', '!=', auth()->id())
                        ->get();
                    
                    foreach ($superadmins as $admin) {
                        $admin->notify(new GeneralNotification([
                            'type' => 'general',
                            'title' => 'Student Status Changed',
                            'message' => "{$student->full_name} status changed to " . ucfirst($validated['status']),
                            'sent_by' => auth()->user()->name,
                            'sent_at' => now()->format('d M Y, H:i'),
                            'data' => [
                                'student_id' => $student->id,
                                'old_status' => $oldStatus,
                                'new_status' => $validated['status'],
                                'url' => route('superadmin.students.show', $student->id)
                            ]
                        ]));
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to send student update notifications: ' . $e->getMessage());
            }

            return redirect()->route('superadmin.students.index')
                ->with('success', 'Student updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up newly uploaded file if exists
            if (isset($validated['profile_photo']) && $validated['profile_photo'] !== $oldPhotoPath) {
                Storage::disk('public')->delete($validated['profile_photo']);
            }
            
            Log::error('Error updating student: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'student_id' => $student->id,
                'data' => $validated
            ]);
            
            return back()
                ->withErrors(['error' => 'Failed to update student. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified student (SOFT DELETE)
     */
    public function destroy(Student $student)
    {
        // Check permission
        if (!auth()->user()->can('delete-student')) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        
        try {
            // Enhanced validation - check all relationships
            $hasEnrollments = $student->enrollments()->count() > 0;
            $hasAttendance = $student->attendance()->count() > 0;
            $hasHomework = $student->homeworkSubmissions()->count() > 0;
            $hasProgress = $student->progressNotes()->count() > 0;

            if ($hasEnrollments || $hasAttendance || $hasHomework || $hasProgress) {
                $relationships = [];
                if ($hasEnrollments) $relationships[] = $student->enrollments()->count() . ' class enrollment(s)';
                if ($hasAttendance) $relationships[] = $student->attendance()->count() . ' attendance record(s)';
                if ($hasHomework) $relationships[] = $student->homeworkSubmissions()->count() . ' homework submission(s)';
                if ($hasProgress) $relationships[] = $student->progressNotes()->count() . ' progress note(s)';

                return back()->with('error', 
                    'Cannot delete student with existing data: ' . implode(', ', $relationships) . 
                    '. Please change student status to "Withdrawn" or "Graduated" instead.');
            }

            $studentName = "{$student->first_name} {$student->last_name}";
            $studentId = $student->id;
            $parentId = $student->parent_id;
            
            // Store photo path for cleanup
            $photoPath = $student->profile_photo;

            // Soft delete the student
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

            DB::commit();

            // Delete profile photo after successful soft delete
            if ($photoPath) {
                try {
                    Storage::disk('public')->delete($photoPath);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete student photo after soft delete: ' . $e->getMessage());
                }
            }

            // Notify parent
            try {
                $parent = User::find($parentId);
                if ($parent) {
                    $parent->notify(new GeneralNotification([
                        'type' => 'general',
                        'title' => 'Student Profile Removed',
                        'message' => "The student profile for {$studentName} has been removed from the system",
                        'sent_by' => auth()->user()->name,
                        'sent_at' => now()->format('d M Y, H:i'),
                        'data' => []
                    ]));
                }
            } catch (\Exception $e) {
                Log::error('Failed to send student deletion notification: ' . $e->getMessage());
            }

            return redirect()->route('superadmin.students.index')
                ->with('success', 'Student deleted successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting student: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'student_id' => $student->id
            ]);
            
            return back()->with('error', 'Failed to delete student. Please try again.');
        }
    }

    /**
     * Check for potential duplicate students
     * AJAX endpoint for real-time duplicate detection
     */
    public function checkDuplicate(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'date_of_birth' => 'required|date',
                'parent_id' => 'required|exists:users,id',
                'student_id' => 'nullable|exists:students,id' // For edit mode
            ]);

            $query = Student::where('first_name', $request->first_name)
                ->where('last_name', $request->last_name)
                ->where('date_of_birth', $request->date_of_birth)
                ->where('parent_id', $request->parent_id);

            // Exclude current student if editing
            if ($request->filled('student_id')) {
                $query->where('id', '!=', $request->student_id);
            }

            $duplicate = $query->first();

            if ($duplicate) {
                return response()->json([
                    'duplicate' => true,
                    'student' => [
                        'id' => $duplicate->id,
                        'full_name' => $duplicate->full_name,
                        'enrollment_date' => $duplicate->enrollment_date,
                        'status' => $duplicate->status,
                        'url' => route('superadmin.students.show', $duplicate->id)
                    ]
                ]);
            }

            return response()->json(['duplicate' => false]);
            
        } catch (\Exception $e) {
            Log::error('Error checking duplicate student: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to check for duplicates'], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

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
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('view students')) {
            abort(403, 'You do not have permission to view students.');
        }

        try {
            $query = Student::with('parent');

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search by name or parent (Enhanced search)
            if ($request->filled('search')) {
                $search = trim($request->search);
                
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
                    
                    $q->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$search}%"]);
                    
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

            // ✅ ENHANCED: Statistics with total weekly hours
            $stats = [
                'total' => Student::count(),
                'active' => Student::where('status', 'active')->count(),
                'inactive' => Student::where('status', 'inactive')->count(),
                'graduated' => Student::where('status', 'graduated')->count(),
                'withdrawn' => Student::where('status', 'withdrawn')->count(),
                'total_weekly_hours' => Student::where('status', 'active')->sum('weekly_hours'),
            ];

            return view('admin.students.index', compact('students', 'parents', 'stats'));
            
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
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('create students')) {
            abort(403, 'You do not have permission to create students.');
        }

        $parents = User::where('role', 'parent')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
            
        return view('admin.students.create', compact('parents'));
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('create students')) {
            abort(403, 'You do not have permission to create students.');
        }

        // ✅ ENHANCED: Comprehensive validation with UK requirements
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => [
                'required',
                'date',
                'after:' . now()->subYears(18)->format('Y-m-d'),  // Must be born after this date (< 18 years old)
                'before:' . now()->subYears(6)->format('Y-m-d'),   // Must be born before this date (> 6 years old)
            ],
            'parent_id' => 'required|exists:users,id',
            'enrollment_date' => 'required|date|before_or_equal:today',
            'weekly_hours' => [
                'required',
                'numeric',
                'min:0.5',
                'max:15',
                'regex:/^([0-9]|1[0-5])(\.[05])?$/'  // Only .0 or .5 decimals, max 15
            ],
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
            'date_of_birth.after' => 'Student must be under 18 years old.',
            'date_of_birth.before' => 'Student must be at least 6 years old.',
            'parent_id.required' => 'Please select a parent/guardian.',
            'parent_id.exists' => 'Selected parent does not exist.',
            'enrollment_date.required' => 'Enrollment date is required.',
            'enrollment_date.before_or_equal' => 'Enrollment date cannot be in the future.',
            'weekly_hours.required' => 'Weekly hours is required.',
            'weekly_hours.numeric' => 'Weekly hours must be a number.',
            'weekly_hours.min' => 'Weekly hours must be at least 0.5 hours.',
            'weekly_hours.max' => 'Weekly hours cannot exceed 15 hours.',
            'weekly_hours.regex' => 'Weekly hours must be in 0.5 hour increments (e.g., 0.5, 1.0, 1.5, 2.0).',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'emergency_phone.required' => 'Emergency phone number is required.',
            'emergency_phone.regex' => 'Emergency phone format is invalid. Only UK phone numbers are allowed.',
            'emergency_phone.max' => 'Emergency phone number must not exceed 20 characters.',
            'profile_photo.image' => 'Profile photo must be an image.',
            'profile_photo.mimes' => 'Profile photo must be a file of type: jpeg, png, jpg, gif.',
            'profile_photo.max' => 'Profile photo size must not exceed 2MB.',
        ]);

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();
        
        try {
            // Verify parent role
            $parent = User::find($validated['parent_id']);
            if (!$parent || !$parent->isParent()) {
                return back()
                    ->withErrors(['parent_id' => 'Selected user must be a parent.'])
                    ->withInput();
            }

            // ✅ ADDED: Check for duplicate students
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

            // ✅ ENHANCED: Activity log with weekly hours
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created_student',
                'model_type' => 'Student',
                'model_id' => $student->id,
                'description' => "Created student: {$student->first_name} {$student->last_name} with {$student->weekly_hours} weekly hours",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // ✅ Notify admins and parent - WITH EMAIL
            try {
                // Notify other admins
                $admins = User::whereIn('role', ['superadmin', 'admin'])
                    ->where('status', 'active')
                    ->where('id', '!=', auth()->id())
                    ->get();
                
                foreach ($admins as $admin) {
                    \App\Helpers\NotificationHelper::notifyUser(
                        $admin,
                        'New Student Enrolled',
                        "{$student->full_name} has been enrolled by {$student->parent->name} ({$student->weekly_hours} hours/week)",
                        'general',
                        [
                            'student_id' => $student->id,
                            'parent_id' => $student->parent_id,
                            'weekly_hours' => $student->weekly_hours,
                            'sent_by' => auth()->user()->name,
                            'sent_at' => now()->format('d M Y, H:i'),
                            'url' => route('admin.students.show', $student->id)
                        ],
                        true  // ✅ Send email immediately (1-3 recipients)
                    );
                }

                // ✅ Notify parent with email
                \App\Helpers\NotificationHelper::notifyUser(
                    $parent,
                    'Student Profile Created',
                    "A student profile has been created for {$student->full_name} with {$student->weekly_hours} hours per week",
                    'general',
                    [
                        'student_id' => $student->id,
                        'sent_by' => auth()->user()->name,
                        'sent_at' => now()->format('d M Y, H:i'),
                        'url' => route('parent.students.show', $student->id)
                    ],
                    true  // ✅ Send email immediately
                );
                
            } catch (\Exception $e) {
                Log::error('Failed to send student creation notifications: ' . $e->getMessage());
            }

            return redirect()->route('admin.students.index')
                ->with('success', 'Student created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
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
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('view students')) {
            abort(403, 'You do not have permission to view students.');
        }

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
                'progressNotes.progressSheet',
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

            // ✅ ENHANCED: Calculate age
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
                'weekly_hours' => $student->weekly_hours,
                'monthly_hours' => $student->monthly_hours,
            ];

            return view('admin.students.show', compact('student', 'stats'));
            
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
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('edit students')) {
            abort(403, 'You do not have permission to edit students.');
        }

        $parents = User::where('role', 'parent')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
            
        return view('admin.students.edit', compact('student', 'parents'));
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, Student $student)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('edit students')) {
            abort(403, 'You do not have permission to edit students.');
        }

        // ✅ ADDED: Store old values for comparison
        $oldStatus = $student->status;
        $oldParentId = $student->parent_id;
        $oldWeeklyHours = $student->weekly_hours;

        // ✅ ENHANCED: Comprehensive validation with UK requirements
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => [
                'required',
                'date',
                'before:today',
                'after:' . now()->subYears(18)->format('Y-m-d'),
                'before:' . now()->subYears(6)->format('Y-m-d')
            ],
            'parent_id' => 'required|exists:users,id',
            'enrollment_date' => 'required|date|before_or_equal:today',
            'weekly_hours' => [
                'required',
                'numeric',
                'min:0.5',
                'max:15',
                'regex:/^([0-9]|1[0-5])(\.[05])?$/'
            ],
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
            'date_of_birth.after' => 'Student must be at least 6 years old.',
            'parent_id.required' => 'Please select a parent/guardian.',
            'parent_id.exists' => 'Selected parent does not exist.',
            'enrollment_date.required' => 'Enrollment date is required.',
            'enrollment_date.before_or_equal' => 'Enrollment date cannot be in the future.',
            'weekly_hours.required' => 'Weekly hours is required.',
            'weekly_hours.numeric' => 'Weekly hours must be a number.',
            'weekly_hours.min' => 'Weekly hours must be at least 0.5 hours.',
            'weekly_hours.max' => 'Weekly hours cannot exceed 15 hours.',
            'weekly_hours.regex' => 'Weekly hours must be in 0.5 hour increments (e.g., 0.5, 1.0, 1.5, 2.0).',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'emergency_phone.regex' => 'Emergency phone format is invalid.',
            'emergency_phone.max' => 'Emergency phone number must not exceed 20 characters.',
            'profile_photo.image' => 'Profile photo must be an image.',
            'profile_photo.mimes' => 'Profile photo must be a file of type: jpeg, png, jpg, gif.',
            'profile_photo.max' => 'Profile photo size must not exceed 2MB.',
        ]);

        // ✅ ADDED: Database transaction for data integrity
        DB::beginTransaction();
        
        try {
            // Verify parent role
            $parent = User::find($validated['parent_id']);
            if (!$parent || !$parent->isParent()) {
                return back()
                    ->withErrors(['parent_id' => 'Selected user must be a parent.'])
                    ->withInput();
            }

            // ✅ ADDED: Check for duplicate students (excluding current student)
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

            // ✅ ENHANCED: Activity log with change tracking
            $changes = [];
            if ($oldWeeklyHours != $validated['weekly_hours']) {
                $changes[] = "hours changed from {$oldWeeklyHours} to {$validated['weekly_hours']}";
            }
            if ($oldStatus != $validated['status']) {
                $changes[] = "status changed from {$oldStatus} to {$validated['status']}";
            }
            
            $changeDescription = count($changes) > 0 ? ' (' . implode(', ', $changes) . ')' : '';
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated_student',
                'model_type' => 'Student',
                'model_id' => $student->id,
                'description' => "Updated student: {$student->first_name} {$student->last_name}{$changeDescription}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // ✅ ADDED: Send notifications after successful update
            try {
                // Notify parent if status changed, parent changed, or hours changed
                if ($oldStatus !== $validated['status'] || 
                    $oldParentId !== $validated['parent_id'] ||
                    $oldWeeklyHours != $validated['weekly_hours']) {
                    
                    $message = "Information for {$student->full_name} has been updated";
                    if ($oldWeeklyHours != $validated['weekly_hours']) {
                        $message .= " - Weekly hours changed from {$oldWeeklyHours} to {$validated['weekly_hours']}";
                    }
                    
                    $parent->notify(new GeneralNotification([
                        'type' => 'general',
                        'title' => 'Student Information Updated',
                        'message' => $message,
                        'sent_by' => auth()->user()->name,
                        'sent_at' => now()->format('d M Y, H:i'),
                        'data' => [
                            'student_id' => $student->id,
                            'url' => route('parent.students.show', $student->id)
                        ]
                    ]));
                }

                // If status changed to graduated or withdrawn, notify admins
                if ($oldStatus !== $validated['status'] && 
                    in_array($validated['status'], ['graduated', 'withdrawn'])) {
                    
                    $admins = User::whereIn('role', ['superadmin', 'admin'])
                        ->where('status', 'active')
                        ->where('id', '!=', auth()->id())
                        ->get();
                    
                    foreach ($admins as $admin) {
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
                                'url' => route('admin.students.show', $student->id)
                            ]
                        ]));
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to send student update notifications: ' . $e->getMessage());
            }

            return redirect()->route('admin.students.index')
                ->with('success', 'Student updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
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
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('delete students')) {
            abort(403, 'You do not have permission to delete students.');
        }

        // Check for existing data BEFORE starting transaction
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
                '. Please change student status to Withdrawn or Graduated instead.');
        }

        DB::beginTransaction();
        try {

            $studentName = "{$student->first_name} {$student->last_name}";
            $studentId = $student->id;
            $parentId = $student->parent_id;
            $photoPath = $student->profile_photo;

            $student->delete();

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

            // ✅ ADDED: Clean up photo after successful delete
            if ($photoPath) {
                try {
                    Storage::disk('public')->delete($photoPath);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete student photo after soft delete: ' . $e->getMessage());
                }
            }

            // ✅ ADDED: Notify parent of deletion
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

            return redirect()->route('admin.students.index')
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
     * ✅ ADDED: Check for potential duplicate students
     * AJAX endpoint for real-time duplicate detection
     */
    public function checkDuplicate(Request $request)
    {
        // ✅ GRANULAR PERMISSION CHECK for Admins
        if (!auth()->user()->can('view students')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'date_of_birth' => 'required|date',
                'parent_id' => 'required|exists:users,id',
                'student_id' => 'nullable|exists:students,id'
            ]);

            $query = Student::where('first_name', $request->first_name)
                ->where('last_name', $request->last_name)
                ->where('date_of_birth', $request->date_of_birth)
                ->where('parent_id', $request->parent_id);

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
                        'weekly_hours' => $duplicate->weekly_hours,
                        'url' => route('admin.students.show', $duplicate->id)
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
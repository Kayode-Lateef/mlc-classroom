@extends('layouts.app')

@section('title', 'Edit Progress Sheet')

@push('styles')
    <style>
        .current-info-box {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .current-info-box h4 {
            color: #0066cc;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .current-info-item {
            margin-bottom: 12px;
        }

        .current-info-item:last-child {
            margin-bottom: 0;
        }

        .current-info-label {
            color: #004080;
            margin-bottom: 3px;
        }

        .current-info-value {
            font-weight: 500;
            color: #0066cc;
        }

        .guidelines-box {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .guidelines-box h4 {
            font-weight: 600;
            margin-bottom: 10px;
        }

        .guidelines-box ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .guidelines-box li {
            color: #6c757d;
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
        }

        .guidelines-box li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #007bff;
            font-weight: bold;
        }

        .student-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff;
            transition: background-color 0.2s;
        }

        .student-item:hover {
            background-color: #f8f9fa;
        }

        .student-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }

        .student-avatar-initial {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background-color: #007bff;
            color: white;
        }

        .performance-legend {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
        }

        .performance-legend h4 {
            font-weight: 600;
            margin-bottom: 12px;
        }

        .performance-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .performance-badge-small {
            padding: 4px 10px;
            border-radius: 12px;
            margin-right: 8px;
            white-space: nowrap;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row">
                    <div class="col-lg-8 p-r-0 title-margin-right">
                        <div class="page-header">
                            <div class="page-title">
                                <h1>Edit Progress Sheet</h1>
                            </div>
                        </div>
                        <span>{{ $progressSheet->topic }}</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.progress-sheets.index') }}">Progress Sheets</a></li>
                                    <li><a href="{{ route('admin.progress-sheets.show', $progressSheet) }}">Details</a></li>
                                    <li class="active">Edit</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.progress-sheets.update', $progressSheet) }}" method="POST" id="progressSheetEditForm">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Main Content -->
                        <div class="col-lg-8">
                            <!-- Basic Information -->
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-info-alt"></i> Basic Information</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Class Selection -->
                                    <div class="form-group">
                                        <label>Class <span class="text-danger">*</span></label>
                                        <select 
                                            name="class_id" 
                                            id="class_id"
                                            required
                                            class="form-control @error('class_id') is-invalid @enderror"
                                        >
                                            <option value="">Select a class...</option>
                                            @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id', $progressSheet->class_id) == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }} - {{ $class->subject }}
                                                @if($class->teacher)
                                                ({{ $class->teacher->name }})
                                                @endif
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('class_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Date -->
                                    <div class="form-group">
                                        <label>Date <span class="text-danger">*</span></label>
                                        <input 
                                            type="date" 
                                            name="date" 
                                            value="{{ old('date', $progressSheet->date->format('Y-m-d')) }}"
                                            max="{{ now()->format('Y-m-d') }}"
                                            required
                                            class="form-control @error('date') is-invalid @enderror"
                                        >
                                        @error('date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Schedule -->
                                    <div class="form-group" id="schedule-container" style="display: none;">
                                        <label>Schedule (Optional)</label>
                                        <select 
                                            name="schedule_id"
                                            id="schedule_id"
                                            class="form-control @error('schedule_id') is-invalid @enderror"
                                        >
                                            <option value="">No specific schedule</option>
                                        </select>
                                        @error('schedule_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Topic -->
                                    <div class="form-group">
                                        <label>Topic <span class="text-danger">*</span></label>
                                        <input 
                                            type="text" 
                                            name="topic" 
                                            value="{{ old('topic', $progressSheet->topic) }}"
                                            placeholder="e.g., Algebra - Quadratic Equations"
                                            required
                                            maxlength="255"
                                            class="form-control @error('topic') is-invalid @enderror"
                                        >
                                        @error('topic')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Objective -->
                                    <div class="form-group">
                                        <label>Lesson Objective</label>
                                        <textarea 
                                            name="objective" 
                                            rows="3"
                                            maxlength="1000"
                                            placeholder="What were the learning objectives for this lesson?"
                                            class="form-control @error('objective') is-invalid @enderror"
                                        >{{ old('objective', $progressSheet->objective) }}</textarea>
                                        @error('objective')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- General Notes -->
                                    <div class="form-group">
                                        <label>General Class Notes</label>
                                        <textarea 
                                            name="notes" 
                                            rows="4"
                                            maxlength="2000"
                                            placeholder="Overall observations about the class..."
                                            class="form-control @error('notes') is-invalid @enderror"
                                        >{{ old('notes', $progressSheet->notes) }}</textarea>
                                        @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Student Notes -->
                            <div class="card alert" id="student-notes-card" style="display: none;">
                                <div class="card-header mb-3">
                                    <h4>
                                        <i class="ti-user"></i> Student Progress Notes
                                        <span class="badge badge-primary" id="student-count"></span>
                                    </h4>
                                </div>
                                <div class="card-body" id="students-container">
                                    <!-- Students will be loaded here via JavaScript -->
                                </div>
                            </div>

                            <!-- No Students Warning -->
                            <div class="alert alert-warning" id="no-students-warning" style="display: none;">
                                <i class="ti-info-alt"></i>
                                <strong>No students enrolled</strong> - 
                                No students are enrolled in this class. You can still update the progress sheet, but there won't be any student notes.
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <!-- Current Information -->
                            <div class="current-info-box">
                                <h4><i class="ti-info-alt"></i> Current Information</h4>
                                
                                <div class="current-info-item">
                                    <div class="current-info-label">Class</div>
                                    <div class="current-info-value">{{ $progressSheet->class->name }}</div>
                                </div>

                                <div class="current-info-item">
                                    <div class="current-info-label">Original Date</div>
                                    <div class="current-info-value">{{ $progressSheet->date->format('d M Y') }}</div>
                                </div>

                                <div class="current-info-item">
                                    <div class="current-info-label">Teacher</div>
                                    <div class="current-info-value">{{ $progressSheet->teacher->name }}</div>
                                </div>

                                <div class="current-info-item">
                                    <div class="current-info-label">Student Notes</div>
                                    <div class="current-info-value">{{ $progressSheet->progressNotes->count() }} recorded</div>
                                </div>

                                <div class="current-info-item">
                                    <div class="current-info-label">Created</div>
                                    <div class="current-info-value">{{ $progressSheet->created_at->format('d M Y') }}</div>
                                </div>

                                @if($progressSheet->updated_at != $progressSheet->created_at)
                                <div class="current-info-item">
                                    <div class="current-info-label">Last Updated</div>
                                    <div class="current-info-value">{{ $progressSheet->updated_at->diffForHumans() }}</div>
                                </div>
                                @endif
                            </div>

                            <!-- Guidelines -->
                            <div class="guidelines-box">
                                <h4><i class="ti-light-bulb"></i> Edit Guidelines</h4>
                                <ul>
                                    <li>Changing the class will reload student notes</li>
                                    <li>Existing student notes will be preserved when possible</li>
                                    <li>Parents may be notified of significant changes</li>
                                    <li>All changes are logged in the activity history</li>
                                </ul>
                            </div>

                            <!-- Performance Legend -->
                            <div class="performance-legend">
                                <h4>Performance Levels</h4>
                                <div class="performance-item">
                                    <span class="performance-badge-small badge-success">✨ Excellent</span>
                                    <span style="color: #6c757d;">Exceeds expectations</span>
                                </div>
                                <div class="performance-item">
                                    <span class="performance-badge-small badge-info">✓ Good</span>
                                    <span style="color: #6c757d;">Meets expectations</span>
                                </div>
                                <div class="performance-item">
                                    <span class="performance-badge-small badge-warning">~ Average</span>
                                    <span style="color: #6c757d;">Satisfactory progress</span>
                                </div>
                                <div class="performance-item">
                                    <span class="performance-badge-small badge-danger">⚠ Struggling</span>
                                    <span style="color: #6c757d;">Needs support</span>
                                </div>
                                <div class="performance-item">
                                    <span class="performance-badge-small badge-secondary">✗ Absent</span>
                                    <span style="color: #6c757d;">Not present</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <button type="button" class="btn btn-danger" onclick="confirmDeleteProgressSheet()">
                                                <i class="ti-trash"></i> Delete Progress Sheet
                                            </button>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.progress-sheets.show', $progressSheet) }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary" id="updateBtn">
                                                <i class="ti-check"></i> Update Progress Sheet
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Separate Delete Form -->
                <form id="delete-form" action="{{ route('admin.progress-sheets.destroy', $progressSheet) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>

                <!-- Footer -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer">
                            <p>MLC Classroom - Edit Progress Sheet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let students = [];
    let schedules = [];
    const existingNotesData = {!! json_encode($progressSheet->progressNotes->map(function($note) {
        return [
            'student_id' => $note->student_id,
            'performance' => $note->performance,
            'notes' => $note->notes,
        ];
    })->toArray()) !!};
    let existingNotes = existingNotesData;

    // Initial load
    const initialClassId = $('#class_id').val();
    if (initialClassId) {
        loadStudents(initialClassId);
    }

    // Load students when class changes
    $('#class_id').on('change', function() {
        const classId = $(this).val();
        
        if (!classId) {
            $('#student-notes-card').hide();
            $('#no-students-warning').hide();
            $('#schedule-container').hide();
            students = [];
            schedules = [];
            return;
        }

        loadStudents(classId);
    });

    function loadStudents(classId) {
        $.ajax({
            url: '{{ route("admin.progress-sheets.get-students") }}',
            method: 'GET',
            data: { class_id: classId },
            success: function(data) {
                students = data.students || [];
                schedules = data.schedules || [];

                // Update schedules dropdown
                if (schedules.length > 0) {
                    $('#schedule-container').show();
                    $('#schedule_id').empty().append('<option value="">No specific schedule</option>');
                    schedules.forEach(function(schedule) {
                        const selected = schedule.id == {{ $progressSheet->schedule_id ?? 'null' }} ? 'selected' : '';
                        $('#schedule_id').append(
                            `<option value="${schedule.id}" ${selected}>${schedule.day_of_week} - ${schedule.start_time} to ${schedule.end_time}</option>`
                        );
                    });
                } else {
                    $('#schedule-container').hide();
                }

                // Render students
                if (students.length > 0) {
                    renderStudents();
                    $('#student-notes-card').show();
                    $('#no-students-warning').hide();
                    $('#student-count').text(students.length);
                } else {
                    $('#student-notes-card').hide();
                    $('#no-students-warning').show();
                }
            },
            error: function(xhr) {
                console.error('Failed to load students:', xhr);
                
                swal({
                    title: "Failed to Load Students",
                    text: "Unable to fetch students. Please try again.",
                    type: "error",
                    confirmButtonText: "OK"
                });
            }
        });
    }

    function renderStudents() {
        let html = '';
        
        students.forEach(function(student, index) {
            const initials = student.first_name.charAt(0) + student.last_name.charAt(0);
            const avatarHtml = student.profile_photo 
                ? `<img src="/storage/${student.profile_photo}" alt="${student.first_name}" class="student-avatar">`
                : `<div class="student-avatar-initial">${initials}</div>`;

            // Get existing note for this student
            const existingNote = existingNotes.find(n => n.student_id === student.id);
            const performance = existingNote ? existingNote.performance : '';
            const notes = existingNote ? existingNote.notes : '';

            html += `
                <div class="student-item">
                    <div style="display: flex; gap: 15px;">
                        <div style="flex-shrink: 0;">
                            ${avatarHtml}
                        </div>
                        <div style="flex: 1;">
                            <h5 style="margin: 0 0 10px 0;">
                                ${student.first_name} ${student.last_name}
                            </h5>
                            
                            <input type="hidden" name="student_notes[${index}][student_id]" value="${student.id}">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Performance</label>
                                        <select name="student_notes[${index}][performance]" class="form-control form-control-sm">
                                            <option value="" ${performance === '' ? 'selected' : ''}>Not assessed</option>
                                            <option value="excellent" ${performance === 'excellent' ? 'selected' : ''}>✨ Excellent</option>
                                            <option value="good" ${performance === 'good' ? 'selected' : ''}>✓ Good</option>
                                            <option value="average" ${performance === 'average' ? 'selected' : ''}>~ Average</option>
                                            <option value="struggling" ${performance === 'struggling' ? 'selected' : ''}>⚠ Struggling</option>
                                            <option value="absent" ${performance === 'absent' ? 'selected' : ''}>✗ Absent</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <input 
                                            type="text"
                                            name="student_notes[${index}][notes]"
                                            value="${notes || ''}"
                                            maxlength="500"
                                            placeholder="Individual observations..."
                                            class="form-control form-control-sm"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#students-container').html(html);
    }

    // ==========================================
    // ✅ FORM SUBMISSION HANDLER (Prevent double submit)
    // ==========================================
    $('#progressSheetEditForm').on('submit', function(e) {
        const submitBtn = $('#updateBtn');
        
        // Prevent double submission
        if (submitBtn.prop('disabled')) {
            e.preventDefault();
            return false;
        }
        
        // Disable button and show loading
        submitBtn.prop('disabled', true)
            .html('<i class="ti-reload fa-spin"></i> Updating...');
    });

    // ==========================================
    // ✅ TOASTR NOTIFICATIONS (Flash Messages)
    // ==========================================
    @if(session('success'))
        toastr.success("{{ session('success') }}", "Success", {
            closeButton: true,
            progressBar: true,
            timeOut: 5000,
            positionClass: "toast-top-right"
        });
    @endif

    @if(session('error'))
        toastr.error("{{ session('error') }}", "Error", {
            closeButton: true,
            progressBar: true,
            timeOut: 8000,
            positionClass: "toast-top-right"
        });
    @endif

    @if(session('warning'))
        toastr.warning("{{ session('warning') }}", "Warning", {
            closeButton: true,
            progressBar: true,
            timeOut: 6000,
            positionClass: "toast-top-right"
        });
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            toastr.error("{{ $error }}", "Validation Error", {
                closeButton: true,
                progressBar: true,
                timeOut: 6000,
                positionClass: "toast-top-right"
            });
        @endforeach
    @endif
});

// ==========================================
// ✅ DELETE PROGRESS SHEET CONFIRMATION
// ==========================================
function confirmDeleteProgressSheet() {
    const studentNotesCount = {{ $progressSheet->progressNotes->count() }};
    const className = "{{ $progressSheet->class->name }}";
    const topic = "{{ $progressSheet->topic }}";
    const date = "{{ $progressSheet->date->format('d M Y') }}";
    
    let warningMessage = `Class: ${className}\nTopic: ${topic}\nDate: ${date}\n\n`;
    
    if (studentNotesCount > 0) {
        warningMessage += `⚠️ This progress sheet has ${studentNotesCount} student note(s).\n\n` +
                         `All student progress notes will be permanently deleted!\n\n` +
                         `This action cannot be undone!`;
    } else {
        warningMessage += `This progress sheet has no student notes yet.\n\n` +
                         `Are you sure you want to delete it?\n\n` +
                         `This action cannot be undone!`;
    }
    
    swal({
        title: "Delete Progress Sheet?",
        text: warningMessage,
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: studentNotesCount > 0 ? `Delete (${studentNotesCount} notes)` : "Yes, delete it!",
        cancelButtonText: "No, keep it",
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function(isConfirm) {
        if (isConfirm) {
            // Show loading state
            swal({
                title: "Deleting...",
                text: "Please wait while we delete the progress sheet and all related notes",
                type: "info",
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            // Submit the delete form
            setTimeout(function() {
                document.getElementById('delete-form').submit();
            }, 500);
        }
    });
}
</script>
@endpush
@extends('layouts.app')

@section('title', 'Create Progress Sheet')

@push('styles')
    <style>
        .guidelines-box {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .guidelines-box h4 {
            color: #0066cc;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .guidelines-box ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .guidelines-box li {
            color: #004080;
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
        }

        .guidelines-box li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #0066cc;
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
                                <h1>Create Progress Sheet</h1>
                            </div>
                        </div>
                        <span>Create a new progress sheet for a class session</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.progress-sheets.index') }}">Progress Sheets</a></li>
                                    <li class="active">Create</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.progress-sheets.store') }}" method="POST" id="progressSheetForm">
                    @csrf

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
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
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
                                            value="{{ old('date', now()->format('Y-m-d')) }}"
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
                                            value="{{ old('topic') }}"
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
                                        >{{ old('objective') }}</textarea>
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
                                        >{{ old('notes') }}</textarea>
                                        @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Student Notes -->
                            <div class="card alert" id="student-notes-card" style="display: none;">
                                <div class="card-header mb-3">
                                    <h4><i class="ti-user"></i> Student Progress Notes</h4>
                                </div>
                                <div class="card-body" id="students-container">
                                    <!-- Students will be loaded here via JavaScript -->
                                </div>
                            </div>

                            <!-- No Students Warning -->
                            <div class="alert alert-warning" id="no-students-warning" style="display: none;">
                                <i class="ti-info-alt"></i>
                                <strong>No students enroled</strong> - 
                                No students are enroled in this class. You can still create the progress sheet, but there won't be any student notes.
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <!-- Guidelines -->
                            <div class="guidelines-box">
                                <h4><i class="ti-light-bulb"></i> Guidelines</h4>
                                <ul>
                                    <li>Select the class and date before adding student notes</li>
                                    <li>Use clear, specific topics that describe what was taught</li>
                                    <li>Student notes are optional but highly recommended</li>
                                    <li>Parents will be notified when progress sheets are created</li>
                                    <li>Use performance levels to track student progress over time</li>
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
                                    <div class="text-right">
                                        <a href="{{ route('admin.progress-sheets.index') }}" class="btn btn-secondary">
                                            <i class="ti-close"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti-check"></i> Create Progress Sheet
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Footer -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer">
                            <p>MLC Classroom - Create Progress Sheet</p>
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

    // Load students when class is selected
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

        // Load students and schedules
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
                        $('#schedule_id').append(
                            `<option value="${schedule.id}">${schedule.day_of_week} - ${schedule.start_time} to ${schedule.end_time}</option>`
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
                } else {
                    $('#student-notes-card').hide();
                    $('#no-students-warning').show();
                }
            },
            error: function(xhr) {
                console.error('Failed to load students:', xhr);
                alert('Failed to load students. Please try again.');
            }
        });
    });

    function renderStudents() {
        let html = '';
        
        students.forEach(function(student, index) {
            const initials = student.first_name.charAt(0) + student.last_name.charAt(0);
            const avatarHtml = student.profile_photo 
                ? `<img src="/storage/${student.profile_photo}" alt="${student.first_name}" class="student-avatar">`
                : `<div class="student-avatar-initial">${initials}</div>`;

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
                                            <option value="">Not assessed</option>
                                            <option value="excellent">✨ Excellent</option>
                                            <option value="good">✓ Good</option>
                                            <option value="average">~ Average</option>
                                            <option value="struggling">⚠ Struggling</option>
                                            <option value="absent">✗ Absent</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <input 
                                            type="text"
                                            name="student_notes[${index}][notes]"
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
});
</script>
@endpush
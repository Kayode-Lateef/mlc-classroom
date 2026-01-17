@extends('layouts.app')

@section('title', 'Edit Attendance')

@push('styles')
    <style>
        .student-row {
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .student-row:hover {
            background-color: #f8f9fa;
        }

        .status-radio-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .status-radio-label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .status-radio-label:hover {
            background-color: #e9ecef;
        }

        .status-radio-label input[type="radio"] {
            cursor: pointer;
        }

        .session-info-card {
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .student-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .student-initial {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            background-color: #e7f3ff;
            color: #007bff;
        }

        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-section {
            margin-bottom: 5px;
        }

        .info-section h5 {
            margin-bottom: 5px;
            font-weight: 600;
            opacity: 0.9;
            font-size: 0.875rem;
        }

        .info-section h3 {
            margin: 0;
            font-weight: 700;
        }

        .info-section small {
            opacity: 0.8;
            font-size: 0.875rem;
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
                                <h1>Edit Attendance</h1>
                            </div>
                        </div>
                        <span class="text-muted">Modify existing attendance records for your class</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('teacher.attendance.index') }}">Attendance</a></li>
                                    <li class="active">Edit</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Session Info Card -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="session-info-card bg-primary">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="info-section">
                                            <h5><i class="ti-blackboard"></i> Class</h5>
                                            <h3>{{ $class->name }}</h3>
                                            <small>{{ $class->subject }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-section">
                                            <h5><i class="ti-calendar"></i> Date</h5>
                                            <h3>{{ $attendanceDate->format('d M Y') }}</h3>
                                            <small>{{ $attendanceDate->format('l') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-section">
                                            <h5><i class="ti-time"></i> Time</h5>
                                            <h3>
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                            </h3>
                                            <small>{{ $schedule->day_of_week }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-section">
                                            <h5><i class="ti-user"></i> Students</h5>
                                            <h3>{{ $attendanceRecords->count() }}</h3>
                                            <small>Active records</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warning Box -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="warning-box">
                                <div style="display: flex; align-items: start; gap: 10px;">
                                    <i class="ti-alert" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <strong>Editing Attendance Records</strong>
                                        <p class="mb-0">
                                            You are modifying existing attendance records. Changes will be reflected immediately in reports and statistics. 
                                            Parents of newly marked absent students will be notified automatically.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <form method="POST" action="{{ route('teacher.attendance.update', [$attendanceDate->format('Y-m-d'), $class->id, $schedule->id]) }}" id="attendanceEditForm">
                        @csrf
                        @method('PUT')

                        <!-- Student List -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card alert">
                                    <div class="card-header">
                                        <h4><i class="ti-pencil-alt"></i> Update Attendance Records</h4>
                                        <div class="card-header-right-icon">
                                            <span class="badge badge-info">{{ $attendanceRecords->count() }} records</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if($attendanceRecords->count() > 0)
                                            @foreach($attendanceRecords as $studentId => $record)
                                            @php
                                                $student = $record->student;
                                            @endphp
                                            <div class="student-row">
                                                <div class="row align-items-center">
                                                    <div class="col-md-4">
                                                        <div style="display: flex; align-items: center; gap: 15px;">
                                                            @if($student->profile_photo)
                                                                <img src="{{ asset('storage/' . $student->profile_photo) }}" 
                                                                     alt="{{ $student->full_name }}" 
                                                                     class="student-avatar">
                                                            @else
                                                                <div class="student-initial">
                                                                    {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <strong>{{ $student->full_name }}</strong><br>
                                                                <small class="text-muted">
                                                                    @if($student->parent)
                                                                        Parent: {{ $student->parent->name }}
                                                                    @else
                                                                        No parent assigned
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="status-radio-group">
                                                            <label class="status-radio-label">
                                                                <input type="radio" 
                                                                       name="attendance[{{ $studentId }}]" 
                                                                       value="present" 
                                                                       {{ $record->status === 'present' ? 'checked' : '' }}>
                                                                <i class="ti-check text-success"></i>
                                                                <span>Present</span>
                                                            </label>

                                                            <label class="status-radio-label">
                                                                <input type="radio" 
                                                                       name="attendance[{{ $studentId }}]" 
                                                                       value="absent" 
                                                                       {{ $record->status === 'absent' ? 'checked' : '' }}>
                                                                <i class="ti-close text-danger"></i>
                                                                <span>Absent</span>
                                                            </label>

                                                            <label class="status-radio-label">
                                                                <input type="radio" 
                                                                       name="attendance[{{ $studentId }}]" 
                                                                       value="late" 
                                                                       {{ $record->status === 'late' ? 'checked' : '' }}>
                                                                <i class="ti-time text-warning"></i>
                                                                <span>Late</span>
                                                            </label>

                                                            <label class="status-radio-label">
                                                                <input type="radio" 
                                                                       name="attendance[{{ $studentId }}]" 
                                                                       value="unauthorized" 
                                                                       {{ $record->status === 'unauthorized' ? 'checked' : '' }}>
                                                                <i class="ti-alert text-orange"></i>
                                                                <span>Unauthorized</span>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <input 
                                                            type="text" 
                                                            name="notes[{{ $studentId }}]" 
                                                            value="{{ $record->notes }}"
                                                            placeholder="Add notes (optional)" 
                                                            class="form-control form-control-sm"
                                                            maxlength="500"
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        @else
                                            <div class="text-center py-5">
                                                <i class="ti-alert" style="font-size: 3rem; color: #cbd5e0;"></i>
                                                <h4 class="mt-3">No Attendance Records Found</h4>
                                                <p class="text-muted">No attendance records exist for this session.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        @if($attendanceRecords->count() > 0)
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card alert">
                                    <div class="card-body">
                                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                            <div>
                                                <a href="{{ route('teacher.attendance.show', [$attendanceDate->format('Y-m-d'), $class->id, $schedule->id]) }}" 
                                                   class="btn btn-info">
                                                    <i class="ti-eye"></i> View Session Details
                                                </a>
                                                
                                                <button type="button" 
                                                        class="btn btn-danger" 
                                                        onclick="confirmDelete()">
                                                    <i class="ti-trash"></i> Delete Session
                                                </button>
                                            </div>

                                            <div>
                                                <a href="{{ route('teacher.attendance.index') }}" class="btn btn-secondary">
                                                    <i class="ti-close"></i> Cancel
                                                </a>
                                                <button type="submit" class="btn btn-success" id="submitBtn">
                                                    <i class="ti-check"></i> Update Attendance
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </form>

                    <!-- Hidden Delete Form -->
                    <form action="{{ route('teacher.attendance.destroy', [$attendanceDate->format('Y-m-d'), $class->id, $schedule->id]) }}" 
                          method="POST" 
                          id="deleteAttendanceSessionForm"
                          style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Edit Attendance</p>
                            </div>
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
    // Handle form submission - disable button to prevent double submit
    $('#attendanceEditForm').on('submit', function(e) {
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="ti-reload"></i> Updating...');
    });
});

// Confirm delete function
function confirmDelete() {
    const className = "{{ $class->name }}";
    const date = "{{ $attendanceDate->format('F j, Y') }}";
    const scheduleTime = "{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}";
    const studentCount = {{ $attendanceRecords->count() }};
    
    swal({
        title: "Delete Attendance Session?",
        text: "⚠️ You are about to delete this entire attendance session:\n\n" +
              "Class: " + className + "\n" +
              "Date: " + date + "\n" +
              "Time: " + scheduleTime + "\n" +
              "Records: " + studentCount + " student(s)\n\n" +
              "This action cannot be undone!\n\n" +
              "Are you sure you want to continue?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete session!",
        cancelButtonText: "No, cancel",
        closeOnConfirm: true,
        closeOnCancel: true
    },
    function(isConfirm) {
        if (isConfirm) {
            document.getElementById('deleteAttendanceSessionForm').submit();
        }
    });
}

// Track changes for unsaved warning
let formChanged = false;
$('input[type="radio"], input[type="text"]').on('change', function() {
    formChanged = true;
});

// Warn before leaving if unsaved changes
window.addEventListener('beforeunload', function (e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// Don't warn when actually submitting
$('#attendanceEditForm, #deleteAttendanceSessionForm').on('submit', function() {
    formChanged = false;
});
</script>
@endpush
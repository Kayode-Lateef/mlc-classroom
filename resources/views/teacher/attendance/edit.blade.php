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

        .session-info-card {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
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
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
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
                        <span class="text-muted">Modify existing attendance records</span>
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
                            <div class="session-info-card card">
                                <div class="row">
                                    <div class="col-md-3">
                                        <h5 style="margin-bottom: 5px;"><i class="ti-blackboard"></i> Class</h5>
                                        <h3 style="margin: 0;">{{ $class->name }}</h3>
                                        <small style="opacity: 0.9;">{{ $class->subject }}</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 style="margin-bottom: 5px;"><i class="ti-calendar"></i> Date</h5>
                                        <h3 style="margin: 0;">{{ $attendanceDate->format('d M Y') }}</h3>
                                        <small style="opacity: 0.9;">{{ $attendanceDate->format('l') }}</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 style="margin-bottom: 5px;"><i class="ti-time"></i> Time</h5>
                                        <h3 style="margin: 0;">
                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                        </h3>
                                        <small style="opacity: 0.9;">{{ $schedule->day_of_week }}</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 style="margin-bottom: 5px;"><i class="ti-user"></i> Teacher</h5>
                                        <h3 style="margin: 0;">{{ $class->teacher->name ?? 'Not assigned' }}</h3>
                                        <small style="opacity: 0.9;">Room: {{ $class->room_number ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warning Box -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="warning-box">
                                <i class="ti-alert"></i> <strong>Editing Attendance Records</strong>
                                <p class="mb-0">You are modifying existing attendance records. Changes will be reflected immediately in reports and statistics.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <form method="POST" action="{{ route('teacher.attendance.update', [$attendanceDate->format('Y-m-d'), $class->id, $schedule->id]) }}">
                        @csrf
                        @method('PUT')

                        <!-- Student List -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card alert">
                                    <div class="card-header">
                                        <h4><i class="ti-user"></i> Update Attendance ({{ $students->count() }} Students)</h4>
                                        <div class="card-header-right-icon">
                                            <span class="badge badge-info">{{ $attendanceRecords->count() }} records found</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @foreach($students as $student)
                                        @php
                                            $record = $attendanceRecords->get($student->id);
                                            $currentStatus = $record ? $record->status : 'present';
                                            $currentNotes = $record ? $record->notes : '';
                                        @endphp

                                        <div class="student-row">
                                            <div class="row align-items-center">
                                                <div class="col-md-4">
                                                    <div style="display: flex; align-items: center; gap: 15px;">
                                                        @if($student->profile_photo)
                                                            <img src="{{ asset('storage/' . $student->profile_photo) }}" alt="{{ $student->full_name }}" class="student-avatar">
                                                        @else
                                                            <div class="student-initial">
                                                                {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <strong>{{ $student->full_name }}</strong><br>
                                                            <small class="text-muted">ID: {{ $student->id }}</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-5">
                                                    <div class="status-radio-group">
                                                        <label class="status-radio-label">
                                                            <input type="radio" name="attendance[{{ $student->id }}]" value="present" {{ $currentStatus === 'present' ? 'checked' : '' }}>
                                                            <i class="ti-check text-success"></i>
                                                            <span>Present</span>
                                                        </label>

                                                        <label class="status-radio-label">
                                                            <input type="radio" name="attendance[{{ $student->id }}]" value="absent" {{ $currentStatus === 'absent' ? 'checked' : '' }}>
                                                            <i class="ti-close text-danger"></i>
                                                            <span>Absent</span>
                                                        </label>

                                                        <label class="status-radio-label">
                                                            <input type="radio" name="attendance[{{ $student->id }}]" value="late" {{ $currentStatus === 'late' ? 'checked' : '' }}>
                                                            <i class="ti-time text-warning"></i>
                                                            <span>Late</span>
                                                        </label>

                                                        <label class="status-radio-label">
                                                            <input type="radio" name="attendance[{{ $student->id }}]" value="unauthorized" {{ $currentStatus === 'unauthorized' ? 'checked' : '' }}>
                                                            <i class="ti-alert text-orange"></i>
                                                            <span style="font-size: 1.2rem">Unauthorized</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <input 
                                                        type="text" 
                                                        name="notes[{{ $student->id }}]" 
                                                        value="{{ $currentNotes }}"
                                                        placeholder="Add notes (optional)" 
                                                        class="form-control form-control-sm"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach

                                        @if($students->count() === 0)
                                        <div class="text-center py-5">
                                            <i class="ti-user" style="font-size: 3rem; color: #cbd5e0;"></i>
                                            <h4 class="mt-3">No Students Enrolled</h4>
                                            <p class="text-muted">This class doesn't have any active students.</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <a href="{{ route('teacher.attendance.show', [$attendanceDate->format('Y-m-d'), $class->id, $schedule->id]) }}" class="btn btn-info">
                                            <i class="ti-eye"></i> View Session
                                        </a>
                                        
                                    </div>

                                    <div>
                                        <a href="{{ route('teacher.attendance.index') }}" class="btn btn-secondary">
                                            <i class="ti-close"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="ti-check"></i> Update Attendance
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form action="{{ route('teacher.attendance.destroy', [$attendanceDate->format('Y-m-d'), $class->id, $schedule->id]) }}" 
                        method="POST" 
                        style="display: inline-block; margin-top: 8px;"
                        id="deleteAttendanceSessionForm">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="ti-trash"></i> Delete Session
                        </button>
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
    // Handle attendance session deletion with SweetAlert
    $('#deleteAttendanceSessionForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default submission
        
        const form = this;
        const className = "{{ $class->name }}";
        const date = "{{ $attendanceDate->format('F j, Y') }}"; // e.g., "January 15, 2026"
        const scheduleTime = "{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}";
        const studentCount = {{ $attendanceRecords->count() ?? 0 }};
        
        swal({
            title: "Delete Attendance Session?",
            text: "⚠️ You are about to delete this entire attendance session:\n\nClass: " + className + "\nDate: " + date + "\nTime: " + scheduleTime + "\nRecords: " + studentCount + " student(s)\n\nThis action cannot be undone!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete session!",
            cancelButtonText: "No, cancel",
            closeOnConfirm: true,
            closeOnCancel: true
        },
        function(isConfirm){
            if (isConfirm) {
                form.submit(); // Submit the form
            }
        });
        
        return false;
    });
});
</script>
@endpush
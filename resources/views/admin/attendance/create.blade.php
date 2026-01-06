@extends('layouts.app')

@section('title', 'Mark Attendance')

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

        .status-radio-label input[type="radio"] {
            cursor: pointer;
        }

        .bulk-action-bar {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .selection-info {
            background-color: #fff;
            padding: 20px;
            border-radius: 6px;
            border: 2px solid #007bff;
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
                                <h1>Mark Attendance</h1>
                            </div>
                        </div>
                        <span class="text-muted">Record student attendance for a class session</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('admin.attendance.index') }}">Attendance</a></li>
                                    <li class="active">Mark</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Class Selection Form -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-search"></i> Select Class & Date</h4>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="{{ route('admin.attendance.create') }}">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="required-field">Class</label>
                                                    <select name="class_id" class="form-control" required onchange="this.form.submit()">
                                                        <option value="">Select a class...</option>
                                                        @foreach($classes as $class)
                                                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                            {{ $class->name }} - {{ $class->teacher->name ?? 'No Teacher' }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="required-field">Date</label>
                                                    <input 
                                                        type="date" 
                                                        name="date" 
                                                        value="{{ $selectedDate }}" 
                                                        max="{{ now()->format('Y-m-d') }}"
                                                        class="form-control"
                                                        required
                                                        onchange="this.form.submit()"
                                                    >
                                                </div>
                                            </div>

                                            @if($schedules->count() > 0)
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="required-field">Schedule</label>
                                                    <select name="schedule_id" class="form-control" required onchange="this.form.submit()">
                                                        <option value="">Select schedule...</option>
                                                        @foreach($schedules as $schedule)
                                                        <option value="{{ $schedule->id }}" {{ request('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - 
                                                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="submit" class="btn btn-primary btn-block">
                                                        <i class="ti-check"></i> Load
                                                    </button>
                                                </div>
                                            </div>
                                            @endif
                                        </div>

                                        @if($selectedClass && $schedules->count() === 0)
                                        <div class="alert alert-warning">
                                            <i class="ti-alert"></i> No schedules found for {{ $selectedClass->name }} on {{ \Carbon\Carbon::parse($selectedDate)->format('l, d M Y') }}.
                                            <br>
                                            <small>Please select a different date or <a href="{{ route('admin.schedules.create') }}">create a schedule</a> for this class.</small>
                                        </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($existingAttendance)
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="alert alert-info">
                                    <i class="ti-info-alt"></i> <strong>Attendance Already Marked</strong>
                                    <p class="mb-0">Attendance has already been marked for this session. You can <a href="{{ route('admin.attendance.edit', [$selectedDate, $selectedClass->id, $selectedSchedule->id]) }}">edit the existing records</a> or <a href="{{ route('admin.attendance.show', [$selectedDate, $selectedClass->id, $selectedSchedule->id]) }}">view the session</a>.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($selectedClass && $selectedSchedule && $students->count() > 0 && !$existingAttendance)
                    <!-- Selected Session Info -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="selection-info">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Class:</strong><br>
                                        {{ $selectedClass->name }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Date:</strong><br>
                                        {{ \Carbon\Carbon::parse($selectedDate)->format('l, d M Y') }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Time:</strong><br>
                                        {{ \Carbon\Carbon::parse($selectedSchedule->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($selectedSchedule->end_time)->format('H:i') }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Students:</strong><br>
                                        {{ $students->count() }} enrolled
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Form -->
                    <form method="POST" action="{{ route('admin.attendance.store') }}">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                        <input type="hidden" name="schedule_id" value="{{ $selectedSchedule->id }}">
                        <input type="hidden" name="date" value="{{ $selectedDate }}">

                        <!-- Bulk Actions -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="bulk-action-bar">
                                    <div>
                                        <strong>Bulk Mark All Students:</strong>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-success" onclick="markAll('present')">
                                            <i class="ti-check"></i> All Present
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="markAll('absent')">
                                            <i class="ti-close"></i> All Absent
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="markAll('late')">
                                            <i class="ti-time"></i> All Late
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Student List -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card alert">
                                    <div class="card-header">
                                        <h4><i class="ti-user"></i> Mark Attendance ({{ $students->count() }} Students)</h4>
                                    </div>
                                    <div class="card-body">
                                        @foreach($students as $student)
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
                                                            <input type="radio" name="attendance[{{ $student->id }}]" value="present" checked>
                                                            <i class="ti-check text-success"></i>
                                                            <span>Present</span>
                                                        </label>

                                                        <label class="status-radio-label">
                                                            <input type="radio" name="attendance[{{ $student->id }}]" value="absent">
                                                            <i class="ti-close text-danger"></i>
                                                            <span>Absent</span>
                                                        </label>

                                                        <label class="status-radio-label">
                                                            <input type="radio" name="attendance[{{ $student->id }}]" value="late">
                                                            <i class="ti-time text-warning"></i>
                                                            <span>Late</span>
                                                        </label>

                                                        <label class="status-radio-label">
                                                            <input type="radio" name="attendance[{{ $student->id }}]" value="unauthorized">
                                                            <i class="ti-alert text-orange"></i>
                                                            <span style="font-size: 1.2rem">Unauthorized</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <input 
                                                        type="text" 
                                                        name="notes[{{ $student->id }}]" 
                                                        placeholder="Add notes (optional)" 
                                                        class="form-control form-control-sm"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="text-right">
                                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">
                                        <i class="ti-close"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="ti-check"></i> Save Attendance
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    @endif

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Mark Attendance</p>
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
    function markAll(status) {
        document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(function(radio) {
            radio.checked = true;
        });
    }
</script>
@endpush
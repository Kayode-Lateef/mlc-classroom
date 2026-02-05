@extends('layouts.app')

@section('title', $class->name)

@push('styles')
    <style>
        .info-item {
            margin-bottom: 20px;
        }

        .info-label {
            color: #6c757d;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .info-value {
            color: #212529;
            font-weight: 500;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .student-avatar-initial {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1rem;
            background-color: #007bff;
            color: white;
        }

        .schedule-day-card {
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .homework-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .homework-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .progress-sheet-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .attendance-indicator {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
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
                                <h1>{{ $class->name }}</h1>
                            </div>
                        </div>
                        <span>{{ $class->subject }} @if($class->level) - {{ $class->level }} @endif</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('teacher.classes.index') }}">My Classes</a></li>
                                    <li class="active">{{ $class->name }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="alert alert-success alert-dismissable">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class="ti-check"></i> {{ session('success') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="alert alert-danger alert-dismissable">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class="ti-alert"></i> {{ session('error') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Class Information Card -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-info-alt"></i> Class Information</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-item">
                                                <div class="info-label">Subject</div>
                                                <div class="info-value">{{ $class->subject }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="info-item">
                                                <div class="info-label">Level</div>
                                                <div class="info-value">{{ $class->level ?? 'N/A' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="info-item">
                                                <div class="info-label">Room Number</div>
                                                <div class="info-value">{{ $class->room_number ?? 'N/A' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="info-item">
                                                <div class="info-label">Capacity</div>
                                                <div class="info-value">
                                                    {{ $stats['students']['total_enrolled'] }} / {{ $class->capacity }}
                                                    <span class="badge badge-{{ $stats['students']['utilization_rate'] >= 90 ? 'danger' : ($stats['students']['utilization_rate'] >= 75 ? 'warning' : 'success') }}">
                                                        {{ $stats['students']['utilization_rate'] }}%
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        @if($class->description)
                                        <div class="col-md-12">
                                            <div class="info-item">
                                                <div class="info-label">Description</div>
                                                <div class="info-value">{{ $class->description }}</div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-user color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Enroled Students</div>
                                        <div class="stat-digit">{{ $stats['students']['total_enrolled'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-check color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Attendance Rate (30d)</div>
                                        <div class="stat-digit">{{ $stats['attendance']['rate'] }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-write color-warning border-warning"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Pending Grading</div>
                                        <div class="stat-digit">{{ $stats['homework']['pending_grading'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-clipboard color-info border-info"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Progress Sheets</div>
                                        <div class="stat-digit">{{ $stats['progress']['total'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Attendance Alert -->
                    @if(!$stats['today_attendance']['marked'] && $class->students->count() > 0)
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="alert alert-warning">
                                <i class="ti-alert"></i> 
                                <strong>Attendance Not Marked Today</strong> - 
                                <a href="{{ route('teacher.attendance.create', ['class_id' => $class->id]) }}" class="btn btn-sm btn-primary">
                                    Mark Attendance Now
                                </a>
                            </div>
                        </div>
                    </div>
                    @elseif($stats['today_attendance']['marked'])
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="alert alert-success">
                                <i class="ti-check"></i> 
                                <strong>Today's Attendance Marked</strong> - 
                                Present: {{ $stats['today_attendance']['present'] }}, 
                                Absent: {{ $stats['today_attendance']['absent'] }}, 
                                Late: {{ $stats['today_attendance']['late'] }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Tabs -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="custom-tab">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li role="presentation" class="active">
                                                <a href="#students" aria-controls="students" role="tab" data-toggle="tab">
                                                    <i class="ti-user"></i> Students ({{ $stats['students']['total_enrolled'] }})
                                                </a>
                                            </li>
                                            <li role="presentation">
                                                <a href="#schedules" aria-controls="schedules" role="tab" data-toggle="tab">
                                                    <i class="ti-calendar"></i> Class Schedule
                                                </a>
                                            </li>
                                            <li role="presentation">
                                                <a href="#homework" aria-controls="homework" role="tab" data-toggle="tab">
                                                    <i class="ti-write"></i> Homework ({{ $stats['homework']['total'] }})
                                                </a>
                                            </li>
                                            <li role="presentation">
                                                <a href="#progress" aria-controls="progress" role="tab" data-toggle="tab">
                                                    <i class="ti-stats-up"></i> Progress Sheets
                                                </a>
                                            </li>
                                        </ul>

                                        <div class="tab-content">
                                            <!-- Students Tab -->
                                            <div role="tabpanel" class="tab-pane active" id="students">
                                                @if($class->students->count() > 0)
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Student</th>
                                                                    <th>Parent</th>
                                                                    <th>Attendance Rate (30d)</th>
                                                                    <th>Homework Completion</th>
                                                                    <th>Average Grade</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($class->students as $student)
                                                                @php
                                                                    $studentStat = $studentStats[$student->id] ?? [
                                                                        'attendance_rate' => 0,
                                                                        'homework_completion' => 0,
                                                                        'average_grade' => 0
                                                                    ];
                                                                @endphp
                                                                <tr>
                                                                    <td>
                                                                        <div style="display: flex; align-items: center;">
                                                                            @if($student->profile_photo)
                                                                                <img src="{{ asset('storage/' . $student->profile_photo) }}" 
                                                                                     alt="{{ $student->full_name }}" 
                                                                                     class="student-avatar" 
                                                                                     style="margin-right: 10px;">
                                                                            @else
                                                                                <div class="student-avatar-initial" style="margin-right: 10px;">
                                                                                    {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                                                                </div>
                                                                            @endif
                                                                            <div>
                                                                                <strong>{{ $student->full_name }}</strong><br>
                                                                                <small class="text-muted">Enroled: {{ \Carbon\Carbon::parse($student->pivot->enrollment_date)->format('d M Y') }}</small>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        {{ $student->parent->name ?? 'N/A' }}<br>
                                                                        @if($student->parent && $student->parent->email)
                                                                        <small class="text-muted">{{ $student->parent->email }}</small>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge badge-{{ $studentStat['attendance_rate'] >= 90 ? 'success' : ($studentStat['attendance_rate'] >= 75 ? 'warning' : 'danger') }}">
                                                                            {{ $studentStat['attendance_rate'] }}%
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge badge-{{ $studentStat['homework_completion'] >= 90 ? 'success' : ($studentStat['homework_completion'] >= 75 ? 'warning' : 'danger') }}">
                                                                            {{ $studentStat['homework_completion'] }}%
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        @if($studentStat['average_grade'] > 0)
                                                                            <strong class="text-{{ $studentStat['average_grade'] >= 80 ? 'success' : ($studentStat['average_grade'] >= 60 ? 'warning' : 'danger') }}">
                                                                                {{ $studentStat['average_grade'] }}
                                                                            </strong>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <div class="btn-group">
                                                                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown">
                                                                                <i class="ti-menu"></i> Actions
                                                                            </button>
                                                                            <ul class="dropdown-menu" role="menu">
                                                                               
                                                                                <li>
                                                                                    <a href="{{ route('teacher.attendance.create', ['class_id' => $class->id, 'student_id' => $student->id]) }}">
                                                                                        <i class="ti-check-box"></i> Mark Attendance
                                                                                    </a>
                                                                                </li>
                                                                                <li>
                                                                                    <a href="{{ route('teacher.progress-sheets.create', ['class_id' => $class->id, 'student_id' => $student->id]) }}">
                                                                                        <i class="ti-clipboard"></i> Add Progress Note
                                                                                    </a>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-user"></i>
                                                        <h4>No Students Enroled</h4>
                                                        <p>This class doesn't have any enroled students yet.</p>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Schedules Tab -->
                                            <div role="tabpanel" class="tab-pane" id="schedules">
                                                @if(count($schedulesByDay) > 0)
                                                    @foreach($schedulesByDay as $day => $daySchedules)
                                                    <div class="schedule-day-card">
                                                        <h5><i class="ti-calendar"></i> {{ $day }}</h5>
                                                        @foreach($daySchedules as $schedule)
                                                        <div style="padding: 10px 0; border-bottom: 1px solid #e9ecef;">
                                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                                <div>
                                                                    <strong>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</strong>
                                                                    @if($schedule->recurring)
                                                                        <span class="badge badge-info ml-2">Recurring</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @endforeach
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-calendar"></i>
                                                        <h4>No Schedules Set</h4>
                                                        <p>No class schedules have been created yet.</p>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Homework Tab -->
                                            <div role="tabpanel" class="tab-pane" id="homework">
                                                <div class="mb-3">
                                                    <a href="{{ route('teacher.homework.create', ['class_id' => $class->id]) }}" class="btn btn-primary">
                                                        <i class="ti-plus"></i> Create New Homework
                                                    </a>
                                                </div>

                                                @if($recentHomework->count() > 0)
                                                    <h5 class="mb-3">Recent Homework Assignments</h5>
                                                    @foreach($recentHomework as $homework)
                                                    <div class="homework-card">
                                                        <div class="row">
                                                            <div class="col-md-8">
                                                                <h5 style="margin: 0 0 10px 0;">
                                                                    <a href="{{ route('teacher.homework.show', $homework) }}">
                                                                        {{ $homework->title }}
                                                                    </a>
                                                                </h5>
                                                                @if($homework->description)
                                                                <p style="margin: 0 0 10px 0; color: #6c757d;">
                                                                    {{ Str::limit($homework->description, 100) }}
                                                                </p>
                                                                @endif
                                                                <div style="display: flex; gap: 15px; font-size: 0.875rem; color: #6c757d;">
                                                                    <span>
                                                                        <i class="ti-calendar"></i> Due: {{ $homework->due_date->format('d M Y') }}
                                                                    </span>
                                                                    <span>
                                                                        <i class="ti-user"></i> {{ $homework->submissions->count() }} submissions
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 text-right">
                                                                @if($homework->due_date >= now())
                                                                    <span class="badge badge-success">Active</span>
                                                                @else
                                                                    <span class="badge badge-secondary">Overdue</span>
                                                                @endif
                                                                <br><br>
                                                                <a href="{{ route('teacher.homework.show', $homework) }}" class="btn btn-sm btn-primary">
                                                                    <i class="ti-eye"></i> View
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach

                                                    <div class="text-center mt-3">
                                                        <a href="{{ route('teacher.homework.index', ['class_id' => $class->id]) }}" class="btn btn-secondary">
                                                            View All Homework
                                                        </a>
                                                    </div>
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-write"></i>
                                                        <h4>No Homework Assigned</h4>
                                                        <p>You haven't created any homework for this class yet.</p>
                                                        <a href="{{ route('teacher.homework.create', ['class_id' => $class->id]) }}" class="btn btn-primary">
                                                            <i class="ti-plus"></i> Create First Homework
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Progress Sheets Tab -->
                                            <div role="tabpanel" class="tab-pane" id="progress">
                                                <div class="mb-3">
                                                    <a href="{{ route('teacher.progress-sheets.create', ['class_id' => $class->id]) }}" class="btn btn-primary">
                                                        <i class="ti-plus"></i> Create Progress Sheet
                                                    </a>
                                                </div>

                                                @if($progressSheets->count() > 0)
                                                    <h5 class="mb-3">Recent Progress Sheets</h5>
                                                    @foreach($progressSheets as $sheet)
                                                    <div class="progress-sheet-card">
                                                        <div class="row">
                                                            <div class="col-md-8">
                                                                <h5 style="margin: 0 0 10px 0;">
                                                                    <a href="{{ route('teacher.progress-sheets.show', $sheet) }}">
                                                                        {{ $sheet->topic }}
                                                                    </a>
                                                                </h5>
                                                                <div style="display: flex; gap: 15px; font-size: 0.875rem; color: #6c757d;">
                                                                    <span>
                                                                        <i class="ti-calendar"></i> {{ $sheet->date->format('d M Y') }}
                                                                    </span>
                                                                    <span>
                                                                        <i class="ti-user"></i> {{ $sheet->progressNotes->count() }} student notes
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 text-right">
                                                                <a href="{{ route('teacher.progress-sheets.show', $sheet) }}" class="btn btn-sm btn-primary">
                                                                    <i class="ti-eye"></i> View
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach

                                                    <div class="text-center mt-3">
                                                        <a href="{{ route('teacher.progress-sheets.index', ['class_id' => $class->id]) }}" class="btn btn-secondary">
                                                            View All Progress Sheets
                                                        </a>
                                                    </div>
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-clipboard"></i>
                                                        <h4>No Progress Sheets</h4>
                                                        <p>You haven't created any progress sheets for this class yet.</p>
                                                        <a href="{{ route('teacher.progress-sheets.create', ['class_id' => $class->id]) }}" class="btn btn-primary">
                                                            <i class="ti-plus"></i> Create First Progress Sheet
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Class Details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
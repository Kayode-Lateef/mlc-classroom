@extends('layouts.app')

@section('title', 'Schedule Details')

@push('styles')
    <style>
        .schedule-header {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;

        }

        .info-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
        }

        .info-value {
            color: #212529;
            font-weight: 500;
        }

        .schedule-card {
            border-left: 4px solid #007bff;
            transition: all 0.3s ease;
        }

        .schedule-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .time-badge {
            background-color: #e7f3ff;
            color: #007bff;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }

        .recurring-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #28a745;
            display: inline-block;
            margin-right: 5px;
        }

        .attendance-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .attendance-item {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s;
        }

        .attendance-item:hover {
            background-color: #f8f9fa;
        }

        .status-present {
            color: #28a745;
        }

        .status-absent {
            color: #dc3545;
        }

        .status-late {
            color: #ffc107;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 15px;
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
                                <h1>Schedule Details</h1>
                            </div>
                        </div>
                        <span>View complete schedule information</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.schedules.index') }}">Schedules</a></li>
                                    <li class="active">{{ $schedule->class->name }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mb-3 text-right">
                                <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary">
                                    <i class="ti-pencil"></i> Edit Schedule
                                </a>
                                @if($schedule->attendance()->count() === 0)
                                <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="ti-trash"></i> Delete Schedule
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Header Card -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="schedule-header">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h2 class="mb-2">
                                            {{ $schedule->class->name }}
                                            @if($schedule->recurring)
                                            <span class="recurring-indicator" title="Recurring"></span>
                                            @endif
                                        </h2>
                                        <p class="mb-2">
                                            <i class="ti-book"></i> {{ $schedule->class->subject }}
                                            @if($schedule->class->level)
                                            | <i class="ti-bookmark"></i> {{ $schedule->class->level }}
                                            @endif
                                        </p>
                                        @if($schedule->class->teacher)
                                        <p class="mb-0">
                                            <i class="ti-user"></i> {{ $schedule->class->teacher->name }}
                                        </p>
                                        @endif
                                    </div>
                                    <div class="col-md-4 text-md-right">
                                        <div class="time-badge" style="background-color: rgba(255,255,255,0.2);">
                                            <i class="ti-calendar"></i> {{ $schedule->day_of_week }}
                                        </div>
                                        <div class="time-badge mt-2" style="background-color: rgba(255,255,255,0.2);">
                                            <i class="ti-time"></i> {{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Left Column: Schedule Information -->
                        <div class="col-lg-8">
                            <!-- Basic Information Card -->
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-info-alt"></i> Schedule Information</h4>
                                </div>
                                <div class="card-body">
                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-calendar"></i> Day of Week
                                        </span>
                                        <span class="info-value">
                                            <span class="badge badge-primary">{{ $schedule->day_of_week }}</span>
                                        </span>
                                    </div>

                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-time"></i> Start Time
                                        </span>
                                        <span class="info-value">{{ $schedule->start_time->format('H:i') }}</span>
                                    </div>

                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-time"></i> End Time
                                        </span>
                                        <span class="info-value">{{ $schedule->end_time->format('H:i') }}</span>
                                    </div>

                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-arrow-circle-right"></i> Duration
                                        </span>
                                        <span class="info-value">
                                            {{ $schedule->start_time->diffInMinutes($schedule->end_time) }} minutes
                                        </span>
                                    </div>

                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-reload"></i> Recurring
                                        </span>
                                        <span class="info-value">
                                            @if($schedule->recurring)
                                                <span class="badge badge-success">Yes - Repeats Weekly</span>
                                            @else
                                                <span class="badge badge-secondary">No - One Time</span>
                                            @endif
                                        </span>
                                    </div>

                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-calendar"></i> Created
                                        </span>
                                        <span class="info-value">{{ $schedule->created_at->format('d M Y, H:i') }}</span>
                                    </div>

                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-pencil"></i> Last Updated
                                        </span>
                                        <span class="info-value">{{ $schedule->updated_at->format('d M Y, H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Class Details Card -->
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-blackboard"></i> Class Details</h4>
                                    <div class="card-header-right-icon">
                                        <a href="{{ route('admin.classes.show', $schedule->class) }}" class="btn btn-sm btn-primary">
                                            <i class="ti-eye"></i> View Class
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-blackboard"></i> Class Name
                                        </span>
                                        <span class="info-value">{{ $schedule->class->name }}</span>
                                    </div>

                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-book"></i> Subject
                                        </span>
                                        <span class="info-value">{{ $schedule->class->subject }}</span>
                                    </div>

                                    @if($schedule->class->level)
                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-bookmark"></i> Level
                                        </span>
                                        <span class="info-value">{{ $schedule->class->level }}</span>
                                    </div>
                                    @endif

                                    @if($schedule->class->teacher)
                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-user"></i> Teacher
                                        </span>
                                        <span class="info-value">{{ $schedule->class->teacher->name }}</span>
                                    </div>
                                    @endif

                                    @if($schedule->class->room_number)
                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-home"></i> Room Number
                                        </span>
                                        <span class="info-value">{{ $schedule->class->room_number }}</span>
                                    </div>
                                    @endif

                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-user"></i> Enrolled Students
                                        </span>
                                        <span class="info-value">
                                            <span class="badge badge-info">
                                                {{ $schedule->class->enrollments()->where('status', 'active')->count() }}
                                            </span>
                                        </span>
                                    </div>

                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="ti-layout-grid2"></i> Capacity
                                        </span>
                                        <span class="info-value">{{ $schedule->class->capacity }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Other Schedules for this Class -->
                            @if($classSchedules->count() > 0)
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-calendar"></i> Other Schedules for {{ $schedule->class->name }}</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Day</th>
                                                    <th>Time</th>
                                                    <th>Duration</th>
                                                    <th>Recurring</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($classSchedules as $otherSchedule)
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-primary">{{ $otherSchedule->day_of_week }}</span>
                                                    </td>
                                                    <td>{{ $otherSchedule->start_time->format('H:i') }} - {{ $otherSchedule->end_time->format('H:i') }}</td>
                                                    <td>{{ $otherSchedule->start_time->diffInMinutes($otherSchedule->end_time) }} min</td>
                                                    <td>
                                                        @if($otherSchedule->recurring)
                                                            <span class="badge badge-success">Yes</span>
                                                        @else
                                                            <span class="badge badge-secondary">No</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.schedules.show', $otherSchedule) }}" class="btn btn-sm btn-info">
                                                            <i class="ti-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>


                        <div class="col-lg-4">

                             
                        </div>



                        <!-- Right Column: Statistics & Activity -->
                        <div class="col-lg-4">
                            <!-- Statistics Card -->
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-stats-up"></i> Statistics</h4>
                                </div>
                                <div class="card mb-3 border-bottom">
                                    <div class="stat-widget-one">
                                        <div class="" style="display: flex; justify-content: center; align-items: center;">
                                            <div class="stat-icon color-success border-success">
                                                <i class="ti-check-box"></i>
                                            </div>
                                            <div style="margin-left: 15px; flex: 1;">
                                                <div class="stat-text">Attendance Sessions</div>
                                                <div class="stat-digit">{{ $attendanceStats['total_sessions'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3 border-bottom">
                                    <div class="stat-widget-one">
                                        <div class="" style="display: flex; justify-content: center; align-items: center;">
                                            <div class="stat-icon color-primary border-primary">
                                                <i class="ti-bar-chart"></i>
                                            </div>
                                            <div style="margin-left: 15px; flex: 1;">
                                                <div class="stat-text">Avg Attendance</div>
                                                <div class="stat-digit">{{ $attendanceStats['average_attendance'] }}%</div>
                                            </div>
                                        </div>
                                    </div>      
                                </div>

                                <div class="card mb-3 border-bottom">
                                    <div class="stat-widget-one">
                                        <div class="" style="display: flex; justify-content: center; align-items: center;">
                                            <div class="stat-icon color-info border-info">
                                                <i class="ti-file"></i>
                                            </div>
                                            <div style="margin-left: 15px; flex: 1;">
                                                <div class="stat-text">Progress Sheets</div>
                                                <div class="stat-digit">{{ $schedule->progressSheets()->count() }}</div>
                                            </div>
                                        </div>
                                    </div>      
                                </div>

                                <div class="card mb-3">
                                    <div class="stat-widget-one">
                                        <div class="" style="display: flex; justify-content: center; align-items: center;">
                                            <div class="stat-icon color-warning border-warning">
                                                <i class="ti-time"></i>
                                            </div>
                                            <div style="margin-left: 15px; flex: 1;">
                                                <div class="stat-text">Total Duration</div>
                                                <div class="stat-digit">{{ $schedule->start_time->diffInMinutes($schedule->end_time) }} min</div>
                                            </div>
                                        </div>
                                    </div>      
                                </div>

                            </div>

                            <!-- Quick Actions Card -->
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <h4><i class="ti-settings"></i> Quick Actions</h4>
                                </div>
                                <div class="card-body">
                                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary btn-block mb-2">
                                        <i class="ti-pencil"></i> Edit Schedule
                                    </a>
                                    <a href="{{ route('admin.classes.show', $schedule->class) }}" class="btn btn-info btn-block mb-2">
                                        <i class="ti-blackboard"></i> View Class
                                    </a>
                                    <a href="{{ route('admin.schedules.index') }}" class="btn btn-pink btn-block mb-2">
                                        <i class="ti-calendar"></i> All Schedules
                                    </a>
                                    @if($schedule->attendance()->count() === 0)
                                    <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-block">
                                            <i class="ti-trash"></i> Delete Schedule
                                        </button>
                                    </form>
                                    @else
                                    <button type="button" class="btn btn-secondary disabled btn-block" disabled title="Cannot delete schedule with attendance records">
                                        <i class="ti-lock"></i> Delete Disabled
                                    </button>
                                    <small class="text-muted d-block mt-2 text-center">
                                        Has {{ $schedule->attendance()->count() }} attendance record(s)
                                    </small>
                                    @endif
                                </div>
                            </div>

                            <!-- Recent Attendance Card -->
                            @if($schedule->attendance()->count() > 0)
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-clipboard"></i> Recent Attendance</h4>
                                </div>
                                <div class="card-body p-0">
                                    <div class="attendance-list">
                                        @foreach($schedule->attendance()->orderBy('date', 'desc')->limit(10)->get() as $attendance)
                                        <div class="attendance-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $attendance->student->full_name }}</strong><br>
                                                    <small class="text-muted">
                                                        <i class="ti-calendar"></i> {{ $attendance->date->format('d M Y') }}
                                                    </small>
                                                </div>
                                                <div>
                                                    @if($attendance->status === 'present')
                                                        <span class="badge badge-success">Present</span>
                                                    @elseif($attendance->status === 'absent')
                                                        <span class="badge badge-danger">Absent</span>
                                                    @elseif($attendance->status === 'late')
                                                        <span class="badge badge-warning">Late</span>
                                                    @else
                                                        <span class="badge badge-secondary">{{ ucfirst($attendance->status) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Schedule Details</p>
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
            // Smooth scroll for long lists
            $('.attendance-list').css('scroll-behavior', 'smooth');
        });
    </script>
@endpush
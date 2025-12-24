@extends('layouts.app')

@section('title', 'Daily Attendance Dashboard')

@push('styles')
    <style>
        .date-selector-card {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .schedule-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .schedule-card:hover {
            box-shadow: 0 3px 10px rgba(0, 123, 255, 0.1);
        }

        .schedule-card.marked {
            background-color: #f0f9ff;
            border-color: #28a745;
        }

        .schedule-card.pending {
            background-color: #fff8e1;
        }

        .schedule-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .time-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            background-color: #e7f3ff;
            border-radius: 4px;
            font-weight: 600;
            color: #007bff;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1rem;
        }

        .status-indicator.complete {
            background-color: #d4edda;
            color: #155724;
        }

        .status-indicator.pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .quick-stats {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .quick-stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .empty-schedule {
            text-align: center;
            padding: 60px 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
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
                                <h1>Daily Attendance Dashboard</h1>
                            </div>
                        </div>
                        <span class="text-muted">Quick access to mark attendance for today's classes</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('superadmin.attendance.index') }}">Attendance</a></li>
                                    <li class="active">Daily</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Date Selector -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="date-selector-card card">
                                <form method="GET" action="{{ route('superadmin.attendance.daily') }}">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <h3 style="margin: 0;">
                                                <i class="ti-calendar"></i> {{ \Carbon\Carbon::parse($selectedDate)->format('l, d F Y') }}
                                            </h3>
                                        </div>
                                        <div class="col-md-4">
                                            <input 
                                                type="date" 
                                                name="date" 
                                                value="{{ $selectedDate }}" 
                                                max="{{ now()->format('Y-m-d') }}"
                                                class="form-control"
                                                onchange="this.form.submit()"
                                            >
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <a href="{{ route('superadmin.attendance.daily') }}" class="btn btn-light">
                                                <i class="ti-reload"></i> Today
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Statistics -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-book color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Sessions</div>
                                        <div class="stat-digit">{{ $stats['total_sessions'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-check color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Marked</div>
                                        <div class="stat-digit">{{ $stats['marked_sessions'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-time color-warning border-warning"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Pending</div>
                                        <div class="stat-digit">{{ $stats['pending_sessions'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-stats-up color-info border-info"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Attendance Rate</div>
                                        <div class="stat-digit">{{ $stats['attendance_summary']['rate'] }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                      
                    </div> 

                    <!-- Today's Attendance Summary -->
                    @if($stats['attendance_summary']['total'] > 0)
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Total Marked:</strong> {{ $stats['attendance_summary']['total'] }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong class="text-success">Present:</strong> {{ $stats['attendance_summary']['present'] }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong class="text-danger">Absent:</strong> {{ $stats['attendance_summary']['absent'] }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong class="text-warning">Late:</strong> {{ $stats['attendance_summary']['late'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Schedule List -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-list"></i> Scheduled Classes for {{ $dayOfWeek }}</h4>
                                    <div class="card-header-right-icon">
                                        <span class="badge badge-primary">{{ $schedulesWithStatus->count() }} classes</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($schedulesWithStatus->count() > 0)
                                        @foreach($schedulesWithStatus as $schedule)
                                        <div class="schedule-card {{ $schedule->is_complete ? 'marked' : 'pending' }}">
                                            <div class="schedule-header">
                                                <div>
                                                    <h4 style="margin: 0 0 5px;">{{ $schedule->class->name }}</h4>
                                                    <small class="text-muted">
                                                        <i class="ti-user"></i> {{ $schedule->class->teacher->name ?? 'No teacher' }}
                                                        &nbsp;|&nbsp;
                                                        <i class="ti-home"></i> Room: {{ $schedule->class->room_number ?? 'N/A' }}
                                                    </small>
                                                </div>
                                                <div>
                                                    @if($schedule->is_complete)
                                                        <span class="status-indicator complete">
                                                            <i class="ti-check"></i> Complete
                                                        </span>
                                                    @else
                                                        <span class="status-indicator pending">
                                                            <i class="ti-time"></i> Pending
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <div class="quick-stats">
                                                        <div class="quick-stat-item">
                                                            <span class="time-badge">
                                                                <i class="ti-time"></i> 
                                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - 
                                                                {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                            </span>
                                                        </div>
                                                        <div class="quick-stat-item">
                                                            <i class="ti-user"></i>
                                                            <span><strong>{{ $schedule->enrolled_count }}</strong> students</span>
                                                        </div>
                                                        @if($schedule->attendance_marked)
                                                        <div class="quick-stat-item">
                                                            <i class="ti-check text-success"></i>
                                                            <span><strong>{{ $schedule->attendance_count }}</strong> marked</span>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-md-6 text-right">
                                                    @if($schedule->attendance_marked)
                                                        <a href="{{ route('superadmin.attendance.show', [$selectedDate, $schedule->class_id, $schedule->id]) }}" 
                                                           class="btn btn-info btn-sm">
                                                            <i class="ti-eye"></i> View
                                                        </a>
                                                        <a href="{{ route('superadmin.attendance.edit', [$selectedDate, $schedule->class_id, $schedule->id]) }}" 
                                                           class="btn btn-success btn-sm">
                                                            <i class="ti-pencil-alt"></i> Edit
                                                        </a>
                                                    @else
                                                        <a href="{{ route('superadmin.attendance.create', ['class_id' => $schedule->class_id, 'date' => $selectedDate, 'schedule_id' => $schedule->id]) }}" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="ti-plus"></i> Mark Attendance
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="empty-schedule">
                                            <i class="ti-calendar" style="font-size: 4rem; color: #cbd5e0;"></i>
                                            <h3 class="mt-3">No Classes Scheduled</h3>
                                            <p class="text-muted mb-4">There are no classes scheduled for {{ $dayOfWeek }}.</p>
                                            <a href="{{ route('superadmin.schedules.create') }}" class="btn btn-primary">
                                                <i class="ti-plus"></i> Create Schedule
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Daily Attendance Dashboard</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
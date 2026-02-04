@extends('layouts.app')

@section('title', 'Detailed Attendance Report')

@push('styles')
    <style>
        .session-header {
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .stat-box {
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }

        .stat-box h3 {
            margin: 10px 0 5px;
            font-size: 2rem;
        }

        .student-row {
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 10px;
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
            background-color: #e7f3ff;
            color: #007bff;
        }

        .status-badge-large {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 600;
        }

        .filter-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .trend-chart {
            height: 300px;
            background: white;
            border-radius: 8px;
            padding: 20px;
        }

        .comparison-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .trend-bar {
            height: 100%;
            background: linear-gradient(180deg, #007bff 0%, #0056b3 100%);
            border-radius: 4px;
            transition: all 0.3s;
        }

        .trend-bar:hover {
            background: linear-gradient(180deg, #0056b3 0%, #003d82 100%);
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
                                <h1>Detailed Attendance Report</h1>
                            </div>
                        </div>
                        <span class="text-muted">Comprehensive attendance analysis for {{ $student->full_name }}</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('parent.attendance.index', ['child_id' => $student->id]) }}">Attendance</a></li>
                                    <li class="active">Detailed Report</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Session Header -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="session-header card bg-primary">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h2 style="margin: 0 0 10px; color: white;">
                                            <i class="ti-user"></i> {{ $student->full_name }}
                                        </h2>
                                        <p style="margin: 0; font-size: 1.1rem; opacity: 0.9; color: white;">
                                            <i class="ti-calendar"></i> {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                            <i class="ti-book"></i> {{ $student->classes->count() }} Classes
                                        </p>
                                        <p style="margin: 10px 0 0; opacity: 0.9; color: white;">
                                            <i class="ti-email"></i> Parent: {{ $student->parent->name ?? 'Not assigned' }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <div class="stat-box">
                                            <h3>{{ $stats['period']['rate'] }}%</h3>
                                            <p style="margin: 0;">Attendance Rate</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="row">
                        <div class="col-lg-12">
                            <a href="{{ route('parent.attendance.index', ['child_id' => $student->id]) }}" class="btn btn-secondary mb-3">
                                <i class="ti-back-left"></i> Back to Attendance Overview
                            </a>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-user color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total</div>
                                        <div class="stat-digit">{{ $stats['period']['total'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-check color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Present</div>
                                        <div class="stat-digit">{{ $stats['period']['present'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-close color-danger border-danger"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Absent</div>
                                        <div class="stat-digit">{{ $stats['period']['absent'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-time color-warning border-warning"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Late</div>
                                        <div class="stat-digit">{{ $stats['period']['late'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="filter-card">
                                <form method="GET" action="{{ route('parent.attendance.show', $student) }}">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Date From</label>
                                                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Date To</label>
                                                <input type="date" name="date_to" value="{{ $dateTo }}" max="{{ now()->format('Y-m-d') }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div style="display: flex; gap: 10px;">
                                                    <button type="submit" class="btn btn-primary flex-fill">
                                                        <i class="ti-filter"></i> Apply
                                                    </button>
                                                    <a href="{{ route('parent.attendance.show', $student) }}" class="btn btn-secondary">
                                                        <i class="ti-reload"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Overall vs This Week vs This Month -->
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-stats-up"></i> Overall (All Time)</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center mb-3">
                                        <div class="col-12">
                                            <h2 class="text-primary">{{ $stats['overall']['rate'] }}%</h2>
                                            <p class="text-muted">Attendance Rate</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Total:</strong> {{ $stats['overall']['total'] }}</p>
                                            <p><strong>Present:</strong> <span class="text-success">{{ $stats['overall']['present'] }}</span></p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Absent:</strong> <span class="text-danger">{{ $stats['overall']['absent'] }}</span></p>
                                            <p><strong>Late:</strong> <span class="text-warning">{{ $stats['overall']['late'] }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-calendar"></i> This Week</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center mb-3">
                                        <div class="col-12">
                                            <h2 class="text-info">{{ $stats['this_week']['rate'] }}%</h2>
                                            <p class="text-muted">Attendance Rate</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Total:</strong> {{ $stats['this_week']['total'] }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Present:</strong> <span class="text-success">{{ $stats['this_week']['present'] }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-calendar"></i> This Month</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center mb-3">
                                        <div class="col-12">
                                            <h2 class="text-success">{{ $stats['this_month']['rate'] }}%</h2>
                                            <p class="text-muted">Attendance Rate</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Total:</strong> {{ $stats['this_month']['total'] }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Present:</strong> <span class="text-success">{{ $stats['this_month']['present'] }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Trend Chart -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-bar-chart"></i> 8-Week Attendance Trend</h4>
                                </div>
                                <div class="card-body">
                                    <div style="display: flex; align-items: flex-end; gap: 10px; height: 250px; padding: 20px;">
                                        @foreach($stats['weekly_trend'] as $week)
                                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: flex-end; align-items: center;">
                                            <small style="margin-bottom: 5px; font-weight: 600;">{{ $week['rate'] }}%</small>
                                            <div class="trend-bar" style="width: 100%; height: {{ $week['rate'] }}%;" title="{{ $week['week'] }}: {{ $week['rate'] }}%"></div>
                                            <small style="margin-top: 8px; color: #6c757d;">{{ $week['week'] }}</small>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- By Class Breakdown -->
                    @if(count($stats['by_class']) > 0)
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-book"></i> Attendance by Class</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Class</th>
                                                    <th>Teacher</th>
                                                    <th>Total Sessions</th>
                                                    <th>Present</th>
                                                    <th>Absent</th>
                                                    <th>Late</th>
                                                    <th>Attendance Rate</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($stats['by_class'] as $classStat)
                                                <tr>
                                                    <td><strong>{{ $classStat['class']->name }}</strong></td>
                                                    <td>{{ $classStat['class']->teacher->name ?? 'N/A' }}</td>
                                                    <td>{{ $classStat['total'] }}</td>
                                                    <td><span class="badge badge-success">{{ $classStat['present'] }}</span></td>
                                                    <td><span class="badge badge-danger">{{ $classStat['absent'] }}</span></td>
                                                    <td><span class="badge badge-warning">{{ $classStat['late'] }}</span></td>
                                                    <td>
                                                        <strong class="{{ $classStat['rate'] >= 90 ? 'text-success' : ($classStat['rate'] >= 75 ? 'text-info' : 'text-danger') }}">
                                                            {{ $classStat['rate'] }}%
                                                        </strong>
                                                        <div class="progress mt-1" style="height: 8px;">
                                                            <div class="progress-bar {{ $classStat['rate'] >= 90 ? 'bg-success' : ($classStat['rate'] >= 75 ? 'bg-info' : 'bg-danger') }}" 
                                                                 style="width: {{ $classStat['rate'] }}%"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Status Distribution -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-pie-chart"></i> Status Distribution (Selected Period)</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div style="padding: 20px; background: #d4edda; border-radius: 8px;">
                                                <h2 class="text-success">{{ $stats['by_status']['present'] }}</h2>
                                                <p class="mb-0"><strong>Present</strong></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div style="padding: 20px; background: #f8d7da; border-radius: 8px;">
                                                <h2 class="text-danger">{{ $stats['by_status']['absent'] }}</h2>
                                                <p class="mb-0"><strong>Absent</strong></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div style="padding: 20px; background: #fff3cd; border-radius: 8px;">
                                                <h2 class="text-warning">{{ $stats['by_status']['late'] }}</h2>
                                                <p class="mb-0"><strong>Late</strong></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Records by Date -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header mb-4">
                                    <h4><i class="ti-list"></i> Detailed Attendance Records ({{ $attendanceRecords->count() }})</h4>
                                </div>
                                <div class="card-body">
                                    @if($attendanceRecords->count() > 0)
                                        @foreach($attendanceByDate as $date => $records)
                                        <h5 class="mt-3 mb-3">{{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</h5>
                                        
                                        @foreach($records as $record)
                                        <div class="student-row">
                                            <div class="row align-items-center">
                                                <div class="col-md-4">
                                                    <strong>{{ $record->class->name }}</strong><br>
                                                    <small class="text-muted">
                                                        Teacher: {{ $record->class->teacher->name ?? 'N/A' }}
                                                    </small>
                                                </div>

                                                <div class="col-md-2">
                                                    @switch($record->status)
                                                        @case('present')
                                                            <span class="status-badge-large badge badge-success">
                                                                <i class="ti-check"></i> Present
                                                            </span>
                                                            @break
                                                        @case('absent')
                                                            <span class="status-badge-large badge badge-danger">
                                                                <i class="ti-close"></i> Absent
                                                            </span>
                                                            @break
                                                        @case('late')
                                                            <span class="status-badge-large badge badge-warning">
                                                                <i class="ti-time"></i> Late
                                                            </span>
                                                            @break
                                                        @case('unauthorized')
                                                            <span class="status-badge-large badge badge-orange">
                                                                <i class="ti-alert"></i> Unauthorized
                                                            </span>
                                                            @break
                                                    @endswitch
                                                </div>

                                                <div class="col-md-4">
                                                    @if($record->notes)
                                                        <small><strong>Notes:</strong> {{ $record->notes }}</small>
                                                    @else
                                                        <small class="text-muted">No notes</small>
                                                    @endif
                                                </div>

                                                <div class="col-md-2 text-right">
                                                    <small class="text-muted">
                                                        @if($record->schedule)
                                                            {{ \Carbon\Carbon::parse($record->schedule->start_time)->format('H:i') }}
                                                        @else
                                                            {{ $record->created_at->format('H:i') }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        @endforeach
                                    @else
                                        <div class="text-center py-5">
                                            <i class="ti-clipboard" style="font-size: 4rem; color: #cbd5e0;"></i>
                                            <h4 class="mt-3">No Attendance Records</h4>
                                            <p class="text-muted">No attendance records found for this period.</p>
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
                                <p>MLC Classroom - Detailed Attendance Report</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
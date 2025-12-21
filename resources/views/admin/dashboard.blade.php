@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
    <style>
        .quick-action-btn {
            width: 100%;
            margin-bottom: 10px;
            text-align: left;
        }
        .task-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .task-priority-high {
            background-color: #f8d7da;
        }
        .task-priority-medium {
            background-color: #fff3cd;
        }
        .task-priority-low {
            background-color: #d1ecf1;
        }
        .activity-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .activity-time {
            font-size: 0.85em;
            color: #888;
        }
    </style>
@endpush

@section('content')
    <div class="main">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-lg-8 p-r-0 title-margin-right">
                    <div class="page-header">
                        <div class="page-title">
                            <h1>Admin Dashboard <span>Welcome back, {{ auth()->user()->name }}</span></h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 p-l-0 title-margin-left">
                    <div class="page-header">
                        <div class="page-title">
                            <ol class="breadcrumb text-right">
                                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="active">Admin Overview</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div id="main-content">
                <!-- Quick Stats - Admin Focus -->
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon">
                                    <i class="ti-user"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Total Students</div>
                                        <div class="stat-text">Count: <strong>{{ number_format($stats['total_students']) }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon">
                                    <i class="ti-briefcase"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Total Teachers</div>
                                        <div class="stat-text">Count: <strong>{{ number_format($stats['total_teachers']) }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon">
                                    <i class="ti-calendar"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Today's Classes</div>
                                        <div class="stat-text">Count: <strong>{{ $todaysClassesCount }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Overview -->
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card bg-success">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-check-box"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Present Today</div>
                                        <div class="stat-text">Count: <strong>{{ $attendanceToday['present'] }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card bg-danger">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-close"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Absent Today</div>
                                        <div class="stat-text">Count: <strong>{{ $attendanceToday['absent'] }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card bg-warning">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-time"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Late Arrivals</div>
                                        <div class="stat-text">Count: <strong>{{ $attendanceToday['late'] }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
              
                </div>

                <!-- Today's Schedule & Quick Actions -->
                <div class="row">
                    <!-- Today's Class Schedule -->
                    <div class="col-lg-8">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-calendar"></i> Today's Class Schedule - {{ date('l, F d, Y') }}</h4>
                                <div class="card-header-right-icon">
                                    <ul>
                                        <li><a href="#"><i class="ti-reload"></i></a></li>
                                        <li><a href="#"><i class="ti-printer"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($upcomingClasses->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Class</th>
                                                <th>Subject</th>
                                                <th>Teacher</th>
                                                <th>Room</th>
                                                <th>Schedule</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($upcomingClasses as $class)
                                            <tr>
                                                <td><span class="badge badge-primary">{{ $class->name }}</span></td>
                                                <td>{{ $class->subject }}</td>
                                                <td>{{ $class->teacher->name ?? 'Not Assigned' }}</td>
                                                <td>{{ $class->room_number ?? 'TBA' }}</td>
                                                <td>
                                                    @if($class->schedules->count() > 0)
                                                        {{ $class->schedules->first()->start_time->format('H:i') }} - {{ $class->schedules->first()->end_time->format('H:i') }}
                                                    @else
                                                        Not scheduled
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center py-4">
                                    <i class="ti-calendar" style="font-size: 3rem; color: #cbd5e0;"></i>
                                    <p class="text-muted mt-3">No classes scheduled for today</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions & Tasks -->
                    <div class="col-lg-4">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-bolt"></i> Quick Actions</h4>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('admin.students.create') }}" class="btn btn-primary quick-action-btn">
                                    <i class="ti-plus"></i> Add Student
                                </a>
                                <a href="{{ route('admin.attendance.create') }}" class="btn btn-success quick-action-btn">
                                    <i class="ti-clipboard"></i> Mark Attendance
                                </a>
                                <a href="{{ route('admin.schedules.create') }}" class="btn btn-info quick-action-btn">
                                    <i class="ti-calendar"></i> Schedule Class
                                </a>
                                <a href="{{ route('admin.notifications.index') }}" class="btn btn-warning quick-action-btn">
                                    <i class="ti-announcement"></i> Send Notice
                                </a>
                                <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary quick-action-btn">
                                    <i class="ti-file"></i> View Reports
                                </a>
                            </div>
                        </div>

                      
                    </div>
                </div>

                <!-- Attendance Overview Charts -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Weekly Attendance Trend</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="weeklyAttendanceChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Class-wise Attendance (Today)</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="classwiseAttendanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities & Notifications -->
                <div class="row">
                    <!-- Recent Activities -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-time"></i> Recent Activities</h4>
                                <div class="card-header-right-icon">
                                    <ul>
                                        <li><a href="#"><i class="ti-reload"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                @forelse($recentActivity as $activity)
                                <div class="activity-item">
                                    <strong>{{ ucwords(str_replace('_', ' ', $activity->action)) }}</strong>
                                    <p class="mb-0">{{ $activity->description }}</p>
                                    <span class="activity-time"><i class="ti-time"></i> {{ $activity->created_at->diffForHumans() }}</span>
                                </div>
                                @empty
                                <div class="text-center py-4">
                                    <i class="ti-time" style="font-size: 3rem; color: #cbd5e0;"></i>
                                    <p class="text-muted mt-3">No recent activities</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Notifications & Alerts -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-bell"></i> Notifications & Alerts <span class="badge badge-warning">{{ count($notifications) }}</span></h4>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                @forelse($notifications as $notification)
                                <div class="alert alert-{{ $notification['type'] }}">
                                    <strong><i class="ti-alert"></i> {{ $notification['title'] }}</strong>
                                    <p class="mb-0">{{ $notification['message'] }}</p>
                                    <small class="text-muted">{{ $notification['time'] }}</small>
                                </div>
                                @empty
                                <div class="text-center py-4">
                                    <i class="ti-bell" style="font-size: 3rem; color: #cbd5e0;"></i>
                                    <p class="text-muted mt-3">No notifications</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Performance Overview -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-bar-chart"></i> Top Student Performers</h4>
                            </div>
                            <div class="card-body">
                                @if($studentPerformance->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Student Name</th>
                                                <th>Class</th>
                                                <th>Average Score</th>
                                                <th>Attendance</th>
                                                <th>Grade</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($studentPerformance as $index => $student)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $student['name'] }}</strong></td>
                                                <td><span class="badge badge-primary">{{ $student['class'] }}</span></td>
                                                <td>
                                                    <span class="badge badge-{{ $student['avg_score'] >= 85 ? 'success' : ($student['avg_score'] >= 70 ? 'info' : ($student['avg_score'] >= 60 ? 'warning' : 'danger')) }}">
                                                        {{ $student['avg_score'] }}%
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $student['attendance_rate'] >= 95 ? 'success' : ($student['attendance_rate'] >= 85 ? 'info' : 'warning') }}">
                                                        {{ $student['attendance_rate'] }}%
                                                    </span>
                                                </td>
                                                <td><strong>{{ $student['grade'] }}</strong></td>
                                                <td>
                                                    <span class="badge badge-{{ $student['status'] == 'Excellent' ? 'success' : ($student['status'] == 'Good' ? 'info' : ($student['status'] == 'Average' ? 'warning' : 'danger')) }}">
                                                        {{ $student['status'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.students.show', $student['id']) }}" class="btn btn-sm btn-info">
                                                        <i class="ti-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center py-4">
                                    <i class="ti-bar-chart" style="font-size: 3rem; color: #cbd5e0;"></i>
                                    <p class="text-muted mt-3">No student performance data available</p>
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
                            <p>MLC Classroom - Admin Dashboard | Last Updated: {{ now()->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/lib/chart-js/Chart.bundle.js') }}"></script>
    <script>
        // Inject chart data from controller
        var weeklyAttendanceChartData = {!! json_encode($weeklyAttendanceChartData) !!};
        var classwiseAttendanceData = {!! json_encode($classwiseAttendanceData) !!};
    </script>
    <script src="{{ asset('assets/js/custom-chart-init.js') }}"></script>
@endpush
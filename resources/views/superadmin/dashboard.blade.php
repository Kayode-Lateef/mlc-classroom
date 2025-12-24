@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@push('styles')
    <style>
        .quick-action-btn {
            margin: 5px;
            width: 100%;
            text-align: left;
        }

        .system-status-badge {
            font-size: 1rem;
            padding: 0.25rem 0.5rem;
        }
        
        .activity-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .activity-time {
            font-size: 12px;
            color: #999;
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
                                <h1>Hello, <span>Welcome {{ auth()->user()->name }}</span></h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Home</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Stats Cards Row 1 -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-four">
                                    <div class="stat-icon">
                                        <i class="ti-user"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="text-left dib">
                                            <div class="stat-heading">Total Students</div>
                                            <div class="stat-text">Total: {{ number_format($stats['total_students']) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-four">
                                    <div class="stat-icon">
                                        <i class="ti-briefcase"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="text-left dib">
                                            <div class="stat-heading">Total Teachers</div>
                                            <div class="stat-text">Total: {{ number_format($stats['total_teachers']) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-four">
                                    <div class="stat-icon">
                                        <i class="ti-blackboard"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="text-left dib">
                                            <div class="stat-heading">Total Classes</div>
                                            <div class="stat-text">Total: {{ number_format($stats['total_classes']) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-four">
                                    <div class="stat-icon">
                                        <i class="ti-id-badge"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="text-left dib">
                                            <div class="stat-heading">Total Parents</div>
                                            <div class="stat-text">Total: {{ number_format($stats['total_parents']) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards Row 2 -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="card bg-danger">
                                <div class="stat-widget-six">
                                    <div class="stat-icon">
                                        <i class="ti-crown"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="text-left dib">
                                            <div class="stat-heading">Super Admins</div>
                                            <div class="stat-text">Count: <strong>{{ $stats['total_superadmins'] }}</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card bg-success">
                                <div class="stat-widget-six">
                                    <div class="stat-icon">
                                        <i class="ti-shield"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="text-left dib">
                                            <div class="stat-heading">Admins</div>
                                            <div class="stat-text">Count: <strong>{{ $stats['total_admins'] }}</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card bg-info">
                                <div class="stat-widget-six">
                                    <div class="stat-icon">
                                        <i class="ti-book"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="text-left dib">
                                            <div class="stat-heading">Active Enrollments</div>
                                            <div class="stat-text">Count: <strong>{{ number_format($stats['active_enrollments']) }}</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card bg-warning">
                                <div class="stat-widget-six">
                                    <div class="stat-icon">
                                        <i class="ti-check-box"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="text-left dib">
                                            <div class="stat-heading">Today's Attendance</div>
                                            @php
                                                $attendanceRate = $attendanceToday['total'] > 0 ? round(($attendanceToday['present'] / $attendanceToday['total']) * 100, 1) : 0;
                                            @endphp
                                            <div class="stat-text">Rate: <strong>{{ $attendanceRate }}%</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row">
                        <!-- Student & Teacher Enrollment Trend -->
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4><i class="ti-stats-up"></i> Enrollment Trend (Last 6 Months)</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="enrollmentTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <!-- User Distribution -->
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4><i class="ti-pie-chart"></i> User Distribution</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="userDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance & Performance Charts -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4><i class="ti-bar-chart"></i> Weekly Attendance Overview</h4>
                                </div>
                                <div class="card-body">
                                    <div class="ct-bar-chart"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4><i class="ti-pie-chart"></i> Performance Distribution</h4>
                                </div>
                                <div class="card-body">
                                    <div class="ct-pie-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities & Quick Actions -->
                    <div class="row">
                        <!-- Recent System Activities -->
                        <div class="col-lg-8">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-time"></i> Recent System Activities</h4>
                                </div>
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    @if($recentActivity->count() > 0)
                                        @foreach($recentActivity as $activity)
                                        <div class="activity-item">
                                            <strong>{{ ucwords(str_replace('_', ' ', $activity->action)) }}</strong>
                                            <p class="mb-0">{{ $activity->description }}</p>
                                            <span class="activity-time">
                                                <i class="ti-time"></i> {{ $activity->created_at->diffForHumans() }}
                                                @if($activity->user)
                                                    by {{ $activity->user->name }}
                                                @endif
                                            </span>
                                        </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted text-center py-4">No recent activities</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="col-lg-4">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-bolt"></i> Quick Actions</h4>
                                </div>
                                <div class="card-body">
                                    <a href="{{ route('superadmin.students.create') }}" class="btn btn-primary quick-action-btn mb-2">
                                        <i class="ti-plus"></i> Add New Student
                                    </a>
                                    <a href="{{ route('superadmin.users.create') }}" class="btn btn-success quick-action-btn mb-2">
                                        <i class="ti-user"></i> Add New User
                                    </a>
                                    <a href="{{ route('superadmin.classes.create') }}" class="btn btn-info quick-action-btn mb-2">
                                        <i class="ti-blackboard"></i> Create New Class
                                    </a>
                                    <a href="{{ route('superadmin.roles.index') }}" class="btn btn-warning quick-action-btn mb-2">
                                        <i class="ti-shield"></i> Manage Roles
                                    </a>
                                    <a href="{{ route('superadmin.permissions.index') }}" class="btn btn-pink quick-action-btn mb-2">
                                        <i class="ti-key"></i> Manage Permissions
                                    </a>
                                    <a href="{{ route('superadmin.settings.index') }}" class="btn btn-dark quick-action-btn">
                                        <i class="ti-settings"></i> System Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent User Registrations -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-user"></i> Recent User Registrations (Last 30 Days)</h4>
                                </div>
                                <div class="card-body">
                                    @if($recentUsers->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Role</th>
                                                    <th>Email</th>
                                                    <th>Registration Date</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentUsers as $index => $newUser)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $newUser->name }}</td>
                                                    <td>
                                                        @if($newUser->role === 'superadmin')
                                                            <span class="badge badge-danger">Super Admin</span>
                                                        @elseif($newUser->role === 'admin')
                                                            <span class="badge badge-success">Admin</span>
                                                        @elseif($newUser->role === 'teacher')
                                                            <span class="badge badge-info">Teacher</span>
                                                        @else
                                                            <span class="badge badge-warning">Parent</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $newUser->email }}</td>
                                                    <td>{{ $newUser->created_at->format('d M Y') }}</td>
                                                    <td>
                                                        @if($newUser->email_verified_at)
                                                            <span class="badge badge-success">Verified</span>
                                                        @else
                                                            <span class="badge badge-warning">Unverified</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('superadmin.users.show', $newUser) }}" class="btn btn-sm btn-info">
                                                            <i class="ti-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                        <p class="text-muted text-center py-4">No recent user registrations</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Enrollments -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-book"></i> Recent Class Enrollments (Last 7 Days)</h4>
                                </div>
                                <div class="card-body">
                                    @if($recentEnrollments->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Student</th>
                                                    <th>Class</th>
                                                    <th>Enrollment Date</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentEnrollments as $index => $enrollment)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $enrollment->student->full_name }}</td>
                                                    <td>{{ $enrollment->class->name }}</td>
                                                    <td>{{ $enrollment->enrollment_date->format('d M Y') }}</td>
                                                    <td>
                                                        @if($enrollment->status === 'active')
                                                            <span class="badge badge-success">Active</span>
                                                        @else
                                                            <span class="badge badge-secondary">{{ ucfirst($enrollment->status) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('superadmin.students.show', $enrollment->student) }}" class="btn btn-sm btn-info">
                                                            <i class="ti-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                        <p class="text-muted text-center py-4">No recent enrollments</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Progress Sheets -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-clipboard"></i> Recent Progress Sheets (Last 7 Days)</h4>
                                </div>
                                <div class="card-body">
                                    @if($recentProgressSheets->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Class</th>
                                                    <th>Date</th>
                                                    <th>Topic</th>
                                                    <th>Teacher</th>
                                                    <th>Students Noted</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentProgressSheets as $index => $sheet)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $sheet->class->name }}</td>
                                                    <td>{{ $sheet->date->format('d M Y') }}</td>
                                                    <td>{{ $sheet->topic ?? 'N/A' }}</td>
                                                    <td>{{ $sheet->teacher->name }}</td>
                                                    <td>
                                                        <span class="badge badge-info">{{ $sheet->progressNotes->count() }} students</span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('superadmin.progress-sheets.show', $sheet) }}" class="btn btn-sm btn-info">
                                                            <i class="ti-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                        <p class="text-muted text-center py-4">No recent progress sheets</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Class Performance & System Info -->
                    <div class="row">
                        <!-- Class Performance Chart -->
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4><i class="ti-bar-chart-alt"></i> Class-wise Performance Comparison</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="classPerformanceChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- System Status -->
                        <div class="col-lg-4">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-settings"></i> System Status</h4>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class="ti-server"></i> Database</span>
                                                <span class="badge badge-success system-status-badge">Online</span>
                                            </div>
                                        </li>
                                        <li class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class="ti-pulse"></i> Server Status</span>
                                                <span class="badge badge-success system-status-badge">Healthy</span>
                                            </div>
                                        </li>
                                        <li class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class="ti-harddrive"></i> Total Users</span>
                                                <span class="badge badge-info system-status-badge">{{ $stats['total_users'] }}</span>
                                            </div>
                                        </li>
                                        <li class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class="ti-shield"></i> Roles</span>
                                                <span class="badge badge-info system-status-badge">{{ $stats['total_roles'] }}</span>
                                            </div>
                                        </li>
                                        <li class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class="ti-key"></i> Permissions</span>
                                                <span class="badge badge-info system-status-badge">{{ $stats['total_permissions'] }}</span>
                                            </div>
                                        </li>
                                        <li class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class="ti-book"></i> Homework Rate</span>
                                                <span class="badge badge-{{ $homeworkCompletionRate >= 80 ? 'success' : ($homeworkCompletionRate >= 60 ? 'warning' : 'danger') }} system-status-badge">{{ $homeworkCompletionRate }}%</span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Critical Activity Log -->
                    @if($criticalActivity->count() > 0)
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert alert-warning">
                                <div class="card-header">
                                    <h4><i class="ti-alert"></i> Critical System Events</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Action</th>
                                                    <th>Description</th>
                                                    <th>User</th>
                                                    <th>Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($criticalActivity as $critical)
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-{{ 
                                                            str_contains($critical->action, 'deleted') ? 'danger' : 
                                                            (str_contains($critical->action, 'updated') ? 'warning' : 'info') 
                                                        }}">
                                                            {{ ucwords(str_replace('_', ' ', $critical->action)) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $critical->description }}</td>
                                                    <td>{{ $critical->user ? $critical->user->name : 'System' }}</td>
                                                    <td>{{ $critical->created_at->diffForHumans() }}</td>
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

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Super Admin Dashboard | Last Updated: {{ now()->format('d M Y, H:i') }}</p>
                            </div>
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
        // Inject PHP data into JavaScript
        var enrollmentChartData = {!! json_encode($enrollmentChartData) !!};
        var userDistributionData = {!! json_encode($userDistributionData) !!};
        var weeklyAttendanceData = {!! json_encode($weeklyAttendanceData) !!};
    </script>
    
    <script src="{{ asset('assets/js/custom-chart-init.js') }}"></script>
@endpush
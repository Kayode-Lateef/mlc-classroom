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

                    <!-- ============================================ -->
                    <!-- WEEKLY HOURS & INCOME STATISTICS (NEW) -->
                    <!-- ============================================ -->

                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="mb-3"><i class="ti-time"></i> Weekly Hours & Income Overview</h4>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Total Weekly Hours -->
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib">
                                        <i class="ti-time color-primary border-primary"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Weekly Hours</div>
                                        <div class="stat-digit">{{ number_format($totalWeeklyHours, 1) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Weekly Income -->
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib">
                                        <i class="ti-wallet color-success border-success"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Weekly Income</div>
                                        <div class="stat-digit">£{{ number_format($weeklyIncome, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Income -->
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib">
                                        <i class="ti-money color-info border-info"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Monthly Income</div>
                                        <div class="stat-digit">£{{ number_format($monthlyIncome, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Annual Income -->
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib">
                                        <i class="ti-bar-chart color-warning border-warning"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Annual Projection</div>
                                        <div class="stat-digit">£{{ number_format($annualIncome, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info Cards -->
                    <div class="row">
                        <!-- Average Hours Per Student -->
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib">
                                        <i class="ti-stats-up" style="color: #9c27b0; border-color: #9c27b0;"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Avg Hours/Student</div>
                                        <div class="stat-digit">{{ number_format($avgHoursPerStudent, 1) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hourly Rate -->
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib">
                                        <i class="ti-tag" style="color: #ff9800; border-color: #ff9800;"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Hourly Rate</div>
                                        <div class="stat-digit">£{{ number_format($hourlyRate, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hour Changes This Month -->
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib">
                                        <i class="ti-reload" style="color: #00bcd4; border-color: #00bcd4;"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Hour Changes</div>
                                        <div class="stat-digit">{{ $hourChangesThisMonth }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Active Students -->
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib">
                                        <i class="ti-user color-success border-success"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Active Students</div>
                                        <div class="stat-digit">{{ $stats['active_students'] }}</div>
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

                    <!-- Attendance & Performance Charts -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4><i class="ti-bar-chart"></i> Weekly Attendance Overview</h4>
                                </div>
                                <div class="card-body">
                                    <div class="ct-bar-chart"></div>
                                </div>
                            </div>
                        </div>

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


<!-- ============================================ -->
<!-- STUDENTS BY HOUR RANGE CHART (NEW) -->
<!-- ============================================ -->

<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card alert">
            <div class="card-header">
                <h4>Students by Weekly Hours</h4>
            </div>
            <div class="card-body">
                <canvas id="studentsByHoursChart" height="200"></canvas>
            </div>
        </div>
    </div>

 
    <div class="col-lg-6">
        <div class="card alert">
            <div class="card-header">
                <h4>Monthly Income by Hour Range</h4>
            </div>
            <div class="card-body">
                <canvas id="incomeByHourRangeChart" height="200"></canvas>
            </div>
        </div>
    </div>

 
</div>


<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card alert">
            <div class="card-header">
                <h4><i class="ti-stats-up"></i> Income Breakdown</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach(['0.5-2 hrs' => '#3386f7', '2-5 hrs' => '#00bcd4', '5-10 hrs' => '#4caf50', '10+ hrs' => '#ff9800'] as $range => $color)
                    @php
                        $index = array_search($range, $incomeByHourRange['labels']);
                        $studentCount = $studentsByHours[$range] ?? 0;
                        $income = $incomeByHourRange['data'][$index] ?? 0;
                    @endphp
                    <div class="col-lg-3 mb-3">
                        <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid {{ $color }};">
                            <div style="font-size: 0.875rem; color: #6c757d; margin-bottom: 5px;">
                                {{ $range }}
                            </div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: {{ $color }};">
                                {{ $studentCount }} 
                                <small style="font-size: 0.875rem; color: #6c757d;">students</small>
                            </div>
                            <div style="font-size: 1.125rem; font-weight: 600; color: #28a745; margin-top: 5px;">
                                £{{ number_format($income, 2) }}/mo
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>


                    <!-- Recent Activities & Quick Actions -->
                    <div class="row">
                        <!-- Recent System Activities -->
                        <div class="col-lg-6">
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

                        <!-- RECENT HOUR CHANGES -->

                        <div class="col-lg-6">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-time"></i> Recent Hour Changes (Last 7 Days)</h4>
                                </div>
                                <div class="card-body">
                                    @if($recentHourChanges->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Student</th>
                                                        <th>Change</th>
                                                        <th>Changed By</th>
                                                        <th>When</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recentHourChanges as $change)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('superadmin.students.show', $change->student_id) }}">
                                                                {{ $change->student->full_name }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <strong>{{ number_format($change->old_hours, 1) }}</strong> 
                                                            <i class="ti-arrow-right text-muted"></i> 
                                                            <strong style="color: #3386f7;">{{ number_format($change->new_hours, 1) }}</strong> hrs
                                                            
                                                            @if($change->isIncrease())
                                                                <span class="badge badge-success ml-2">
                                                                    <i class="ti-arrow-up"></i> {{ number_format($change->hour_difference, 1) }}
                                                                </span>
                                                            @else
                                                                <span class="badge badge-warning ml-2">
                                                                    <i class="ti-arrow-down"></i> {{ number_format(abs($change->hour_difference), 1) }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $change->changedBy->name }}</td>
                                                        <td>
                                                            <small class="text-muted">
                                                                <i class="ti-time"></i> {{ $change->changed_at->diffForHumans() }}
                                                            </small>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="ti-time" style="font-size: 3rem; color: #cbd5e0;"></i>
                                            <p class="text-muted mt-2">No hour changes in the last 7 days</p>
                                        </div>
                                    @endif
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



                    <!--  System Info -->
                    <div class="row">
                          <!-- Critical Activity Log -->
                        @if($criticalActivity->count() > 0)
                            <div class="col-lg-8">
                                <div class="card alert alert-warning">
                                    <div class="card-header mb-3">
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
                        @endif

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
       
        var studentsByHoursData = {!! json_encode(array_keys($studentsByHours)) !!};
        var studentsByHoursValues = {!! json_encode(array_values($studentsByHours)) !!};
        var incomeByHourRangeLabels = {!! json_encode($incomeByHourRange['labels'] ?? []) !!};
        var incomeByHourRangeValues = {!! json_encode($incomeByHourRange['data'] ?? []) !!}; 

    </script>
    
    <script src="{{ asset('assets/js/custom-chart-init.js') }}"></script>
@endpush
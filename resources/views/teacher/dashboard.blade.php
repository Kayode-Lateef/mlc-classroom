@extends('layouts.app')

@section('title', 'Teacher Dashboard')

@push('styles')
<style>
        .quick-action-btn {
            width: 100%;
            margin-bottom: 10px;
        }
        
        .class-card {
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .class-card:hover {
            background-color: #e9ecef;
            transform: translateX(5px);
        }
        
        .class-card.active {
            background-color: #d4edda;
        }
        
        .class-card.completed {
            opacity: 0.7;
        }
        
        .student-list-item {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .student-list-item:last-child {
            border-bottom: none;
        }
        
        .assignment-item {
            padding: 12px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
        }
        
        .grade-badge {
            min-width: 50px;
            text-align: center;
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
                            <h1>Teacher Dashboard <span>Welcome, {{ auth()->user()->name }}</span></h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 p-l-0 title-margin-left">
                    <div class="page-header">
                        <div class="page-title">
                            <ol class="breadcrumb text-right">
                                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="active">My Classes</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div id="main-content">
                <!-- Quick Stats - Teacher Focus -->
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon">
                                    <i class="ti-user"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">My Students</div>
                                        <div class="stat-text">Total: <strong>{{ $stats['total_students'] }}</strong></div>
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
                                        <div class="stat-heading">My Classes</div>
                                        <div class="stat-text">Total: <strong>{{ $stats['total_classes'] }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon">
                                    <i class="ti-clipboard"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Pending Grades</div>
                                        <div class="stat-text">Count: <strong>{{ $stats['pending_grading'] }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon ">
                                    <i class="ti-write"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Assignments</div>
                                        <div class="stat-text">Active: <strong>{{ $stats['active_assignments'] }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Overview -->
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card bg-success">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-calendar"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Classes Today</div>
                                        <div class="stat-text">Count: <strong>{{ $classesTodayCount }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card bg-info">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-check-box"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Attendance Rate</div>
                                        <div class="stat-text">Today: <strong>{{ $todayAttendanceRate }}%</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card bg-warning">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-pencil-alt"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Due Assignments</div>
                                        <div class="stat-text">This Week: <strong>{{ $dueThisWeek }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card bg-danger">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-alert"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Absent Students</div>
                                        <div class="stat-text">Today: <strong>{{ $absentStudentsToday }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule & Quick Actions -->
                <div class="row">
                    <!-- Today's Teaching Schedule -->
                    <div class="col-lg-8">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-calendar"></i> My Schedule Today - {{ date('l, F d, Y') }}</h4>
                                <div class="card-header-right-icon">
                                    <ul>
                                        <li><a href="#"><i class="ti-reload"></i></a></li>
                                        <li><a href="#"><i class="ti-printer"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(count($todaysSchedule) > 0)
                                    @foreach($todaysSchedule as $item)
                                    <div class="class-card {{ $item['status'] }}">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5 class="mb-1">
                                                    @if($item['status'] === 'completed')
                                                        <i class="ti-check-box text-success"></i>
                                                    @elseif($item['status'] === 'in_progress')
                                                        <i class="ti-video-clapper text-success"></i>
                                                    @else
                                                        <i class="ti-alarm-clock text-warning"></i>
                                                    @endif
                                                    {{ $item['class']->name }}
                                                </h5>
                                                <p class="mb-1">
                                                    <i class="ti-time"></i> 
                                                    <strong>{{ $item['start_time']->format('h:i A') }} - {{ $item['end_time']->format('h:i A') }}</strong> 
                                                    | Room {{ $item['class']->room_number ?? 'TBA' }}
                                                </p>
                                                <p class="mb-0 text-muted">Subject: {{ $item['class']->subject }}</p>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                @if($item['status'] === 'completed')
                                                    <span class="badge badge-secondary">Completed</span><br>
                                                    @if(!empty($item['attendance']))
                                                        <small>Attendance: {{ $item['attendance']['present'] }}/{{ $item['attendance']['total'] }} ({{ $item['attendance']['rate'] }}%)</small><br>
                                                    @endif
                                                    <a href="{{ route('teacher.classes.show', $item['class']->id) }}" class="btn btn-sm btn-info mt-2">
                                                        <i class="ti-eye"></i> View Details
                                                    </a>
                                                @elseif($item['status'] === 'in_progress')
                                                    <span class="badge badge-success">In Progress</span><br>
                                                    <small>Started {{ $item['start_time']->diffForHumans() }}</small><br>
                                                    <a href="{{ route('teacher.attendance.create', ['class_id' => $item['class']->id]) }}" class="btn btn-sm btn-success mt-2">
                                                        <i class="ti-clipboard"></i> Mark Attendance
                                                    </a>
                                                @else
                                                    <span class="badge badge-warning">Upcoming</span><br>
                                                    <small>Starts {{ $item['start_time']->diffForHumans() }}</small><br>
                                                    <a href="{{ route('teacher.classes.show', $item['class']->id) }}" class="btn btn-sm btn-primary mt-2">
                                                        <i class="ti-eye"></i> Prepare
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-4">
                                        <i class="ti-calendar" style="font-size: 3rem; color: #cbd5e0;"></i>
                                        <h4 class="mt-3">No Classes Today</h4>
                                        <p class="text-muted">You don't have any classes scheduled for today.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions & Reminders -->
                    <div class="col-lg-4">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-bolt"></i> Quick Actions</h4>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('teacher.attendance.create') }}" class="btn btn-primary quick-action-btn">
                                    <i class="ti-clipboard"></i> Mark Attendance
                                </a>
                                <a href="{{ route('teacher.homework.index') }}" class="btn btn-success quick-action-btn">
                                    <i class="ti-pencil-alt"></i> Grade Assignments
                                </a>
                                <a href="{{ route('teacher.homework.create') }}" class="btn btn-info quick-action-btn">
                                    <i class="ti-write"></i> Create Assignment
                                </a>
                                <a href="{{ route('teacher.progress-sheets.index') }}" class="btn btn-warning quick-action-btn">
                                    <i class="ti-calendar"></i> Progress Sheets
                                </a>
                                <a href="{{ route('teacher.classes.index') }}" class="btn btn-secondary quick-action-btn">
                                    <i class="ti-bar-chart"></i> My Classes
                                </a>
                                <a href="{{ route('teacher.resources.index') }}" class="btn btn-dark quick-action-btn">
                                    <i class="ti-folder"></i> Learning Resources
                                </a>
                            </div>
                        </div>

                        <!-- Important Reminders -->
                        <div class="card alert mt-3">
                            <div class="card-header">
                                <h4><i class="ti-bell"></i> Reminders <span class="badge badge-danger">{{ count($pendingByAssignment) }}</span></h4>
                            </div>
                            <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                                @if($stats['pending_grading'] > 0)
                                    <div class="alert alert-danger">
                                        <strong><i class="ti-alert"></i> Urgent!</strong>
                                        <p class="mb-0">{{ $stats['pending_grading'] }} assignments need grading</p>
                                    </div>
                                @endif
                                
                                @if($dueThisWeek > 0)
                                    <div class="alert alert-warning">
                                        <strong><i class="ti-time"></i> Deadline</strong>
                                        <p class="mb-0">{{ $dueThisWeek }} assignments due this week</p>
                                    </div>
                                @endif
                                
                                @if(count($studentsNeedingAttention) > 0)
                                    <div class="alert alert-info">
                                        <strong><i class="ti-alert"></i> Attention Needed</strong>
                                        <p class="mb-0">{{ count($studentsNeedingAttention) }} students need attention</p>
                                    </div>
                                @endif
                                
                                <div class="alert alert-success">
                                    <strong><i class="ti-check"></i> Today</strong>
                                    <p class="mb-0">{{ $classesTodayCount }} classes scheduled</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Performance & Assignments -->
                <div class="row">
                    <!-- Recent Student Performance -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-bar-chart"></i> Recent Student Performance - My Classes</h4>
                                <div class="card-header-right-icon">
                                    <ul>
                                        <li><a href="#"><i class="ti-reload"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(count($recentStudentPerformance) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Class</th>
                                                    <th>Last Test</th>
                                                    <th>Avg</th>
                                                    <th>Attendance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentStudentPerformance as $performance)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $performance['student']->full_name }}</strong><br>
                                                        <small class="text-muted">{{ $performance['student']->id }}</small>
                                                    </td>
                                                    <td><span class="badge badge-primary">{{ Str::limit($performance['class'], 10) }}</span></td>
                                                    <td>
                                                        <span class="badge {{ $performance['last_test'] >= 80 ? 'badge-success' : ($performance['last_test'] >= 70 ? 'badge-info' : ($performance['last_test'] >= 60 ? 'badge-warning' : 'badge-danger')) }} grade-badge">
                                                            {{ $performance['last_test'] }}%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $performance['avg_score'] >= 80 ? 'badge-success' : ($performance['avg_score'] >= 70 ? 'badge-info' : ($performance['avg_score'] >= 60 ? 'badge-warning' : 'badge-danger')) }} grade-badge">
                                                            {{ $performance['avg_score'] }}%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $performance['attendance_rate'] >= 90 ? 'badge-success' : ($performance['attendance_rate'] >= 80 ? 'badge-warning' : 'badge-danger') }}">
                                                            {{ $performance['attendance_rate'] }}%
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <p class="text-muted">No student performance data available yet.</p>
                                    </div>
                                @endif
                                <div class="text-center mt-3">
                                    <a href="{{ route('teacher.classes.index') }}" class="btn btn-primary btn-sm">View All Students</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Assignments to Grade -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-pencil"></i> Pending Assignments to Grade <span class="badge badge-warning">{{ $stats['pending_grading'] }}</span></h4>
                            </div>
                            <div class="card-body" style="max-height: 450px; overflow-y: auto;">
                                @if(count($pendingByAssignment) > 0)
                                    @foreach($pendingByAssignment as $assignmentId => $data)
                                    <div class="assignment-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $data['assignment']->title }}</h6>
                                                <p class="mb-1"><span class="badge badge-primary">{{ $data['assignment']->class->name }}</span></p>
                                                <small class="text-muted">
                                                    Submitted: {{ $data['total_submitted'] }} | Graded: {{ $data['total_graded'] }}
                                                </small>
                                            </div>
                                            <div class="text-right">
                                                @php
                                                    $dueDate = \Carbon\Carbon::parse($data['assignment']->due_date);
                                                    $isOverdue = $dueDate->isPast();
                                                    $isDueToday = $dueDate->isToday();
                                                    $isDueTomorrow = $dueDate->isTomorrow();
                                                @endphp
                                                
                                                @if($isOverdue)
                                                    <span class="badge badge-danger">Overdue</span>
                                                @elseif($isDueToday)
                                                    <span class="badge badge-danger">Due Today</span>
                                                @elseif($isDueTomorrow)
                                                    <span class="badge badge-warning">Due Tomorrow</span>
                                                @else
                                                    <span class="badge badge-info">Due {{ $dueDate->format('M d') }}</span>
                                                @endif
                                                <br>
                                                <a href="{{ route('teacher.homework.submissions', $data['assignment']->id) }}" class="btn btn-sm btn-success mt-2">Grade Now</a>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-4">
                                        <i class="ti-check" style="font-size: 3rem; color: #28a745;"></i>
                                        <h5 class="mt-3">All Caught Up!</h5>
                                        <p class="text-muted">No pending assignments to grade.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Class Performance Charts -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>My Classes - Average Performance</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="classPerformanceChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Attendance Trend - Last 4 Weeks</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="attendanceTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Needs Attention & Recent Submissions -->
                <div class="row">
                    <!-- Students Needing Attention -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-alert"></i> Students Needing Attention <span class="badge badge-danger">{{ count($studentsNeedingAttention) }}</span></h4>
                            </div>
                            <div class="card-body">
                                @if(count($studentsNeedingAttention) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Class</th>
                                                    <th>Issue</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($studentsNeedingAttention as $student)
                                                <tr>
                                                    <td>{{ $student['student']->full_name }}</td>
                                                    <td><span class="badge badge-primary">{{ Str::limit($student['class'], 10) }}</span></td>
                                                    <td>
                                                        @if($student['avg_score'] < 60)
                                                            <span class="badge badge-danger">Low Score ({{ $student['avg_score'] }}%)</span>
                                                        @elseif($student['avg_score'] < 70)
                                                            <span class="badge badge-warning">Below Average ({{ $student['avg_score'] }}%)</span>
                                                        @endif
                                                        
                                                        @if($student['attendance_rate'] < 70)
                                                            <span class="badge badge-danger">Poor Attendance ({{ $student['attendance_rate'] }}%)</span>
                                                        @elseif($student['attendance_rate'] < 80)
                                                            <span class="badge badge-warning">Low Attendance ({{ $student['attendance_rate'] }}%)</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="#" class="btn btn-sm btn-warning"><i class="ti-comment"></i> Contact</a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="ti-face-smile" style="font-size: 3rem; color: #28a745;"></i>
                                        <h5 class="mt-3">All Students Doing Well!</h5>
                                        <p class="text-muted">No students need immediate attention.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Recent Assignment Submissions -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-files"></i> Recent Assignment Submissions</h4>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                @if(count($recentSubmissions) > 0)
                                    @foreach($recentSubmissions as $submission)
                                    <div class="student-list-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong>{{ $submission->student->full_name }}</strong> - {{ $submission->homeworkAssignment->class->name }}<br>
                                                <small class="text-muted">{{ $submission->homeworkAssignment->title }}</small>
                                            </div>
                                            <div class="text-right">
                                                @if($submission->status === 'graded')
                                                    <span class="badge badge-success">Graded</span>
                                                @elseif($submission->status === 'late')
                                                    <span class="badge badge-warning">Late</span>
                                                @else
                                                    <span class="badge badge-info">Submitted</span>
                                                @endif
                                                <br>
                                                <small class="text-muted">{{ $submission->submitted_date ? $submission->submitted_date->diffForHumans() : 'Not submitted' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-4">
                                        <p class="text-muted">No recent submissions.</p>
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
                            <p>MLC Classroom - Teacher Dashboard | Last Updated: <span id="date-time"></span></p>
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
        var classPerformanceData = {!! json_encode($classPerformanceData) !!};
        var attendanceTrendData = {!! json_encode($attendanceTrendData) !!};
    </script>
    <script src="{{ asset('assets/js/custom-chart-init.js') }}"></script>
@endpush
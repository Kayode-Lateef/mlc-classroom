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
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon">
                                    <i class="ti-user"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">My Students</div>
                                        <div class="stat-text">Total: <strong>342</strong></div>
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
                                        <div class="stat-heading">My Teachers</div>
                                        <div class="stat-text">Total: <strong>28</strong></div>
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
                                        <div class="stat-heading">Pending Tasks</div>
                                        <div class="stat-text">Count: <strong>15</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon">
                                    <i class="ti-calendar"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Today's Classes</div>
                                        <div class="stat-text">Count: <strong>12</strong></div>
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
                                    <i class="ti-check-box"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Present Today</div>
                                        <div class="stat-text">Count: <strong>318</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card bg-danger">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-close"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Absent Today</div>
                                        <div class="stat-text">Count: <strong>24</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card bg-warning">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-time"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Late Arrivals</div>
                                        <div class="stat-text">Count: <strong>8</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card bg-info">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-file"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Leave Requests</div>
                                        <div class="stat-text">Pending: <strong>5</strong></div>
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
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Class</th>
                                                <th>Subject</th>
                                                <th>Teacher</th>
                                                <th>Room</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>08:00 - 09:00</strong></td>
                                                <td><span class="badge badge-primary">Grade 10A</span></td>
                                                <td>Mathematics</td>
                                                <td>Mr. Anderson</td>
                                                <td>Room 101</td>
                                                <td><span class="badge badge-success">Completed</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>09:00 - 10:00</strong></td>
                                                <td><span class="badge badge-primary">Grade 11B</span></td>
                                                <td>Physics</td>
                                                <td>Dr. Williams</td>
                                                <td>Room 205</td>
                                                <td><span class="badge badge-info">In Progress</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>10:30 - 11:30</strong></td>
                                                <td><span class="badge badge-primary">Grade 9C</span></td>
                                                <td>English</td>
                                                <td>Ms. Thompson</td>
                                                <td>Room 103</td>
                                                <td><span class="badge badge-warning">Upcoming</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>11:30 - 12:30</strong></td>
                                                <td><span class="badge badge-primary">Grade 12A</span></td>
                                                <td>Chemistry</td>
                                                <td>Prof. Davis</td>
                                                <td>Lab 201</td>
                                                <td><span class="badge badge-warning">Upcoming</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>13:30 - 14:30</strong></td>
                                                <td><span class="badge badge-primary">Grade 10B</span></td>
                                                <td>Biology</td>
                                                <td>Dr. Martinez</td>
                                                <td>Lab 102</td>
                                                <td><span class="badge badge-warning">Upcoming</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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
                                <button class="btn btn-primary quick-action-btn">
                                    <i class="ti-plus"></i> Add Student
                                </button>
                                <button class="btn btn-success quick-action-btn">
                                    <i class="ti-clipboard"></i> Mark Attendance
                                </button>
                                <button class="btn btn-info quick-action-btn">
                                    <i class="ti-calendar"></i> Schedule Class
                                </button>
                                <button class="btn btn-warning quick-action-btn">
                                    <i class="ti-announcement"></i> Send Notice
                                </button>
                                <button class="btn btn-secondary quick-action-btn">
                                    <i class="ti-file"></i> View Reports
                                </button>
                            </div>
                        </div>

                        <!-- Priority Tasks -->
                        <div class="card alert mt-3">
                            <div class="card-header">
                                <h4><i class="ti-check-box"></i> Priority Tasks <span class="badge badge-danger">15</span></h4>
                            </div>
                            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                <div class="task-item task-priority-high">
                                    <input type="checkbox"> 
                                    <strong>Review leave requests</strong>
                                    <br><small class="text-muted">Due: Today, 5:00 PM</small>
                                </div>
                                <div class="task-item task-priority-high">
                                    <input type="checkbox"> 
                                    <strong>Approve exam schedules</strong>
                                    <br><small class="text-muted">Due: Today, 6:00 PM</small>
                                </div>
                                <div class="task-item task-priority-medium">
                                    <input type="checkbox"> 
                                    <strong>Review student applications</strong>
                                    <br><small class="text-muted">Due: Tomorrow</small>
                                </div>
                                <div class="task-item task-priority-medium">
                                    <input type="checkbox"> 
                                    <strong>Update class timetables</strong>
                                    <br><small class="text-muted">Due: Dec 15</small>
                                </div>
                                <div class="task-item task-priority-low">
                                    <input type="checkbox"> 
                                    <strong>Prepare monthly report</strong>
                                    <br><small class="text-muted">Due: Dec 20</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Overview & Teacher Performance -->
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
                                <div class="activity-item">
                                    <strong>Attendance Marked</strong>
                                    <p class="mb-0">Grade 10A - Mathematics class attendance recorded</p>
                                    <span class="activity-time"><i class="ti-time"></i> 10 minutes ago</span>
                                </div>
                                <div class="activity-item">
                                    <strong>New Student Enrolled</strong>
                                    <p class="mb-0">Emma Watson enrolled in Grade 11B</p>
                                    <span class="activity-time"><i class="ti-time"></i> 1 hour ago</span>
                                </div>
                                <div class="activity-item">
                                    <strong>Leave Request Approved</strong>
                                    <p class="mb-0">Leave request by John Smith approved</p>
                                    <span class="activity-time"><i class="ti-time"></i> 2 hours ago</span>
                                </div>
                                <div class="activity-item">
                                    <strong>Class Schedule Updated</strong>
                                    <p class="mb-0">Physics class moved to Lab 205</p>
                                    <span class="activity-time"><i class="ti-time"></i> 3 hours ago</span>
                                </div>
                                <div class="activity-item">
                                    <strong>Report Generated</strong>
                                    <p class="mb-0">Weekly attendance report created</p>
                                    <span class="activity-time"><i class="ti-time"></i> 4 hours ago</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications & Alerts -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-bell"></i> Notifications & Alerts <span class="badge badge-warning">12</span></h4>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <div class="alert alert-warning">
                                    <strong><i class="ti-alert"></i> Low Attendance Alert</strong>
                                    <p class="mb-0">Grade 9C has 72% attendance this week</p>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                                <div class="alert alert-info">
                                    <strong><i class="ti-file"></i> 5 Leave Requests Pending</strong>
                                    <p class="mb-0">Review and approve pending leave requests</p>
                                    <small class="text-muted">3 hours ago</small>
                                </div>
                                <div class="alert alert-success">
                                    <strong><i class="ti-check"></i> Exam Schedule Approved</strong>
                                    <p class="mb-0">Mid-term exam schedule has been approved</p>
                                    <small class="text-muted">5 hours ago</small>
                                </div>
                                <div class="alert alert-danger">
                                    <strong><i class="ti-close"></i> Deadline Approaching</strong>
                                    <p class="mb-0">Submit grade reports by Dec 15th</p>
                                    <small class="text-muted">1 day ago</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Performance Overview -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-bar-chart"></i> Student Performance Overview - My Classes</h4>
                                <div class="card-header-right-icon">
                                    <ul>
                                        <li class="card-option drop-menu">
                                            <i class="ti-settings" data-toggle="dropdown"></i>
                                            <ul class="card-option-dropdown dropdown-menu">
                                                <li><a href="#"><i class="ti-download"></i> Export Data</a></li>
                                                <li><a href="#"><i class="ti-eye"></i> View Details</a></li>
                                                <li><a href="#"><i class="ti-filter"></i> Filter</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
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
                                            <tr>
                                                <td>1</td>
                                                <td>
                                                    <strong>Alice Johnson</strong><br>
                                                    <small class="text-muted">ID: STD-001</small>
                                                </td>
                                                <td><span class="badge badge-primary">Grade 10A</span></td>
                                                <td><span class="badge badge-success">92%</span></td>
                                                <td><span class="badge badge-success">98%</span></td>
                                                <td><strong class="text-success">A</strong></td>
                                                <td><span class="badge badge-success">Excellent</span></td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info"><i class="ti-eye"></i></a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td>
                                                    <strong>Robert Brown</strong><br>
                                                    <small class="text-muted">ID: STD-002</small>
                                                </td>
                                                <td><span class="badge badge-primary">Grade 11B</span></td>
                                                <td><span class="badge badge-info">85%</span></td>
                                                <td><span class="badge badge-success">95%</span></td>
                                                <td><strong class="text-info">B+</strong></td>
                                                <td><span class="badge badge-info">Good</span></td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info"><i class="ti-eye"></i></a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td>
                                                    <strong>Emily Davis</strong><br>
                                                    <small class="text-muted">ID: STD-003</small>
                                                </td>
                                                <td><span class="badge badge-primary">Grade 9C</span></td>
                                                <td><span class="badge badge-warning">68%</span></td>
                                                <td><span class="badge badge-warning">82%</span></td>
                                                <td><strong class="text-warning">C+</strong></td>
                                                <td><span class="badge badge-warning">Average</span></td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info"><i class="ti-eye"></i></a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>4</td>
                                                <td>
                                                    <strong>Michael Wilson</strong><br>
                                                    <small class="text-muted">ID: STD-004</small>
                                                </td>
                                                <td><span class="badge badge-primary">Grade 10B</span></td>
                                                <td><span class="badge badge-danger">55%</span></td>
                                                <td><span class="badge badge-danger">75%</span></td>
                                                <td><strong class="text-danger">D</strong></td>
                                                <td><span class="badge badge-danger">Needs Attention</span></td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info"><i class="ti-eye"></i></a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Requests & Teacher Assignments -->
                <div class="row">
                    <!-- Leave Requests -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-file"></i> Pending Leave Requests <span class="badge badge-warning">5</span></h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Type</th>
                                                <th>Duration</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>John Smith</td>
                                                <td><span class="badge badge-primary">Student</span></td>
                                                <td>Dec 13-15 (3 days)</td>
                                                <td>
                                                    <button class="btn btn-sm btn-success"><i class="ti-check"></i></button>
                                                    <button class="btn btn-sm btn-danger"><i class="ti-close"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Prof. Anderson</td>
                                                <td><span class="badge badge-info">Teacher</span></td>
                                                <td>Dec 14 (1 day)</td>
                                                <td>
                                                    <button class="btn btn-sm btn-success"><i class="ti-check"></i></button>
                                                    <button class="btn btn-sm btn-danger"><i class="ti-close"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Sarah Wilson</td>
                                                <td><span class="badge badge-primary">Student</span></td>
                                                <td>Dec 16-17 (2 days)</td>
                                                <td>
                                                    <button class="btn btn-sm btn-success"><i class="ti-check"></i></button>
                                                    <button class="btn btn-sm btn-danger"><i class="ti-close"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Teacher Availability -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-user"></i> Teacher Availability (Today)</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Teacher Name</th>
                                                <th>Subject</th>
                                                <th>Status</th>
                                                <th>Next Class</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Mr. Anderson</td>
                                                <td>Mathematics</td>
                                                <td><span class="badge badge-success">Available</span></td>
                                                <td>2:00 PM</td>
                                            </tr>
                                            <tr>
                                                <td>Dr. Williams</td>
                                                <td>Physics</td>
                                                <td><span class="badge badge-warning">In Class</span></td>
                                                <td>Now - 10:00 AM</td>
                                            </tr>
                                            <tr>
                                                <td>Ms. Thompson</td>
                                                <td>English</td>
                                                <td><span class="badge badge-success">Available</span></td>
                                                <td>10:30 AM</td>
                                            </tr>
                                            <tr>
                                                <td>Prof. Davis</td>
                                                <td>Chemistry</td>
                                                <td><span class="badge badge-danger">On Leave</span></td>
                                                <td>-</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer">
                            <p>MLC Classroom - Admin Dashboard | Last Updated: <span id="date-time"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script src="{{ asset('assets/js/lib/chart-js/Chart.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/custom-chart-init.js') }}"></script>
@endpush
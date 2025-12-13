@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')

@endpush

@section('content')
    <div class="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8 p-r-0 title-margin-right">
                    <div class="page-header">
                        <div class="page-title">
                            <h1>Hello, <span>Welcome Here {{ auth()->user()->name }} </span></h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 p-l-0 title-margin-left">
                    <div class="page-header">
                        <div class="page-title">
                            <ol class="breadcrumb text-right">
                                <li><a href="#">Dashboard</a></li>
                                <li class="active">Home</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div id="main-content">
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
                                        <div class="stat-text">Total: 765</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon">
                                    <i class="ti-user"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Total Teacher</div>
                                        <div class="stat-text">Total: 24720</div>
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
                                        <div class="stat-heading">Active Classes</div>
                                        <div class="stat-text">Total: 765</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon">
                                    <i class="ti-check-box"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Total Admins</div>
                                        <div class="stat-text">Total: 24720</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3">
                        <div class="card bg-danger">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-alert"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Pending Approvals</div>
                                        <div class="stat-text">Count: <strong>5</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card bg-primary">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-calendar"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Total Subjects</div>
                                        <div class="stat-text">Count: <strong>28</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card bg-success">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-home"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Total Classrooms</div>
                                        <div class="stat-text">Count: <strong>28</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card bg-primary">
                            <div class="stat-widget-six">
                                <div class="stat-icon">
                                    <i class="ti-check-box"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Attendance Rate</div>
                                        <div class="stat-text">Average: <strong>94.5%</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Charts Section -->
                <div class="row">
                    <!-- Student Enrollment Trend -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Student Enrollment Trend (Last 6 Months)</h4>
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
                                <h4>User Distribution</h4>
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
                                <h4>Weekly Attendance Overview</h4>
                            </div>
                            <div class="card-body">
                                <div class="ct-bar-chart"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Class Performance Distribution</h4>
                            </div>
                            <div class="card-body">
                                <div class="ct-pie-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Status & Quick Actions -->
                <div class="row">
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
                                            <span>Database</span>
                                            <span class="badge badge-success system-status-badge">Online</span>
                                        </div>
                                    </li>
                                    <li class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Server Status</span>
                                            <span class="badge badge-success system-status-badge">Healthy</span>
                                        </div>
                                    </li>
                                    <li class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Backup Status</span>
                                            <span class="badge badge-success system-status-badge">Up to date</span>
                                        </div>
                                    </li>
                                    <li class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Storage Usage</span>
                                            <span class="badge badge-warning system-status-badge">68% Used</span>
                                        </div>
                                    </li>
                                    <li class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>System Version</span>
                                            <span class="badge badge-info system-status-badge">v2.5.1</span>
                                        </div>
                                    </li>
                                </ul>
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
                                <button class="btn btn-primary quick-action-btn">
                                    <i class="ti-plus"></i> Add New Student
                                </button>
                                <button class="btn btn-success quick-action-btn">
                                    <i class="ti-user"></i> Add New Teacher
                                </button>
                                <button class="btn btn-info quick-action-btn">
                                    <i class="ti-blackboard"></i> Create New Class
                                </button>
                                <button class="btn btn-warning quick-action-btn">
                                    <i class="ti-announcement"></i> Send Announcement
                                </button>
                                <button class="btn btn-secondary quick-action-btn">
                                    <i class="ti-file"></i> Generate Report
                                </button>
                                <button class="btn btn-dark quick-action-btn">
                                    <i class="ti-settings"></i> System Settings
                                </button>
                            </div>
                        </div>
                    </div>

                                      <!-- Recent System Alerts -->
                    <div class="col-lg-4">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-bell"></i> System Alerts</h4>
                            </div>
                            <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                                <div class="alert-item">
                                    <strong>Low Storage Warning</strong>
                                    <p class="mb-0" style="font-size: 0.85rem;">Storage is 68% full. Consider cleanup.</p>
                                    <span class="activity-time">2 hours ago</span>
                                </div>
                                <div class="alert-item">
                                    <strong>Backup Completed</strong>
                                    <p class="mb-0" style="font-size: 0.85rem;">Daily backup completed successfully.</p>
                                    <span class="activity-time">5 hours ago</span>
                                </div>
                                <div class="alert-item">
                                    <strong>New User Registration</strong>
                                    <p class="mb-0" style="font-size: 0.85rem;">3 new teachers registered. Pending approval.</p>
                                    <span class="activity-time">1 day ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                               <!-- Recent Activities & Pending Approvals -->
                <div class="row">
                    <!-- Recent Activities -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4>Recent System Activities</h4>
                                <div class="card-header-right-icon">
                                    <ul>
                                        <li><a href="#"><i class="ti-reload"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <div class="activity-item">
                                    <strong>New Student Enrolled</strong>
                                    <p class="mb-0">John Doe enrolled in Grade 10A</p>
                                    <span class="activity-time"><i class="ti-time"></i> 15 minutes ago</span>
                                </div>
                                <div class="activity-item">
                                    <strong>Teacher Account Created</strong>
                                    <p class="mb-0">Sarah Johnson added as Mathematics teacher</p>
                                    <span class="activity-time"><i class="ti-time"></i> 1 hour ago</span>
                                </div>
                                <div class="activity-item">
                                    <strong>Class Schedule Updated</strong>
                                    <p class="mb-0">Biology class time changed for Grade 11B</p>
                                    <span class="activity-time"><i class="ti-time"></i> 2 hours ago</span>
                                </div>
                                <div class="activity-item">
                                    <strong>Report Generated</strong>
                                    <p class="mb-0">Monthly attendance report created by Admin</p>
                                    <span class="activity-time"><i class="ti-time"></i> 3 hours ago</span>
                                </div>
                                <div class="activity-item">
                                    <strong>System Backup</strong>
                                    <p class="mb-0">Automated daily backup completed successfully</p>
                                    <span class="activity-time"><i class="ti-time"></i> 5 hours ago</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Approvals -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4>Pending Approvals <span class="badge badge-danger">8</span></h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Name</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="badge badge-info">Teacher</span></td>
                                                <td>Michael Brown</td>
                                                <td>Dec 10, 2024</td>
                                                <td>
                                                    <button class="btn btn-sm btn-success"><i class="ti-check"></i></button>
                                                    <button class="btn btn-sm btn-danger"><i class="ti-close"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge badge-primary">Student</span></td>
                                                <td>Emily Davis</td>
                                                <td>Dec 11, 2024</td>
                                                <td>
                                                    <button class="btn btn-sm btn-success"><i class="ti-check"></i></button>
                                                    <button class="btn btn-sm btn-danger"><i class="ti-close"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge badge-warning">Leave</span></td>
                                                <td>Prof. Anderson</td>
                                                <td>Dec 11, 2024</td>
                                                <td>
                                                    <button class="btn btn-sm btn-success"><i class="ti-check"></i></button>
                                                    <button class="btn btn-sm btn-danger"><i class="ti-close"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge badge-info">Teacher</span></td>
                                                <td>Dr. Smith</td>
                                                <td>Dec 12, 2024</td>
                                                <td>
                                                    <button class="btn btn-sm btn-success"><i class="ti-check"></i></button>
                                                    <button class="btn btn-sm btn-danger"><i class="ti-close"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="#" class="btn btn-primary btn-sm">View All Approvals</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Recent Registrations -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header">
                                <h4>Recent Registrations (Last 7 Days)</h4>
                                <div class="card-header-right-icon">
                                    <ul>
                                        <li class="card-option drop-menu">
                                            <i class="ti-settings" data-toggle="dropdown"></i>
                                            <ul class="card-option-dropdown dropdown-menu">
                                                <li><a href="#"><i class="ti-download"></i> Export</a></li>
                                                <li><a href="#"><i class="ti-eye"></i> View All</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
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
                                            <tr>
                                                <td>1</td>
                                                <td>Alice Johnson</td>
                                                <td><span class="badge badge-primary">Student</span></td>
                                                <td>alice.j@email.com</td>
                                                <td>Dec 12, 2024</td>
                                                <td><span class="badge badge-success">Approved</span></td>
                                                <td><a href="#" class="btn btn-sm btn-info"><i class="ti-eye"></i></a></td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td>Robert Wilson</td>
                                                <td><span class="badge badge-info">Teacher</span></td>
                                                <td>r.wilson@email.com</td>
                                                <td>Dec 11, 2024</td>
                                                <td><span class="badge badge-warning">Pending</span></td>
                                                <td><a href="#" class="btn btn-sm btn-info"><i class="ti-eye"></i></a></td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td>Mary Thompson</td>
                                                <td><span class="badge badge-primary">Student</span></td>
                                                <td>m.thompson@email.com</td>
                                                <td>Dec 10, 2024</td>
                                                <td><span class="badge badge-success">Approved</span></td>
                                                <td><a href="#" class="btn btn-sm btn-info"><i class="ti-eye"></i></a></td>
                                            </tr>
                                            <tr>
                                                <td>4</td>
                                                <td>James Miller</td>
                                                <td><span class="badge badge-secondary">Admin</span></td>
                                                <td>j.miller@email.com</td>
                                                <td>Dec 09, 2024</td>
                                                <td><span class="badge badge-success">Approved</span></td>
                                                <td><a href="#" class="btn btn-sm btn-info"><i class="ti-eye"></i></a></td>
                                            </tr>
                                            <tr>
                                                <td>5</td>
                                                <td>Patricia Garcia</td>
                                                <td><span class="badge badge-info">Teacher</span></td>
                                                <td>p.garcia@email.com</td>
                                                <td>Dec 08, 2024</td>
                                                <td><span class="badge badge-danger">Rejected</span></td>
                                                <td><a href="#" class="btn btn-sm btn-info"><i class="ti-eye"></i></a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Progress Overview -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-bar-chart"></i> Recent Academic Progress Report</h4>
                                <div class="card-header-right-icon">
                                    <ul>
                                        <li class="card-option drop-menu">
                                            <i class="ti-settings" data-toggle="dropdown"></i>
                                            <ul class="card-option-dropdown dropdown-menu">
                                                <li><a href="#"><i class="ti-download"></i> Download Report</a></li>
                                                <li><a href="#"><i class="ti-printer"></i> Print Report</a></li>
                                                <li><a href="#"><i class="ti-eye"></i> View Full Report</a></li>
                                                <li><a href="#"><i class="ti-filter"></i> Filter by Class</a></li>
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
                                                <th>Subject</th>
                                                <th>Test Score</th>
                                                <th>Assignment</th>
                                                <th>Attendance</th>
                                                <th>Overall Grade</th>
                                                <th>Performance</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td>
                                                    <strong>John Smith</strong><br>
                                                    <small class="text-muted">ID: STD-2024-001</small>
                                                </td>
                                                <td><span class="badge badge-primary">Grade 10A</span></td>
                                                <td>Mathematics</td>
                                                <td>
                                                    <span class="badge badge-success">92%</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">88%</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">95%</span>
                                                </td>
                                                <td>
                                                    <strong class="text-success">A</strong>
                                                </td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-success progress-bar-striped active w-90" role="progressbar" aria-valuenow="91" aria-valuemin="0" aria-valuemax="100">
                                                            91%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="ti-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td>
                                                    <strong>Emily Johnson</strong><br>
                                                    <small class="text-muted">ID: STD-2024-002</small>
                                                </td>
                                                <td><span class="badge badge-primary">Grade 11B</span></td>
                                                <td>Physics</td>
                                                <td>
                                                    <span class="badge badge-success">85%</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-warning">78%</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">92%</span>
                                                </td>
                                                <td>
                                                    <strong class="text-success">B+</strong>
                                                </td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-info progress-bar-striped active w-80" role="progressbar" aria-valuenow="83" aria-valuemin="0" aria-valuemax="100">
                                                            83%
                                                        </div>
                                                    </div>
                                                  
                                                </td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="ti-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td>
                                                    <strong>Michael Brown</strong><br>
                                                    <small class="text-muted">ID: STD-2024-003</small>
                                                </td>
                                                <td><span class="badge badge-primary">Grade 9C</span></td>
                                                <td>English</td>
                                                <td>
                                                    <span class="badge badge-warning">72%</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-warning">75%</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-warning">88%</span>
                                                </td>
                                                <td>
                                                    <strong class="text-warning">B</strong>
                                                </td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-warning progress-bar-striped active w-75" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                                                            75%
                                                        </div>
                                                    </div>
                                                   
                                                </td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="ti-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>4</td>
                                                <td>
                                                    <strong>Sarah Davis</strong><br>
                                                    <small class="text-muted">ID: STD-2024-004</small>
                                                </td>
                                                <td><span class="badge badge-primary">Grade 12A</span></td>
                                                <td>Chemistry</td>
                                                <td>
                                                    <span class="badge badge-success">95%</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">90%</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">98%</span>
                                                </td>
                                                <td>
                                                    <strong class="text-success">A+</strong>
                                                </td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-success progress-bar-striped active w-40" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                                                            40%
                                                        </div>
                                                    </div>
                                                  
                                                </td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="ti-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>5</td>
                                                <td>
                                                    <strong>David Wilson</strong><br>
                                                    <small class="text-muted">ID: STD-2024-005</small>
                                                </td>
                                                <td><span class="badge badge-primary">Grade 10B</span></td>
                                                <td>Biology</td>
                                                <td>
                                                    <span class="badge badge-danger">58%</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-warning">65%</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-warning">82%</span>
                                                </td>
                                                <td>
                                                    <strong class="text-danger">C</strong>
                                                </td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-danger progress-bar-striped active w-60" role="progressbar" aria-valuenow="62" aria-valuemin="0" aria-valuemax="100">
                                                            62%
                                                        </div>
                                                    </div>
                                                  
                                                </td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="ti-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>6</td>
                                                <td>
                                                    <strong>Lisa Anderson</strong><br>
                                                    <small class="text-muted">ID: STD-2024-006</small>
                                                </td>
                                                <td><span class="badge badge-primary">Grade 11A</span></td>
                                                <td>History</td>
                                                <td>
                                                    <span class="badge badge-success">88%</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">85%</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">94%</span>
                                                </td>
                                                <td>
                                                    <strong class="text-success">B+</strong>
                                                </td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-info progress-bar-striped active w-90" role="progressbar" aria-valuenow="87" aria-valuemin="0" aria-valuemax="100">
                                                            87%
                                                        </div>
                                                    </div>
                                                   
                                                </td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="ti-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="#" class="btn btn-primary">View Complete Progress Report</a>
                                    <a href="#" class="btn btn-success"><i class="ti-download"></i> Export to Excel</a>
                                    <a href="#" class="btn btn-info"><i class="ti-printer"></i> Print Report</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Summary Cards -->
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5>Excellent Performance</h5>
                                <h2 class="text-success">245 <small>Students</small></h2>
                                <p class="text-muted">(≥ 90% Overall)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5>Good Performance</h5>
                                <h2 class="text-info">485 <small>Students</small></h2>
                                <p class="text-muted">(75% - 89%)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5>Average Performance</h5>
                                <h2 class="text-warning">385 <small>Students</small></h2>
                                <p class="text-muted">(60% - 74%)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5>Needs Attention</h5>
                                <h2 class="text-danger">130 <small>Students</small></h2>
                                <p class="text-muted">(< 60%)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Class-wise Performance Comparison -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="ti-stats-up"></i> Class-wise Performance Comparison</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="classPerformanceChart"></canvas>
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
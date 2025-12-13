@extends('layouts.app')

@section('title', 'Teacher Dashboard')

@push('styles')

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
                            <p class="text-muted">{{ date('l, F d, Y') }}</p>
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
                                        <div class="stat-text">Total: <strong>156</strong></div>
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
                                        <div class="stat-text">Total: <strong>6</strong></div>
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
                                        <div class="stat-text">Count: <strong>23</strong></div>
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
                                        <div class="stat-text">Active: <strong>8</strong></div>
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
                                        <div class="stat-text">Count: <strong>4</strong></div>
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
                                        <div class="stat-text">Today: <strong>94%</strong></div>
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
                                        <div class="stat-text">This Week: <strong>3</strong></div>
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
                                        <div class="stat-text">Today: <strong>9</strong></div>
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
                                <!-- Completed Class -->
                                <div class="class-card completed">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="mb-1"><i class="ti-check-box text-success"></i> Grade 10A - Mathematics</h5>
                                            <p class="mb-1"><i class="ti-time"></i> <strong>08:00 AM - 09:00 AM</strong> | Room 101</p>
                                            <p class="mb-0 text-muted">Topic: Quadratic Equations</p>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <span class="badge badge-secondary">Completed</span><br>
                                            <small>Attendance: 28/30 (93%)</small><br>
                                            <a href="#" class="btn btn-sm btn-info mt-2"><i class="ti-eye"></i> View Details</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Active Class -->
                                <div class="class-card active">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="mb-1"><i class="ti-video-clapper text-success"></i> Grade 11B - Advanced Math</h5>
                                            <p class="mb-1"><i class="ti-time"></i> <strong>09:30 AM - 10:30 AM</strong> | Room 205</p>
                                            <p class="mb-0 text-muted">Topic: Calculus - Derivatives</p>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <span class="badge badge-success">In Progress</span><br>
                                            <small>Started 15 mins ago</small><br>
                                            <a href="#" class="btn btn-sm btn-success mt-2"><i class="ti-clipboard"></i> Mark Attendance</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Upcoming Classes -->
                                <div class="class-card upcoming">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="mb-1"><i class="ti-alarm-clock text-warning"></i> Grade 9C - Basic Mathematics</h5>
                                            <p class="mb-1"><i class="ti-time"></i> <strong>11:00 AM - 12:00 PM</strong> | Room 103</p>
                                            <p class="mb-0 text-muted">Topic: Geometry - Triangles</p>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <span class="badge badge-warning">Upcoming</span><br>
                                            <small>Starts in 30 mins</small><br>
                                            <a href="#" class="btn btn-sm btn-primary mt-2"><i class="ti-eye"></i> Prepare</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="class-card upcoming">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="mb-1"><i class="ti-alarm-clock text-warning"></i> Grade 12A - Statistics</h5>
                                            <p class="mb-1"><i class="ti-time"></i> <strong>02:00 PM - 03:00 PM</strong> | Room 202</p>
                                            <p class="mb-0 text-muted">Topic: Probability Distributions</p>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <span class="badge badge-warning">Upcoming</span><br>
                                            <small>Starts in 4 hours</small><br>
                                            <a href="#" class="btn btn-sm btn-primary mt-2"><i class="ti-eye"></i> Prepare</a>
                                        </div>
                                    </div>
                                </div>
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
                                <button class="btn btn-primary quick-action-btn">
                                    <i class="ti-clipboard"></i> Mark Attendance
                                </button>
                                <button class="btn btn-success quick-action-btn">
                                    <i class="ti-pencil-alt"></i> Grade Assignments
                                </button>
                                <button class="btn btn-info quick-action-btn">
                                    <i class="ti-write"></i> Create Assignment
                                </button>
                                <button class="btn btn-warning quick-action-btn">
                                    <i class="ti-calendar"></i> View Lesson Plan
                                </button>
                                <button class="btn btn-secondary quick-action-btn">
                                    <i class="ti-bar-chart"></i> Student Reports
                                </button>
                                <button class="btn btn-dark quick-action-btn">
                                    <i class="ti-announcement"></i> Send Notice
                                </button>
                            </div>
                        </div>

                        <!-- Important Reminders -->
                        <div class="card alert mt-3">
                            <div class="card-header">
                                <h4><i class="ti-bell"></i> Reminders <span class="badge badge-danger">5</span></h4>
                            </div>
                            <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                                <div class="alert alert-danger">
                                    <strong><i class="ti-alert"></i> Urgent!</strong>
                                    <p class="mb-0">Grade 10A test papers due by 3 PM today</p>
                                </div>
                                <div class="alert alert-warning">
                                    <strong><i class="ti-time"></i> Deadline</strong>
                                    <p class="mb-0">Submit lesson plans for next week by Dec 15</p>
                                </div>
                                <div class="alert alert-info">
                                    <strong><i class="ti-calendar"></i> Upcoming</strong>
                                    <p class="mb-0">Parent-Teacher meeting on Dec 18</p>
                                </div>
                                <div class="alert alert-success">
                                    <strong><i class="ti-check"></i> Complete</strong>
                                    <p class="mb-0">3 assignments graded today</p>
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
                                            <tr>
                                                <td>
                                                    <strong>John Smith</strong><br>
                                                    <small class="text-muted">STD-001</small>
                                                </td>
                                                <td><span class="badge badge-primary">10A</span></td>
                                                <td><span class="badge badge-success grade-badge">92%</span></td>
                                                <td><span class="badge badge-success grade-badge">88%</span></td>
                                                <td><span class="badge badge-success">95%</span></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <strong>Emily Davis</strong><br>
                                                    <small class="text-muted">STD-002</small>
                                                </td>
                                                <td><span class="badge badge-primary">11B</span></td>
                                                <td><span class="badge badge-info grade-badge">85%</span></td>
                                                <td><span class="badge badge-info grade-badge">82%</span></td>
                                                <td><span class="badge badge-success">92%</span></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <strong>Michael Brown</strong><br>
                                                    <small class="text-muted">STD-003</small>
                                                </td>
                                                <td><span class="badge badge-primary">9C</span></td>
                                                <td><span class="badge badge-warning grade-badge">68%</span></td>
                                                <td><span class="badge badge-warning grade-badge">70%</span></td>
                                                <td><span class="badge badge-warning">78%</span></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <strong>Sarah Wilson</strong><br>
                                                    <small class="text-muted">STD-004</small>
                                                </td>
                                                <td><span class="badge badge-primary">12A</span></td>
                                                <td><span class="badge badge-success grade-badge">95%</span></td>
                                                <td><span class="badge badge-success grade-badge">94%</span></td>
                                                <td><span class="badge badge-success">98%</span></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <strong>David Lee</strong><br>
                                                    <small class="text-muted">STD-005</small>
                                                </td>
                                                <td><span class="badge badge-primary">10B</span></td>
                                                <td><span class="badge badge-danger grade-badge">52%</span></td>
                                                <td><span class="badge badge-danger grade-badge">58%</span></td>
                                                <td><span class="badge badge-danger">65%</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="#" class="btn btn-primary btn-sm">View All Students</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Assignments to Grade -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-pencil"></i> Pending Assignments to Grade <span class="badge badge-warning">23</span></h4>
                            </div>
                            <div class="card-body" style="max-height: 450px; overflow-y: auto;">
                                <div class="assignment-item assignment-due-soon">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">Quadratic Equations Worksheet</h6>
                                            <p class="mb-1"><span class="badge badge-primary">Grade 10A</span></p>
                                            <small class="text-muted">Submitted: 28 students | Graded: 5</small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-danger">Due Today</span><br>
                                            <a href="#" class="btn btn-sm btn-success mt-2">Grade Now</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="assignment-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">Calculus Problem Set #5</h6>
                                            <p class="mb-1"><span class="badge badge-primary">Grade 11B</span></p>
                                            <small class="text-muted">Submitted: 25 students | Graded: 10</small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-warning">Due Tomorrow</span><br>
                                            <a href="#" class="btn btn-sm btn-primary mt-2">Grade Now</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="assignment-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">Geometry - Triangle Properties</h6>
                                            <p class="mb-1"><span class="badge badge-primary">Grade 9C</span></p>
                                            <small class="text-muted">Submitted: 22 students | Graded: 8</small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-info">Due Dec 16</span><br>
                                            <a href="#" class="btn btn-sm btn-primary mt-2">Grade Now</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="assignment-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">Statistics Quiz #3</h6>
                                            <p class="mb-1"><span class="badge badge-primary">Grade 12A</span></p>
                                            <small class="text-muted">Submitted: 30 students | Graded: 0</small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-info">Due Dec 18</span><br>
                                            <a href="#" class="btn btn-sm btn-primary mt-2">Grade Now</a>
                                        </div>
                                    </div>
                                </div>
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
                                <h4>Attendance Trend - This Month</h4>
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
                                <h4><i class="ti-alert"></i> Students Needing Attention <span class="badge badge-danger">12</span></h4>
                            </div>
                            <div class="card-body">
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
                                            <tr>
                                                <td>David Lee</td>
                                                <td><span class="badge badge-primary">10B</span></td>
                                                <td><span class="badge badge-danger">Low Score (52%)</span></td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-warning"><i class="ti-comment"></i> Contact</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Lisa Wang</td>
                                                <td><span class="badge badge-primary">9C</span></td>
                                                <td><span class="badge badge-warning">Poor Attendance (60%)</span></td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-warning"><i class="ti-comment"></i> Contact</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Mark Johnson</td>
                                                <td><span class="badge badge-primary">11B</span></td>
                                                <td><span class="badge badge-danger">Missing 3 Assignments</span></td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-warning"><i class="ti-comment"></i> Contact</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Anna Chen</td>
                                                <td><span class="badge badge-primary">10A</span></td>
                                                <td><span class="badge badge-warning">Declining Grades</span></td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-warning"><i class="ti-comment"></i> Contact</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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
                                <div class="student-list-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>John Smith</strong> - Grade 10A<br>
                                            <small class="text-muted">Quadratic Equations Worksheet</small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-success">Submitted</span><br>
                                            <small class="text-muted">5 mins ago</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="student-list-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>Emma Wilson</strong> - Grade 11B<br>
                                            <small class="text-muted">Calculus Problem Set #5</small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-success">Submitted</span><br>
                                            <small class="text-muted">15 mins ago</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="student-list-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>Oliver Brown</strong> - Grade 9C<br>
                                            <small class="text-muted">Geometry - Triangle Properties</small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-success">Submitted</span><br>
                                            <small class="text-muted">1 hour ago</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="student-list-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>Sophia Garcia</strong> - Grade 12A<br>
                                            <small class="text-muted">Statistics Quiz #3</small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-warning">Late</span><br>
                                            <small class="text-muted">2 hours ago</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="student-list-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>James Martinez</strong> - Grade 10A<br>
                                            <small class="text-muted">Quadratic Equations Worksheet</small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-success">Submitted</span><br>
                                        <small class="text-muted">3 hours ago</small>
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
    <script src="{{ asset('assets/js/custom-chart-init.js') }}"></script>
@endpush
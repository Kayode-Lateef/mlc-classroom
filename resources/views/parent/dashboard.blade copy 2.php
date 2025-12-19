@extends('layouts.app')

@section('title', 'Parent Dashboard')

@push('styles')
    <style>
        /* Custom styles for parent dashboard */
        .child-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .child-card:hover {
            border-color: #007bff;
            box-shadow: 0 5px 15px rgba(0,123,255,0.2);
        }
        
        .child-card.active {
            border-color: #28a745;
            background-color: #f8fff9;
        }
        
        .subject-card {
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        
        .attendance-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .present {
            background-color: #28a745;
        }
        
        .absent {
            background-color: #dc3545;
        }
        
        .late {
            background-color: #ffc107;
        }
        
        .announcement-item {
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }
        
        .event-item {
            padding: 12px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
        }
        
        
        .progress-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto;
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
                            <h1>Parent Dashboard <span>Welcome, {{ auth()->user()->name }}</span></h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 p-l-0 title-margin-left">
                    <div class="page-header">
                        <div class="page-title">
                            <ol class="breadcrumb text-right">
                                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="active">My Children</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div id="main-content">
                <!-- My Children Overview -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-user"></i> My Children</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Child 1 -->
                                    <div class="col-lg-6">
                                        <div class="child-card active">
                                            <div class="row">
                                                <div class="col-md-3 text-center">
                                                    <div class="progress-circle" style="background-color: #d4edda; color: #28a745;">
                                                        88%
                                                    </div>
                                                    <p class="mt-2 mb-0"><strong>Overall Grade</strong></p>
                                                </div>
                                                <div class="col-md-9">
                                                    <h4>Sarah Johnson</h4>
                                                    <p class="mb-1">
                                                        <span class="badge badge-primary">Grade 10A</span>
                                                        <span class="badge badge-success">Active</span>
                                                    </p>
                                                    <p class="mb-1"><strong>Student ID:</strong> STD-2024-001</p>
                                                    <p class="mb-1"><strong>Class Teacher:</strong> Mr. Anderson</p>
                                                    <div class="mt-3">
                                                        <a href="#sarah-details" class="btn btn-sm btn-primary">View Details</a>
                                                        <a href="#" class="btn btn-sm btn-info">Contact Teacher</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Child 2 -->
                                    <div class="col-lg-6">
                                        <div class="child-card">
                                            <div class="row">
                                                <div class="col-md-3 text-center">
                                                    <div class="progress-circle" style="background-color: #fff3cd; color: #ffc107;">
                                                        75%
                                                    </div>
                                                    <p class="mt-2 mb-0"><strong>Overall Grade</strong></p>
                                                </div>
                                                <div class="col-md-9">
                                                    <h4>Michael Johnson</h4>
                                                    <p class="mb-1">
                                                        <span class="badge badge-primary">Grade 7B</span>
                                                        <span class="badge badge-success">Active</span>
                                                    </p>
                                                    <p class="mb-1"><strong>Student ID:</strong> STD-2024-045</p>
                                                    <p class="mb-1"><strong>Class Teacher:</strong> Ms. Thompson</p>
                                                    <div class="mt-3">
                                                        <a href="#michael-details" class="btn btn-sm btn-primary">View Details</a>
                                                        <a href="#" class="btn btn-sm btn-info">Contact Teacher</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Overview Stats -->
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon">
                                    <i class="ti-calendar"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Attendance Rate</div>
                                        <div class="stat-text">Average: <strong>94%</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon">
                                    <i class="ti-book"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Active Assignments</div>
                                        <div class="stat-text">Count: <strong>6</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon">
                                    <i class="ti-alert"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Pending Items</div>
                                        <div class="stat-text">Count: <strong>3</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon">
                                    <i class="ti-announcement"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">New Messages</div>
                                        <div class="stat-text">Count: <strong>2</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sarah's Detailed View -->
                <div class="row" id="sarah-details">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-user"></i> Sarah Johnson - Grade 10A - Academic Performance</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Subject Performance -->
                                    <div class="col-lg-6">
                                        <h5 class="mb-3">Subject Performance</h5>
                                        <div class="subject-card">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0">Mathematics</h6>
                                                    <small class="text-muted">Teacher: Mr. Anderson</small>
                                                </div>
                                                <div class="text-right">
                                                    <span class="badge badge-success" style="font-size: 1.1rem;">92%</span>
                                                    <br><small>Grade: A</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="subject-card" style="border-left-color: #28a745;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0">Physics</h6>
                                                    <small class="text-muted">Teacher: Dr. Williams</small>
                                                </div>
                                                <div class="text-right">
                                                    <span class="badge badge-success" style="font-size: 1.1rem;">88%</span>
                                                    <br><small>Grade: B+</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="subject-card" style="border-left-color: #ffc107;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0">English</h6>
                                                    <small class="text-muted">Teacher: Ms. Davis</small>
                                                </div>
                                                <div class="text-right">
                                                    <span class="badge badge-warning" style="font-size: 1.1rem;">78%</span>
                                                    <br><small>Grade: B</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="subject-card" style="border-left-color: #28a745;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0">Chemistry</h6>
                                                    <small class="text-muted">Teacher: Prof. Martinez</small>
                                                </div>
                                                <div class="text-right">
                                                    <span class="badge badge-success" style="font-size: 1.1rem;">90%</span>
                                                    <br><small>Grade: A-</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="subject-card" style="border-left-color: #17a2b8;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0">History</h6>
                                                    <small class="text-muted">Teacher: Mr. Thompson</small>
                                                </div>
                                                <div class="text-right">
                                                    <span class="badge badge-info" style="font-size: 1.1rem;">85%</span>
                                                    <br><small>Grade: B+</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Attendance & Assignments -->
                                    <div class="col-lg-6">
                                        <h5 class="mb-3">Recent Attendance (Last 10 Days)</h5>
                                        <div class="mb-4">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="attendance-indicator present"></span>
                                                <span class="ml-2">Dec 13 - Present</span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="attendance-indicator present"></span>
                                                <span class="ml-2">Dec 12 - Present</span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="attendance-indicator present"></span>
                                                <span class="ml-2">Dec 11 - Present</span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="attendance-indicator late"></span>
                                                <span class="ml-2">Dec 10 - Late (15 mins)</span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="attendance-indicator present"></span>
                                                <span class="ml-2">Dec 9 - Present</span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="attendance-indicator present"></span>
                                                <span class="ml-2">Dec 6 - Present</span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="attendance-indicator absent"></span>
                                                <span class="ml-2">Dec 5 - Absent (Sick Leave)</span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="attendance-indicator present"></span>
                                                <span class="ml-2">Dec 4 - Present</span>
                                            </div>
                                            <p class="mt-3">
                                                <strong>This Month:</strong> 
                                                <span class="badge badge-success">95% Attendance</span>
                                            </p>
                                        </div>

                                        <h5 class="mb-3">Pending Assignments</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Subject</th>
                                                        <th>Assignment</th>
                                                        <th>Due Date</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Math</td>
                                                        <td>Quadratic Equations</td>
                                                        <td>Dec 15</td>
                                                        <td><span class="badge badge-success">Submitted</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Physics</td>
                                                        <td>Newton's Laws Lab</td>
                                                        <td>Dec 18</td>
                                                        <td><span class="badge badge-warning">In Progress</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td>English</td>
                                                        <td>Essay - Shakespeare</td>
                                                        <td>Dec 20</td>
                                                        <td><span class="badge badge-info">Not Started</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Charts -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Sarah's Progress Trend (Last 4 Months)</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="progressTrendChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Subject Performance Comparison</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="subjectComparisonChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- School Announcements & Upcoming Events -->
                <div class="row">
                    <!-- School Announcements -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-announcement"></i> School Announcements</h4>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <div class="announcement-item">
                                    <h6><i class="ti-info-alt"></i> Winter Break Schedule</h6>
                                    <p class="mb-1">School will be closed from Dec 23 to Jan 5 for winter break. Classes resume on Jan 6, 2025.</p>
                                    <small class="text-muted">Posted: Dec 10, 2024</small>
                                </div>

                                <div class="announcement-item">
                                    <h6><i class="ti-calendar"></i> Parent-Teacher Conference</h6>
                                    <p class="mb-1">Individual meetings scheduled for Dec 18-19. Please check your email for your appointment time.</p>
                                    <small class="text-muted">Posted: Dec 8, 2024</small>
                                </div>

                                <div class="announcement-item">
                                    <h6><i class="ti-clipboard"></i> Mid-Term Exam Schedule</h6>
                                    <p class="mb-1">Mid-term exams will be held from Jan 15-22. Detailed schedule has been emailed to all parents.</p>
                                    <small class="text-muted">Posted: Dec 5, 2024</small>
                                </div>

                                <div class="announcement-item">
                                    <h6><i class="ti-book"></i> Library Books Due</h6>
                                    <p class="mb-1">All library books must be returned by Dec 20. Late fees apply after this date.</p>
                                    <small class="text-muted">Posted: Dec 3, 2024</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-flag-alt"></i> Upcoming Events</h4>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <div class="event-item">
                                    <h6><i class="ti-calendar"></i> Parent-Teacher Meeting</h6>
                                    <p class="mb-1"><strong>Date:</strong> December 18, 2024</p>
                                    <p class="mb-1"><strong>Time:</strong> 3:00 PM - 6:00 PM</p>
                                    <p class="mb-0"><strong>Location:</strong> Main Hall</p>
                                </div>

                                <div class="event-item">
                                    <h6><i class="ti-gift"></i> Annual Science Fair</h6>
                                    <p class="mb-1"><strong>Date:</strong> January 10, 2025</p>
                                    <p class="mb-1"><strong>Time:</strong> 10:00 AM - 2:00 PM</p>
                                    <p class="mb-0"><strong>Location:</strong> School Gymnasium</p>
                                </div>

                                <div class="event-item">
                                    <h6><i class="ti-medall"></i> Sports Day</h6>
                                    <p class="mb-1"><strong>Date:</strong> January 25, 2025</p>
                                    <p class="mb-1"><strong>Time:</strong> 8:00 AM - 4:00 PM</p>
                                    <p class="mb-0"><strong>Location:</strong> School Sports Ground</p>
                                </div>

                                <div class="event-item">
                                    <h6><i class="ti-music"></i> Annual Day Celebration</h6>
                                    <p class="mb-1"><strong>Date:</strong> February 14, 2025</p>
                                    <p class="mb-1"><strong>Time:</strong> 5:00 PM - 8:00 PM</p>
                                    <p class="mb-0"><strong>Location:</strong> School Auditorium</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Grades & Homework Status -->
                <div class="row">
                    <!-- Recent Grades & Test Results -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-write"></i> Recent Grades & Test Results</h4>
                                <div class="card-header-right-icon">
                                    <ul>
                                        <li><a href="#"><i class="ti-reload"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="mb-3">Sarah Johnson - Grade 10A</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Test/Assignment</th>
                                                <th>Score</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Mathematics</td>
                                                <td>Quadratic Equations Test</td>
                                                <td><span class="badge badge-success">92%</span></td>
                                                <td>Dec 10, 2024</td>
                                            </tr>
                                            <tr>
                                                <td>Physics</td>
                                                <td>Newton's Laws Quiz</td>
                                                <td><span class="badge badge-success">88%</span></td>
                                                <td>Dec 8, 2024</td>
                                            </tr>
                                            <tr>
                                                <td>English</td>
                                                <td>Essay - Shakespeare</td>
                                                <td><span class="badge badge-warning">78%</span></td>
                                                <td>Dec 7, 2024</td>
                                            </tr>
                                            <tr>
                                                <td>Chemistry</td>
                                                <td>Lab Report #3</td>
                                                <td><span class="badge badge-success">90%</span></td>
                                                <td>Dec 6, 2024</td>
                                            </tr>
                                            <tr>
                                                <td>History</td>
                                                <td>World War II Project</td>
                                                <td><span class="badge badge-info">85%</span></td>
                                                <td>Dec 5, 2024</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <hr>

                                <h5 class="mb-3 mt-4">Michael Johnson - Grade 7B</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Test/Assignment</th>
                                                <th>Score</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Science</td>
                                                <td>Biology Quiz</td>
                                                <td><span class="badge badge-warning">72%</span></td>
                                                <td>Dec 11, 2024</td>
                                            </tr>
                                            <tr>
                                                <td>English</td>
                                                <td>Reading Comprehension</td>
                                                <td><span class="badge badge-info">80%</span></td>
                                                <td>Dec 9, 2024</td>
                                            </tr>
                                            <tr>
                                                <td>Mathematics</td>
                                                <td>Fractions Test</td>
                                                <td><span class="badge badge-warning">75%</span></td>
                                                <td>Dec 6, 2024</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="text-center mt-3">
                                    <a href="#" class="btn btn-primary">View All Grades</a>
                                    <a href="#" class="btn btn-info"><i class="ti-download"></i> Download Report Card</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Homework & Assignment Status -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-clipboard"></i> Homework & Assignment Status</h4>
                            </div>
                            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                <h5 class="mb-3">Sarah Johnson - Grade 10A</h5>
                                
                                <div class="assignment-item" style="padding: 12px; margin-bottom: 10px; background-color: #f8f9fa;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">Mathematics - Homework #15</h6>
                                            <small class="text-muted">Assigned: Dec 10 | Due: Dec 15</small>
                                        </div>
                                        <span class="badge badge-success">Submitted</span>
                                    </div>
                                    <p class="mb-0 mt-2"><strong>Status:</strong> Submitted on Dec 13 - Graded: 92%</p>
                                </div>

                                <div class="assignment-item" style="padding: 12px; margin-bottom: 10px; background-color: #f8f9fa;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">Physics - Lab Report</h6>
                                            <small class="text-muted">Assigned: Dec 11 | Due: Dec 18</small>
                                        </div>
                                        <span class="badge badge-warning">In Progress</span>
                                    </div>
                                    <p class="mb-0 mt-2"><strong>Status:</strong> 60% completed - Due in 5 days</p>
                                </div>

                                <div class="assignment-item" style="padding: 12px; margin-bottom: 10px; background-color: #f8f9fa;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">English - Essay Writing</h6>
                                            <small class="text-muted">Assigned: Dec 8 | Due: Dec 20</small>
                                        </div>
                                        <span class="badge badge-info">Not Started</span>
                                    </div>
                                    <p class="mb-0 mt-2"><strong>Status:</strong> Not started yet - Due in 7 days</p>
                                </div>

                                <hr>

                                <h5 class="mb-3 mt-4">Michael Johnson - Grade 7B</h5>

                                <div class="assignment-item" style="padding: 12px; margin-bottom: 10px; background-color: #fff5f5;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">Science - Project Work</h6>
                                            <small class="text-muted">Assigned: Dec 5 | Due: Dec 12</small>
                                        </div>
                                        <span class="badge badge-danger">Overdue</span>
                                    </div>
                                    <p class="mb-0 mt-2"><strong>Status:</strong> Not submitted - 1 day overdue</p>
                                </div>

                                <div class="assignment-item" style="padding: 12px; margin-bottom: 10px; background-color: #f8f9fa;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">Mathematics - Practice Problems</h6>
                                            <small class="text-muted">Assigned: Dec 10 | Due: Dec 16</small>
                                        </div>
                                        <span class="badge badge-warning">In Progress</span>
                                    </div>
                                    <p class="mb-0 mt-2"><strong>Status:</strong> 40% completed - Due in 3 days</p>
                                </div>

                                <div class="assignment-item" style="padding: 12px; margin-bottom: 10px; background-color: #f8f9fa;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">English - Reading Assignment</h6>
                                            <small class="text-muted">Assigned: Dec 9 | Due: Dec 14</small>
                                        </div>
                                        <span class="badge badge-success">Submitted</span>
                                    </div>
                                    <p class="mb-0 mt-2"><strong>Status:</strong> Submitted on Dec 12 - Awaiting grade</p>
                                </div>

                                <div class="text-center mt-3">
                                    <a href="#" class="btn btn-primary">View All Assignments</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Footer -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer">
                            <p>MLC Classroom - Parent Dashboard | Last Updated: <span id="date-time"></span></p>
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
@extends('layouts.app')

@section('title', 'Parent Dashboard')

@push('styles')
    <style>
        /* Custom styles for parent dashboard */
        .stat-widget-four {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        
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
        
        .homework-item {
            padding: 12px;
            border-left: 3px solid #17a2b8;
            margin-bottom: 10px;
            background-color: #f8f9fa;
        }
        
        .homework-item.late {
            border-left-color: #dc3545;
            background-color: #fff5f5;
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

        .schedule-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            min-height: 150px;
        }

        .schedule-item {
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 8px;
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
                <!-- Child Selector (if multiple children) -->
                @if($children->count() > 1)
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-body">
                                <form method="GET" action="{{ route('parent.dashboard') }}" class="form-inline">
                                    <label class="mr-3"><strong>Select Child:</strong></label>
                                    <select name="child_id" onchange="this.form.submit()" class="form-control">
                                        @foreach($children as $child)
                                        <option value="{{ $child->id }}" {{ $selectedChild && $selectedChild->id == $child->id ? 'selected' : '' }}>
                                            {{ $child->full_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($selectedChild)
                <!-- Child Info Header -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-9">
                                        <h2 class="mb-2">{{ $selectedChild->full_name }}</h2>
                                        <p class="mb-0" style="opacity: 0.9;">
                                            <i class="ti-calendar"></i> Enrolled: {{ $selectedChild->enrollment_date->format('d F Y') }}
                                        </p>
                                    </div>
                                    <div class="col-md-3 text-right">
                                        <i class="ti-user" style="font-size: 4rem; opacity: 0.3;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->              
                  <div class="row">
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon color-success border-success">
                                    <i class="ti-check"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Attendance Rate</div>
                                        <div class="stat-text"><strong>{{ number_format($stats['attendance_rate'], 1) }}%</strong></div>
                                        <div class="card-footer text-center text-muted">
                                            {{ $stats['present_count'] }}/{{ $stats['total_attendance'] }} classes this month
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon color-info border-info">
                                    <i class="ti-clipboard"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Pending Homework</div>
                                        <div class="stat-text">{{ $stats['pending_homework'] }}</strong></div>
                                        <div class="card-footer text-center">
                                            <a href="{{ route('parent.homework.index', $selectedChild) }}" class="text-warning">View homework →</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon color-primary border-primary">
                                    <i class="ti-blackboard"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Enrolled Classes</div>
                                        <div class="stat-text"><strong>{{ $stats['enrolled_classes'] }}</strong></div>
                                        {{-- <div class="card-footer text-center">
                                            <a href="{{ route('parent.students.classes', $selectedChild) }}" class="text-info">View classes →</a>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-four">
                                <div class="stat-icon color-pink border-pink">
                                    <i class="ti-star"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-heading">Recent Average</div>
                                        <div class="stat-text">
                                            <strong>
                                                @if($stats['recent_grades']->count() > 0)
                                                    {{ $stats['recent_grades']->first() }}
                                                @else
                                                    N/A
                                                @endif
                                            </strong>
                                        </div>
                                        <div class="card-footer text-center text-muted">
                                            Last {{ $stats['recent_grades']->count() }} assignments
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

             

                <!-- Upcoming Homework & Recent Progress -->
                <div class="row">
                    <!-- Upcoming Homework -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-clipboard"></i> Upcoming Homework (Next 7 Days)</h4>
                            </div>
                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                @if($upcomingHomework && $upcomingHomework->count() > 0)
                                    @foreach($upcomingHomework as $submission)
                                    <div class="homework-item {{ $submission->status === 'late' ? 'late' : '' }}">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">{{ $submission->homeworkAssignment->title }}</h6>
                                            <span class="badge {{ $submission->status === 'submitted' ? 'badge-success' : ($submission->status === 'late' ? 'badge-danger' : 'badge-warning') }}">
                                                {{ ucfirst($submission->status) }}
                                            </span>
                                        </div>
                                        <p class="mb-1 text-muted">{{ $submission->homeworkAssignment->class->name }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="ti-calendar"></i> Due: {{ $submission->homeworkAssignment->due_date->format('d M Y') }}
                                            </small>
                                            <a href="{{ route('parent.homework.show', [$selectedChild, $submission]) }}" class="btn btn-sm btn-info">
                                                <i class="ti-eye"></i> View
                                            </a>
                                        </div>
                                    </div>
                                    @endforeach
                                    <div class="text-center mt-3 pt-3 border-top">
                                        <a href="{{ route('parent.homework.index', $selectedChild) }}" class="btn btn-primary">View All Homework</a>
                                    </div>
                                @else
                                    <p class="text-center text-muted py-5">No upcoming homework assignments.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Recent Progress Reports -->
                    <div class="col-lg-6">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-bar-chart"></i> Recent Progress Reports</h4>
                            </div>
                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                @if($recentProgress && $recentProgress->count() > 0)
                                    @foreach($recentProgress as $progressNote)
                                    <div class="subject-card">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">{{ $progressNote->progressSheet->class->name }}</h6>
                                            <span class="badge 
                                                {{ $progressNote->performance === 'excellent' ? 'badge-success' : 
                                                   ($progressNote->performance === 'good' ? 'badge-info' : 
                                                   ($progressNote->performance === 'average' ? 'badge-warning' : 
                                                   ($progressNote->performance === 'struggling' ? 'badge-danger' : 'badge-secondary'))) }}">
                                                {{ ucfirst($progressNote->performance ?? 'N/A') }}
                                            </span>
                                        </div>
                                        <p class="mb-2 text-muted">{{ $progressNote->progressSheet->topic }}</p>
                                        @if($progressNote->notes)
                                        <p class="mb-2" style="font-style: italic;">"{{ Str::limit($progressNote->notes, 100) }}"</p>
                                        @endif
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="ti-calendar"></i> {{ $progressNote->progressSheet->date->format('d M Y') }}
                                            </small>
                                            <small class="text-muted">
                                                <i class="ti-user"></i> Teacher: {{ $progressNote->progressSheet->teacher->name }}
                                            </small>
                                        </div>
                                    </div>
                                    @endforeach
                                    {{-- <div class="text-center mt-3 pt-3 border-top">
                                        <a href="{{ route('parent.students.progress', $selectedChild) }}" class="btn btn-primary">View All Progress Reports</a>
                                    </div> --}}
                                @else
                                    <p class="text-center text-muted py-5">No progress reports yet.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- This Week's Schedule -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-calendar"></i> This Week's Schedule</h4>
                            </div>
                            <div class="card-body">
                                @if($weeklySchedule && count($weeklySchedule) > 0)
                                <div class="row">
                                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day)
                                    <div class="col-md">
                                        <div class="schedule-card">
                                            <h6 class="text-center mb-3 pb-2 border-bottom">{{ $day }}</h6>
                                            
                                            @if(isset($weeklySchedule[$day]) && $weeklySchedule[$day]->count() > 0)
                                                @foreach($weeklySchedule[$day] as $class)
                                                    @foreach($class->schedules as $schedule)
                                                    <div class="schedule-item">
                                                        <p class="mb-1"><strong>{{ $class->name }}</strong></p>
                                                        <p class="mb-1 text-muted">
                                                            <i class="ti-time"></i> {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                                        </p>
                                                        <p class="mb-0 text-muted">
                                                            <i class="ti-user"></i> {{ $class->teacher->name }}
                                                        </p>
                                                    </div>
                                                    @endforeach
                                                @endforeach
                                            @else
                                                <p class="text-center text-muted">No classes</p>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                    <p class="text-center text-muted py-5">No schedule available.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- This Week's Attendance Summary -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-check-box"></i> This Week's Attendance</h4>
                            </div>
                            <div class="card-body">
                                @if($attendanceSummary && $attendanceSummary['total'] > 0)
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="text-center p-3" style="background-color: #f8f9fa; border-radius: 8px;">
                                                <h3 class="mb-0">{{ $attendanceSummary['total'] }}</h3>
                                                <p class="mb-0 text-muted">Total Classes</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3" style="background-color: #d4edda; border-radius: 8px;">
                                                <h3 class="mb-0 text-success">{{ $attendanceSummary['present'] }}</h3>
                                                <p class="mb-0 text-muted">Present</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3" style="background-color: #f8d7da; border-radius: 8px;">
                                                <h3 class="mb-0 text-danger">{{ $attendanceSummary['absent'] }}</h3>
                                                <p class="mb-0 text-muted">Absent</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3" style="background-color: #fff3cd; border-radius: 8px;">
                                                <h3 class="mb-0 text-warning">{{ $attendanceSummary['late'] }}</h3>
                                                <p class="mb-0 text-muted">Late</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($attendanceSummary['records']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Class</th>
                                                    <th>Date & Time</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($attendanceSummary['records'] as $record)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $record->class->name }}</strong>
                                                    </td>
                                                    <td>
                                                        {{ $record->date->format('l, d M Y') }} • {{ $record->schedule->time_range }}
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $record->status === 'present' ? 'badge-success' : ($record->status === 'absent' ? 'badge-danger' : 'badge-warning') }}">
                                                            {{ ucfirst($record->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                    
                                    <div class="text-center mt-3 pt-3 border-top">
                                        <a href="{{ route('parent.attendance.index', $selectedChild) }}" class="btn btn-primary">View Full Attendance History</a>
                                    </div>
                                @else
                                    <p class="text-center text-muted py-5">No attendance records for this week.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @else
                <!-- No Children Message -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-body text-center py-5">
                                <i class="ti-user" style="font-size: 5rem; color: #cbd5e0;"></i>
                                <h3 class="mt-4 mb-2">No Children Enrolled</h3>
                                <p class="text-muted mb-4">You don't have any children enrolled yet. Please contact the school administrator to add your children.</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

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

@endpush
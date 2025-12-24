@extends('layouts.app')

@section('title', 'Attendance Reports & Analytics')

@push('styles')
    <style>
        .report-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }

        .class-row {
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .class-row:hover {
            background-color: #f8f9fa;
        }


        .student-alert-row {
            padding: 12px;
            background-color: #fff3cd;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .filter-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
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
                                <h1>Attendance Reports & Analytics</h1>
                            </div>
                        </div>
                        <span class="text-muted">Comprehensive attendance statistics and insights</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('superadmin.attendance.index') }}">Attendance</a></li>
                                    <li class="active">Reports</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Filter Section -->
                    <div class="row mt-3">
                        <div class="col-lg-12">
                            <div class="filter-section">
                                <form method="GET" action="{{ route('superadmin.attendance.reports') }}">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Date From</label>
                                                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Date To</label>
                                                <input type="date" name="date_to" value="{{ $dateTo }}" max="{{ now()->format('Y-m-d') }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Filter by Class</label>
                                                <select name="class_id" class="form-control">
                                                    <option value="">All Classes</option>
                                                    @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" {{ $classFilter == $class->id ? 'selected' : '' }}>
                                                        {{ $class->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    <i class="ti-filter"></i> Apply
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>


                    <!-- Overall Statistics -->
                    <div class="row">
                        <div class="col-lg-2">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-user color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Records</div>
                                        <div class="stat-digit">{{ number_format($overallStats['total_records']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-check color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Present</div>
                                        <div class="stat-digit">{{ number_format($overallStats['present']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-close color-danger border-danger"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Absent</div>
                                        <div class="stat-digit">{{ number_format($overallStats['absent']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-time color-warning border-warning"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Late</div>
                                        <div class="stat-digit">{{ number_format($overallStats['late']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-alert color-dark border-dark"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text" style="font-size: 1.1rem">Unauthorized</div>
                                        <div class="stat-digit">{{ number_format($overallStats['unauthorized']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div> 

                        <div class="col-lg-2">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-stats-up color-info border-info"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Rate</div>
                                        <div class="stat-digit">{{ $overallStats['attendance_rate'] }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                        
                    </div>

               

                    <!-- Attendance Trend Chart -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-bar-chart"></i> Daily Attendance Trend</h4>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="attendanceTrendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance by Class -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-blackboard"></i> Attendance by Class</h4>
                                    <div class="card-header-right-icon">
                                        <span class="badge badge-primary">{{ $attendanceByClass->count() }} classes</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($attendanceByClass->count() > 0)
                                        @foreach($attendanceByClass as $classData)
                                        <div class="class-row">
                                            <div class="row align-items-center">
                                                <div class="col-md-4">
                                                    <strong>{{ $classData->class->name }}</strong><br>
                                                    <small class="text-muted">
                                                        <i class="ti-user"></i> {{ $classData->class->teacher->name ?? 'No teacher' }}
                                                    </small>
                                                </div>

                                                <div class="col-md-4">
                                                    {{-- <div class="attendance-bar">
                                                        <div class="attendance-fill {{ $classData->attendance_rate < 75 ? 'low' : ($classData->attendance_rate < 85 ? 'medium' : '') }}" 
                                                             style="width: {{ $classData->attendance_rate }}%">
                                                        </div>
                                                    </div> --}}
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-primary progress-bar-striped active 
                                                                    {{ $classData->attendance_rate < 75 ? 'progress-bar-danger' : ($classData->attendance_rate < 85 ? 'progress-bar-warning' : 'progress-bar-success') }}" 
                                                            role="progressbar" 
                                                            style="width: {{ $classData->attendance_rate }}%;"
                                                            aria-valuenow="{{ $classData->attendance_rate }}" 
                                                            aria-valuemin="0" 
                                                            aria-valuemax="100">
                                                            {{ round($classData->attendance_rate) }}%
                                                        </div>
                                                    </div>
                                                    <small class="text-muted mt-1 d-block">{{ $classData->attendance_rate }}% attendance rate</small>
                                                </div>

                                                <div class="col-md-4 text-right">
                                                    <span class="badge badge-success">{{ $classData->present_count }} Present</span>
                                                    <span class="badge badge-danger">{{ $classData->absent_count }} Absent</span>
                                                    <span class="badge badge-warning">{{ $classData->late_count }} Late</span>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-4">
                                            <p class="text-muted">No attendance data available for the selected period.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Attendance Alert -->
                    @if($lowAttendanceStudents->count() > 0)
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-alert"></i> Students with Low Attendance (&lt;75%)</h4>
                                    <div class="card-header-right-icon">
                                        <span class="badge badge-warning">{{ $lowAttendanceStudents->count() }} students</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @foreach($lowAttendanceStudents as $student)
                                    <div class="student-alert-row">
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <strong>{{ $student->full_name }}</strong><br>
                                                <small class="text-muted">Parent: {{ $student->parent->name ?? 'Not assigned' }}</small>
                                            </div>
                                            <div class="col-md-3">
                                                <div style="display: flex; gap: 10px;">
                                                    <span class="badge badge-success">{{ $student->present_count }} Present</span>
                                                    <span class="badge badge-danger">{{ $student->absent_count }} Absent</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <strong class="text-warning">{{ $student->attendance_rate }}% Attendance</strong>
                                            </div>
                                            <div class="col-md-2 text-right">
                                                <a href="{{ route('superadmin.students.show', $student) }}" class="btn btn-sm btn-info">
                                                    <i class="ti-eye"></i> View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Attendance Reports</p>
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
    $(document).ready(function() {
        // Attendance Trend Chart
        var ctx = document.getElementById('attendanceTrendChart').getContext('2d');
        var attendanceTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: {!! json_encode($chartData['data']) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    });
</script>
@endpush
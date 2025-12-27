@extends('layouts.app')
@section('content')
    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="page-header">
                            <div class="page-title">
                                <h1>Student Reports</h1>
                            </div>
                        </div><span>Individual student performance analysis</span>
                    </div>
                    <div class="col-lg-4">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('superadmin.reports.index') }}">Reports</a></li>
                                    <li class="active">Students</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Selection -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-user"></i> Select Student</h4>
                            </div>
                            <div class="card-body">
                                <form method="GET">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group"><label
                                                    style="font-size: 0.875rem; font-weight: 500;">Student</label>
                                                <select name="student_id" class="form-control" required>
                                                    <option value="">Choose student...</option>
                                                    @foreach ($students as $student)
                                                        <option value="{{ $student->id }}"
                                                            {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                                            {{ $student->full_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group"><label
                                                    style="font-size: 0.875rem; font-weight: 500;">Date From</label><input
                                                    type="date" name="date_from" value="{{ $dateFrom }}"
                                                    class="form-control"></div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group"><label
                                                    style="font-size: 0.875rem; font-weight: 500;">Date To</label><input
                                                    type="date" name="date_to" value="{{ $dateTo }}"
                                                    class="form-control"></div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group"><label>&nbsp;</label><button type="submit"
                                                    class="btn btn-primary btn-block"><i class="ti-search"></i>
                                                    Generate</button></div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($studentData)
                    <!-- Student Overview -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header"
                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                    <h4 style="color: white; margin: 0;"><i class="ti-user"></i>
                                        {{ $studentData->full_name }}</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <p style="font-size: 0.875rem; color: #6c757d; margin-bottom: 5px;">Student ID
                                            </p>
                                            <p style="font-size: 0.9375rem; font-weight: 600;">{{ $studentData->id }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p style="font-size: 0.875rem; color: #6c757d; margin-bottom: 5px;">Parent</p>
                                            <p style="font-size: 0.9375rem; font-weight: 600;">
                                                {{ $studentData->parent->name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p style="font-size: 0.875rem; color: #6c757d; margin-bottom: 5px;">Enrolled
                                                Classes</p>
                                            <p style="font-size: 0.9375rem; font-weight: 600;">
                                                {{ $studentData->enrollments->count() }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p style="font-size: 0.875rem; color: #6c757d; margin-bottom: 5px;">Status</p>
                                            <span class="badge badge-success">{{ ucfirst($studentData->status) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-check color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Attendance Rate</div>
                                        <div class="stat-digit">{{ $attendanceData['rate'] }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-pencil-alt color-primary border-primary"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Homework Rate</div>
                                        <div class="stat-digit">{{ $homeworkData['rate'] }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-stats-up color-purple border-purple"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Average Grade</div>
                                        <div class="stat-digit">{{ $homeworkData['average_grade'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-comment-alt color-danger border-danger"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Progress Notes</div>
                                        <div class="stat-digit">{{ $progressData['count'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-bar-chart"></i> Attendance Trend (12 Months)</h4>
                                </div>
                                <div class="card-body"><canvas id="attendanceChart" height="100"></canvas></div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-line-chart"></i> Grade Progression</h4>
                                </div>
                                <div class="card-body"><canvas id="gradeChart" height="100"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Buttons -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-download"></i> Export Student Report</h4>
                                </div>
                                <div class="card-body">
                                    <!-- PDF Export -->
                                    <form method="POST" action="{{ route('superadmin.reports.export') }}"
                                        style="display: inline-block; margin-right: 10px;">
                                        @csrf
                                        <input type="hidden" name="report_type" value="student">
                                        <input type="hidden" name="student_id" value="{{ $studentData->id }}">
                                        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                        <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                        <input type="hidden" name="format" value="pdf">
                                        <button type="submit" class="btn btn-success">
                                            <i class="ti-download"></i> Export PDF
                                        </button>
                                    </form>

                                    <!-- Excel Export -->
                                    <form method="POST" action="{{ route('superadmin.reports.export') }}"
                                        style="display: inline-block; margin-right: 10px;">
                                        @csrf
                                        <input type="hidden" name="report_type" value="student">
                                        <input type="hidden" name="student_id" value="{{ $studentData->id }}">
                                        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                        <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                        <input type="hidden" name="format" value="excel">
                                        <button type="submit" class="btn btn-info">
                                            <i class="ti-download"></i> Export Excel
                                        </button>
                                    </form>

                                    <!-- CSV Export -->
                                    <form method="POST" action="{{ route('superadmin.reports.export') }}"
                                        style="display: inline-block;">
                                        @csrf
                                        <input type="hidden" name="report_type" value="student">
                                        <input type="hidden" name="student_id" value="{{ $studentData->id }}">
                                        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                        <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                        <input type="hidden" name="format" value="csv">
                                        <button type="submit" class="btn btn-secondary">
                                            <i class="ti-download"></i> Export CSV
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer">
                            <p>MLC Classroom - Student Reports</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($studentData && $charts)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            new Chart(document.getElementById('attendanceChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($charts['attendance_trend']['labels']) !!},
                    datasets: [{
                        label: 'Attendance %',
                        data: {!! json_encode($charts['attendance_trend']['data']) !!},
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)'
                    }]
                },
                options: {
                    responsive: true
                }
            });
            new Chart(document.getElementById('gradeChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($charts['grade_progression']['labels']) !!},
                    datasets: [{
                        label: 'Grade',
                        data: {!! json_encode($charts['grade_progression']['data']) !!},
                        borderColor: '#6f42c1',
                        backgroundColor: 'rgba(111, 66, 193, 0.1)'
                    }]
                },
                options: {
                    responsive: true
                }
            });
        </script>
    @endpush
@endif

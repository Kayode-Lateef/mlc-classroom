@extends('layouts.app')
@section('title', 'Class Reports')

@section('content')
    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-8 p-r-0 title-margin-right">
                        <div class="page-header">
                            <div class="page-title">
                                <h1>Class Reports</h1>
                            </div>
                        </div><span>Class performance and student comparisons</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                                    <li class="active">Classes</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header mb-3">
                                <h4><i class="ti-filter"></i> Select Class</h4>
                            </div>
                            <div class="card-body">
                                <form method="GET">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group"><label
                                                    style="font-weight: 500;">Class</label>
                                                <select name="class_id" class="form-control" required>
                                                    <option value="">Choose class...</option>
                                                    @foreach ($classes as $class)
                                                        <option value="{{ $class->id }}"
                                                            {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                            {{ $class->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group"><label
                                                    style="font-weight: 500;">Date From</label><input
                                                    type="date" name="date_from" value="{{ $dateFrom }}"
                                                    class="form-control"></div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group"><label
                                                    style="font-weight: 500;">Date To</label><input
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

                @if ($classData)
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <h4 style="margin: 0;">{{ $classData->name }}</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p style="color: #6c757d; margin: 0;">Teacher</p>
                                            <p style="font-weight: 600;">
                                                {{ $classData->teacher->name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p style="color: #6c757d; margin: 0;">Total Students</p>
                                            <p style="font-weight: 600;">
                                                {{ $classData->students->count() }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p style="color: #6c757d; margin: 0;">Status</p><span
                                                class="badge badge-success">Active</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-check color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Attendance Rate</div>
                                        <div class="stat-digit">{{ $attendanceStats['rate'] }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-pencil-alt color-primary border-primary"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Homework Completion</div>
                                        <div class="stat-digit">{{ $homeworkStats['average_completion'] }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-book color-purple border-purple"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Assignments</div>
                                        <div class="stat-digit">{{ $homeworkStats['total_assignments'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-user"></i> Student Performance</h4>
                                </div>
                                <div class="card-body">
                                    @if ($studentStats->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Student</th>
                                                        <th>Attendance %</th>
                                                        <th>Homework %</th>
                                                        <th>Avg Grade</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($studentStats as $stat)
                                                        <tr>
                                                            <td>
                                                                {{ $stat['student']->full_name }}</td>
                                                            <td><span
                                                                    class="badge badge-{{ $stat['attendance_rate'] >= 80 ? 'success' : 'warning' }}">{{ $stat['attendance_rate'] }}%</span>
                                                            </td>
                                                            <td><span
                                                                    class="badge badge-{{ $stat['homework_rate'] >= 80 ? 'success' : 'warning' }}">{{ $stat['homework_rate'] }}%</span>
                                                            </td>
                                                            <td style="font-weight: 600;">
                                                                {{ $stat['average_grade'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Buttons -->
                    {{-- <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <h4><i class="ti-download"></i> Export Class Report</h4>
                                </div>
                                <div class="card-body">
                                    <!-- PDF Export -->
                                    <form method="POST" action="{{ route('admin.reports.export') }}"
                                        style="display: inline-block; margin-right: 10px;">
                                        @csrf
                                        <input type="hidden" name="report_type" value="class">
                                        <input type="hidden" name="class_id" value="{{ $classData->id }}">
                                        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                        <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                        <input type="hidden" name="format" value="pdf">
                                        <button type="submit" class="btn btn-success">
                                            <i class="ti-download"></i> Export PDF
                                        </button>
                                    </form>

                                    <!-- Excel Export -->
                                    <form method="POST" action="{{ route('admin.reports.export') }}"
                                        style="display: inline-block; margin-right: 10px;">
                                        @csrf
                                        <input type="hidden" name="report_type" value="class">
                                        <input type="hidden" name="class_id" value="{{ $classData->id }}">
                                        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                        <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                        <input type="hidden" name="format" value="excel">
                                        <button type="submit" class="btn btn-info">
                                            <i class="ti-download"></i> Export Excel
                                        </button>
                                    </form>

                                    <!-- CSV Export -->
                                    <form method="POST" action="{{ route('admin.reports.export') }}"
                                        style="display: inline-block;">
                                        @csrf
                                        <input type="hidden" name="report_type" value="class">
                                        <input type="hidden" name="class_id" value="{{ $classData->id }}">
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
                    </div> --}}
                    
                @endif

                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer">
                            <p>MLC Classroom - Class Reports</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

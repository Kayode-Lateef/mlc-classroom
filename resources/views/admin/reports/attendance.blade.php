@extends('layouts.app')
@section('title', 'Attendance Reports')

@section('content')
    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-8 p-r-0 title-margin-right">
                        <div class="page-header">
                            <div class="page-title"><h1>Attendance Reports</h1></div>
                        </div>
                        <span>View and export attendance statistics</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                                    <li class="active">Attendance</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-one">
                                <div class="stat-icon dib"><i class="ti-calendar color-primary border-primary"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">Total Records</div>
                                    <div class="stat-digit">{{ number_format($stats['total']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-one">
                                <div class="stat-icon dib"><i class="ti-check color-success border-success"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">Present</div>
                                    <div class="stat-digit">{{ number_format($stats['present']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-one">
                                <div class="stat-icon dib"><i class="ti-close color-danger border-danger"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">Absent</div>
                                    <div class="stat-digit">{{ number_format($stats['absent']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-one">
                                <div class="stat-icon dib"><i class="ti-stats-up color-purple border-purple"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">Attendance Rate</div>
                                    <div class="stat-digit">{{ $stats['attendance_rate'] }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header mb-3"><h4><i class="ti-filter"></i> Filters</h4></div>
                            <div class="card-body">
                                <form method="GET">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Date From</label>
                                                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Date To</label>
                                                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Class</label>
                                                <select name="class_id" class="form-control">
                                                    <option value="">All Classes</option>
                                                    @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="">All Status</option>
                                                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                                                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">&nbsp;</label>
                                                <button type="submit" class="btn btn-primary btn-block"><i class="ti-filter"></i> Apply</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card alert">
                            <div class="card-header mb-3"><h4><i class="ti-bar-chart"></i> Attendance Trend</h4></div>
                            <div class="card-body">
                                <canvas id="trendChart" height="80"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card alert">
                            <div class="card-header mb-3"><h4><i class="ti-pie-chart"></i> Status Distribution</h4></div>
                            <div class="card-body">
                                <canvas id="pieChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-list"></i> Attendance Records</h4>
                                <div class="card-header-right">
                                    <!-- PDF Export -->
                                    <form method="POST" action="{{ route('admin.reports.export') }}" style="display: inline-block; margin-right: 5px;">
                                        @csrf
                                        <input type="hidden" name="report_type" value="attendance">
                                        <input type="hidden" name="format" value="pdf">
                                        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                        <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                        @if(request('class_id'))
                                            <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                                        @endif
                                        @if(request('student_id'))
                                            <input type="hidden" name="student_id" value="{{ request('student_id') }}">
                                        @endif
                                        @if(request('status'))
                                            <input type="hidden" name="status" value="{{ request('status') }}">
                                        @endif
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="ti-download"></i> PDF
                                        </button>
                                    </form>

                                    <!-- Excel Export -->
                                    <form method="POST" action="{{ route('admin.reports.export') }}" style="display: inline-block; margin-right: 5px;">
                                        @csrf
                                        <input type="hidden" name="report_type" value="attendance">
                                        <input type="hidden" name="format" value="excel">
                                        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                        <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                        @if(request('class_id'))
                                            <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                                        @endif
                                        @if(request('student_id'))
                                            <input type="hidden" name="student_id" value="{{ request('student_id') }}">
                                        @endif
                                        @if(request('status'))
                                            <input type="hidden" name="status" value="{{ request('status') }}">
                                        @endif
                                        <button type="submit" class="btn btn-info btn-sm">
                                            <i class="ti-download"></i> Excel
                                        </button>
                                    </form>

                                    <!-- CSV Export -->
                                    <form method="POST" action="{{ route('admin.reports.export') }}" style="display: inline-block;">
                                        @csrf
                                        <input type="hidden" name="report_type" value="attendance">
                                        <input type="hidden" name="format" value="csv">
                                        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                        <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                        @if(request('class_id'))
                                            <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                                        @endif
                                        @if(request('student_id'))
                                            <input type="hidden" name="student_id" value="{{ request('student_id') }}">
                                        @endif
                                        @if(request('status'))
                                            <input type="hidden" name="status" value="{{ request('status') }}">
                                        @endif
                                        <button type="submit" class="btn btn-secondary btn-sm">
                                            <i class="ti-download"></i> CSV
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($attendanceRecords->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Student</th>
                                                <th>Class</th>
                                                <th>Status</th>
                                                <th>Marked By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($attendanceRecords as $record)
                                            <tr>
                                                <td>{{ $record->date->format('d M Y') }}</td>
                                                <td>{{ $record->student->full_name }}</td>
                                                <td>{{ $record->class->name }}</td>
                                                <td>
                                                    @php
                                                        $badges = ['present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'unauthorized' => 'secondary'];
                                                    @endphp
                                                    <span class="badge badge-{{ $badges[$record->status] ?? 'secondary' }}">{{ ucfirst($record->status) }}</span>
                                                </td>
                                                <td>{{ $record->markedBy->name ?? 'N/A' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                {{ $attendanceRecords->links() }}
                                @else
                                <div style="text-align: center; padding: 40px;">
                                    <i class="ti-calendar" style="font-size: 4rem; color: #cbd5e0;"></i>
                                    <p style="margin: 10px 0 0 0; color: #6c757d;">No attendance records found</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer"><p>MLC Classroom - Attendance Reports</p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
{{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
<script src="{{ asset('assets/js/lib/chart-js/Chart.bundle.js') }}"></script>

<script>
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($attendanceTrend['labels']) !!},
        datasets: [{
            label: 'Present',
            data: {!! json_encode($attendanceTrend['present']) !!},
            borderColor: 'rgb(40, 167, 69)',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
        }, {
            label: 'Absent',
            data: {!! json_encode($attendanceTrend['absent']) !!},
            borderColor: 'rgb(220, 53, 69)',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
        }]
    },
    options: { responsive: true, maintainAspectRatio: true }
});

const pieCtx = document.getElementById('pieChart').getContext('2d');
new Chart(pieCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($statusDistribution['labels']) !!},
        datasets: [{
            data: {!! json_encode($statusDistribution['data']) !!},
            backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#6c757d']
        }]
    },
    options: { responsive: true, maintainAspectRatio: true }
});
</script>
@endpush
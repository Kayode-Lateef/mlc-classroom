@extends('layouts.app')
@section('title', 'Homework Reports')
@section('content')
    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-8 p-r-0 title-margin-right">
                        <div class="page-header">
                            <div class="page-title">
                                <h1>Homework Reports</h1>
                            </div>
                        </div><span>Homework completion and grading statistics</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                                    <li class="active">Homework</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-one">
                                <div class="stat-icon dib"><i class="ti-book color-primary border-primary"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">Total Assignments</div>
                                    <div class="stat-digit">{{ $stats['total_assignments'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-one">
                                <div class="stat-icon dib"><i class="ti-check color-success border-success"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">Avg Completion</div>
                                    <div class="stat-digit">{{ $stats['average_completion'] }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-one">
                                <div class="stat-icon dib"><i class="ti-time color-warning border-warning"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">Grading Pending</div>
                                    <div class="stat-digit">{{ $stats['grading_pending'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="stat-widget-one">
                                <div class="stat-icon dib"><i class="ti-alert color-danger border-danger"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">Overdue</div>
                                    <div class="stat-digit">{{ $stats['overdue'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-filter"></i> Filters</h4>
                            </div>
                            <div class="card-body">
                                <form method="GET">
                                    <div class="row">
                                        <div class="col-md-2">
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
                                        <div class="col-md-3">
                                            <div class="form-group"><label
                                                    style="font-weight: 500;">Class</label>
                                                <select name="class_id" class="form-control">
                                                    <option value="">All Classes</option>
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
                                                    style="font-weight: 500;">Teacher</label>
                                                <select name="teacher_id" class="form-control">
                                                    <option value="">All Teachers</option>
                                                    @foreach ($teachers as $teacher)
                                                        <option value="{{ $teacher->id }}"
                                                            {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                                            {{ $teacher->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group"><label>&nbsp;</label><button type="submit"
                                                    class="btn btn-primary btn-block"><i class="ti-filter"></i>
                                                    Apply</button></div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card alert">
                            <div class="card-header mb-3">
                                <h4><i class="ti-bar-chart"></i> Completion Trend (Last 7 Weeks)</h4>
                            </div>
                            <div class="card-body"><canvas id="completionChart" height="80"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card alert">
                            <div class="card-header mb-3">
                                <h4><i class="ti-pie-chart"></i> Grade Distribution</h4>
                            </div>
                            <div class="card-body"><canvas id="gradeChart" height="200"></canvas></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header mb-3">
                                <h4><i class="ti-list"></i> Homework Assignments</h4>
                                {{-- <div class="card-header-right">
                                    <!-- PDF Export -->
                                    <form method="POST" action="{{ route('admin.reports.export') }}"
                                        style="display: inline-block; margin-right: 5px;">
                                        @csrf
                                        <input type="hidden" name="report_type" value="homework">
                                        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                        <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                        @if (request('class_id'))
                                            <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                                        @endif
                                        @if (request('teacher_id'))
                                            <input type="hidden" name="teacher_id" value="{{ request('teacher_id') }}">
                                        @endif
                                        <input type="hidden" name="format" value="pdf">
                                        <button type="submit" class="btn btn-success btn-sm"><i class="ti-download"></i>
                                            PDF</button>
                                    </form>
                                    <!-- Excel Export -->
                                    <form method="POST" action="{{ route('admin.reports.export') }}"
                                        style="display: inline-block; margin-right: 5px;">
                                        @csrf
                                        <input type="hidden" name="report_type" value="homework">
                                        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                        <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                        @if (request('class_id'))
                                            <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                                        @endif
                                        @if (request('teacher_id'))
                                            <input type="hidden" name="teacher_id" value="{{ request('teacher_id') }}">
                                        @endif
                                        <input type="hidden" name="format" value="excel">
                                        <button type="submit" class="btn btn-info btn-sm"><i class="ti-download"></i>
                                            Excel</button>
                                    </form>
                                    <!-- CSV Export -->
                                    <form method="POST" action="{{ route('admin.reports.export') }}"
                                        style="display: inline-block;">
                                        @csrf
                                        <input type="hidden" name="report_type" value="homework">
                                        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                        <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                        @if (request('class_id'))
                                            <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                                        @endif
                                        @if (request('teacher_id'))
                                            <input type="hidden" name="teacher_id" value="{{ request('teacher_id') }}">
                                        @endif
                                        <input type="hidden" name="format" value="csv">
                                        <button type="submit" class="btn btn-secondary btn-sm"><i
                                                class="ti-download"></i> CSV</button>
                                    </form>
                                </div> --}}
                            </div>
                            <div class="card-body">
                                @if ($homeworkAssignments->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Assignment</th>
                                                    <th>Class</th>
                                                    <th>Due Date</th>
                                                    <th>Completion</th>
                                                    <th>Avg Grade</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($homeworkAssignments as $hw)
                                                    <tr>
                                                        <td>{{ $hw->title }}</td>
                                                        <td>{{ $hw->class->name }}</td>
                                                        <td>
                                                            {{ $hw->due_date->format('d M Y') }}</td>
                                                        <td>
                                                            <div class="progress" style="height: 20px;">
                                                                <div class="progress-bar bg-success"
                                                                    style="width: {{ $hw->completion_rate }}%">
                                                                    {{ $hw->completion_rate }}%</div>
                                                            </div>
                                                        </td>
                                                        <td style="font-weight: 600;">
                                                            {{ $hw->average_grade }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    {{ $homeworkAssignments->links() }}
                                @else
                                    <div style="text-align: center; padding: 40px;"><i class="ti-book"
                                            style="font-size: 4rem; color: #cbd5e0;"></i>
                                        <p style="color: #6c757d;">No homework assignments found</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer">
                            <p>MLC Classroom - Homework Reports</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('completionChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($completionTrend['labels']) !!},
                datasets: [{
                    label: 'Completion %',
                    data: {!! json_encode($completionTrend['data']) !!},
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)'
                }]
            },
            options: {
                responsive: true
            }
        });
        new Chart(document.getElementById('gradeChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($gradeDistribution['labels']) !!},
                datasets: [{
                    data: {!! json_encode($gradeDistribution['data']) !!},
                    backgroundColor: ['#28a745', '#007bff', '#ffc107', '#fd7e14', '#dc3545']
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
@endpush

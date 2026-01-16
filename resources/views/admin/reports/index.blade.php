@extends('layouts.app')
@section('title', 'Reports Dashboard')

@push('styles')
<style>
    .report-shortcut {
        padding: 25px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .report-shortcut:hover {
        background-color: #f8f9fa;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .report-shortcut i {
        font-size: 3rem;
        margin-bottom: 15px;
    }

    .recent-report-item {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
    }

    .recent-report-item:last-child {
        border-bottom: none;
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
                                <h1>Reports Dashboard</h1>
                            </div>
                        </div>
                        <span>Generate and view comprehensive reports</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Reports</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-user color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Students</div>
                                        <div class="stat-digit">{{ number_format($stats['total_students']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-layout-grid2 color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Classes</div>
                                        <div class="stat-digit">{{ number_format($stats['total_classes']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-check color-purple border-purple"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Attendance Rate</div>
                                        <div class="stat-digit">{{ $stats['attendance_rate'] }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-pencil-alt color-danger border-danger"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Homework Rate</div>
                                        <div class="stat-digit">{{ $stats['homework_completion'] }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Report Shortcuts -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <h4><i class="ti-folder"></i> Quick Report Access</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <a href="{{ route('admin.reports.attendance') }}" style="text-decoration: none; color: inherit;">
                                                <div class="report-shortcut">
                                                    <i class="ti-calendar" style="color: #007bff;"></i>
                                                    <h5 style="margin: 10px 0; font-weight: 600;">Attendance Reports</h5>
                                                    <p style="margin: 0; color: #6c757d;">View attendance statistics and trends</p>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-3">
                                            <a href="{{ route('admin.reports.students') }}" style="text-decoration: none; color: inherit;">
                                                <div class="report-shortcut">
                                                    <i class="ti-user" style="color: #28a745;"></i>
                                                    <h5 style="margin: 10px 0; font-weight: 600;">Student Reports</h5>
                                                    <p style="margin: 0; color: #6c757d;">Individual student performance analysis</p>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-3">
                                            <a href="{{ route('admin.reports.classes') }}" style="text-decoration: none; color: inherit;">
                                                <div class="report-shortcut">
                                                    <i class="ti-layout-grid2" style="color: #6f42c1;"></i>
                                                    <h5 style="margin: 10px 0; font-weight: 600;">Class Reports</h5>
                                                    <p style="margin: 0; color: #6c757d;">Class performance and comparisons</p>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-3">
                                            <a href="{{ route('admin.reports.homework') }}" style="text-decoration: none; color: inherit;">
                                                <div class="report-shortcut">
                                                    <i class="ti-pencil-alt" style="color: #dc3545;"></i>
                                                    <h5 style="margin: 10px 0; font-weight: 600;">Homework Reports</h5>
                                                    <p style="margin: 0; color: #6c757d;">Homework completion and grading stats</p>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Reports Chart -->
                        <div class="col-lg-8">
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <h4><i class="ti-bar-chart"></i> Reports Generated (Last 6 Months)</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="reportsChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Reports -->
                        <div class="col-lg-4">
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <h4><i class="ti-time"></i> Recent Reports</h4>
                                </div>
                                <div class="card-body" style="padding: 0;">
                                    @if($recentReports->count() > 0)
                                        @foreach($recentReports as $report)
                                        <div class="recent-report-item">
                                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                                <div>
                                                    <h6 style="margin: 0 0 5px 0; font-weight: 600;">{{ $report['type'] }}</h6>
                                                    <p style="margin: 0; color: #6c757d;">
                                                        <i class="ti-user"></i> {{ $report['generated_by'] }}
                                                    </p>
                                                    <p style="margin: 5px 0 0 0; color: #6c757d;">
                                                        <i class="ti-time"></i> {{ $report['date']->diffForHumans() }}
                                                    </p>
                                                </div>
                                                <span class="badge badge-primary">{{ $report['format'] }}</span>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div style="padding: 40px 20px; text-align: center;">
                                            <i class="ti-folder" style="font-size: 3rem; color: #cbd5e0;"></i>
                                            <p style="margin: 10px 0 0 0; color: #6c757d;">No recent reports</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Reports Dashboard</p>
                            </div>
                        </div>
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
$(document).ready(function() {
    // Reports chart
    const ctx = document.getElementById('reportsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($reportsByType['labels']) !!},
            datasets: [
                {
                    label: 'Attendance',
                    data: {!! json_encode($reportsByType['attendance']) !!},
                    backgroundColor: 'rgba(0, 123, 255, 0.7)',
                },
                {
                    label: 'Students',
                    data: {!! json_encode($reportsByType['students']) !!},
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                },
                {
                    label: 'Classes',
                    data: {!! json_encode($reportsByType['classes']) !!},
                    backgroundColor: 'rgba(111, 66, 193, 0.7)',
                },
                {
                    label: 'Homework',
                    data: {!! json_encode($reportsByType['homework']) !!},
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
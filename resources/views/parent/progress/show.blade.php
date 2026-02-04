@extends('layouts.app')

@section('title', 'Detailed Progress Report')

@push('styles')
    <style>
        .session-header {
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .stat-box {
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }

        .stat-box h3 {
            margin: 10px 0 5px;
            /* font-size: 2rem; */
        }

        .detail-item {
            margin-bottom: 20px;
        }

        .detail-label {
            color: #6c757d;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .detail-value {
            color: #212529;
        }

        .performance-badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-weight: 600;
        }

        .badge-excellent {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-good {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .badge-average {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-struggling {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-absent {
            background-color: #f8f9fa;
            color: #6c757d;
        }

        .student-note-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff;
            transition: background-color 0.2s;
        }

        .student-note-item:hover {
            background-color: #f8f9fa;
        }

        .student-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }

        .student-avatar-initial {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background-color: #007bff;
            color: white;
        }

        .filter-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .trend-bar {
            height: 100%;
            background: linear-gradient(180deg, #28a745 0%, #20c997 100%);
            border-radius: 4px;
            transition: all 0.3s;
        }

        .trend-bar:hover {
            background: linear-gradient(180deg, #20c997 0%, #17a2b8 100%);
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
                                <h1>Detailed Progress Report</h1>
                            </div>
                        </div>
                        <span class="text-muted">Comprehensive progress analysis for {{ $student->full_name }}</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('parent.progress.index', ['child_id' => $student->id]) }}">Progress</a></li>
                                    <li class="active">Detailed Report</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Session Header -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="session-header card bg-primary">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h2 style="margin: 0 0 10px; color: white;">
                                            <i class="ti-user"></i> {{ $student->full_name }}
                                        </h2>
                                        <p style="margin: 0; font-size: 1.1rem; opacity: 0.9; color: white;">
                                            <i class="ti-calendar"></i> {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                            <i class="ti-book"></i> {{ $student->classes->count() }} Classes
                                        </p>
                                        <p style="margin: 10px 0 0; opacity: 0.9; color: white;">
                                            <i class="ti-email"></i> Parent: {{ $student->parent->name ?? 'Not assigned' }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <div class="stat-box">
                                            <h3>{{ $stats['period']['success_rate'] }}%</h3>
                                            <p style="margin: 0;">Success Rate</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="row">
                        <div class="col-lg-12">
                            <a href="{{ route('parent.progress.index', ['child_id' => $student->id]) }}" class="btn btn-secondary mb-3">
                                <i class="ti-back-left"></i> Back to Progress Overview
                            </a>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-clipboard color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total</div>
                                        <div class="stat-digit">{{ $stats['period']['total'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-star color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Excellent</div>
                                        <div class="stat-digit">{{ $stats['period']['excellent'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-check color-info border-info"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Good</div>
                                        <div class="stat-digit">{{ $stats['period']['good'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-minus color-warning border-warning"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Average</div>
                                        <div class="stat-digit">{{ $stats['period']['average'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-alert color-danger border-danger"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Struggling</div>
                                        <div class="stat-digit">{{ $stats['period']['struggling'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-close color-secondary border-secondary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Absent</div>
                                        <div class="stat-digit">{{ $stats['period']['absent'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="filter-card">
                                <form method="GET" action="{{ route('parent.progress.show', $student) }}">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Date From</label>
                                                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Date To</label>
                                                <input type="date" name="date_to" value="{{ $dateTo }}" max="{{ now()->format('Y-m-d') }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div style="display: flex; gap: 10px;">
                                                    <button type="submit" class="btn btn-primary flex-fill">
                                                        <i class="ti-filter"></i> Apply
                                                    </button>
                                                    <a href="{{ route('parent.progress.show', $student) }}" class="btn btn-secondary">
                                                        <i class="ti-reload"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Overall vs This Month vs Last Month -->
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card alert">
                                <div class="card-header mb-4">
                                    <h4><i class="ti-stats-up"></i> Overall (All Time)</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center mb-3">
                                        <div class="col-12">
                                            <h2 class="text-primary">{{ $stats['overall']['success_rate'] }}%</h2>
                                            <p class="text-muted">Success Rate</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Total:</strong> {{ $stats['overall']['total'] }}</p>
                                            <p><strong>Excellent:</strong> <span class="text-success">{{ $stats['overall']['excellent'] }}</span></p>
                                            <p><strong>Good:</strong> <span class="text-info">{{ $stats['overall']['good'] }}</span></p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Average:</strong> <span class="text-warning">{{ $stats['overall']['average'] }}</span></p>
                                            <p><strong>Struggling:</strong> <span class="text-danger">{{ $stats['overall']['struggling'] }}</span></p>
                                            <p><strong>Absent:</strong> <span class="text-muted">{{ $stats['overall']['absent'] }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card alert">
                                <div class="card-header mb-4">
                                    <h4><i class="ti-calendar"></i> This Month</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center mb-3">
                                        <div class="col-12">
                                            <h2 class="text-success">{{ $stats['this_month']['total'] }}</h2>
                                            <p class="text-muted">Total Notes</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Excellent:</strong> <span class="text-success">{{ $stats['this_month']['excellent'] }}</span></p>
                                            <p><strong>Good:</strong> <span class="text-info">{{ $stats['this_month']['good'] }}</span></p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Struggling:</strong> <span class="text-danger">{{ $stats['this_month']['struggling'] }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card alert">
                                <div class="card-header mb-4">
                                    <h4><i class="ti-calendar"></i> Last Month</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center mb-3">
                                        <div class="col-12">
                                            <h2 class="text-info">{{ $stats['last_month']['total'] }}</h2>
                                            <p class="text-muted">Total Notes</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Excellent:</strong> <span class="text-success">{{ $stats['last_month']['excellent'] }}</span></p>
                                            <p><strong>Good:</strong> <span class="text-info">{{ $stats['last_month']['good'] }}</span></p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Struggling:</strong> <span class="text-danger">{{ $stats['last_month']['struggling'] }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Trend Chart -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header mb-4">
                                    <h4><i class="ti-bar-chart"></i> 6-Month Performance Trend</h4>
                                </div>
                                <div class="card-body">
                                    <div style="display: flex; align-items: flex-end; gap: 15px; height: 250px; padding: 20px;">
                                        @foreach($stats['monthly_trend'] as $month)
                                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: flex-end; align-items: center;">
                                            <small style="margin-bottom: 5px; font-weight: 600;">{{ $month['rate'] }}%</small>
                                            <div class="trend-bar" style="width: 100%; height: {{ $month['rate'] }}%;" title="{{ $month['month'] }}: {{ $month['rate'] }}% ({{ $month['count'] }} notes)"></div>
                                            <small style="margin-top: 8px; color: #6c757d;">{{ $month['month'] }}</small>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- By Class Breakdown -->
                    @if(count($stats['by_class']) > 0)
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header mb-4">
                                    <h4><i class="ti-book"></i> Performance by Class</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Class</th>
                                                    <th>Teacher</th>
                                                    <th>Total</th>
                                                    <th>Excellent</th>
                                                    <th>Good</th>
                                                    <th>Average</th>
                                                    <th>Struggling</th>
                                                    <th>Absent</th>
                                                    <th>Success Rate</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($stats['by_class'] as $classStat)
                                                <tr>
                                                    <td><strong>{{ $classStat['class']->name }}</strong></td>
                                                    <td>{{ $classStat['class']->teacher->name ?? 'N/A' }}</td>
                                                    <td>{{ $classStat['total'] }}</td>
                                                    <td><span class="badge badge-success">{{ $classStat['excellent'] }}</span></td>
                                                    <td><span class="badge badge-info">{{ $classStat['good'] }}</span></td>
                                                    <td><span class="badge badge-warning">{{ $classStat['average'] }}</span></td>
                                                    <td><span class="badge badge-danger">{{ $classStat['struggling'] }}</span></td>
                                                    <td><span class="badge badge-secondary">{{ $classStat['absent'] }}</span></td>
                                                    <td>
                                                        <strong class="{{ $classStat['success_rate'] >= 80 ? 'text-success' : ($classStat['success_rate'] >= 60 ? 'text-info' : 'text-danger') }}">
                                                            {{ $classStat['success_rate'] }}%
                                                        </strong>
                                                        <div class="progress mt-1" style="height: 8px;">
                                                            <div class="progress-bar {{ $classStat['success_rate'] >= 80 ? 'bg-success' : ($classStat['success_rate'] >= 60 ? 'bg-info' : 'bg-danger') }}" 
                                                                 style="width: {{ $classStat['success_rate'] }}%"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Performance Distribution -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header mb-4">
                                    <h4><i class="ti-pie-chart"></i> Performance Distribution (Selected Period)</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col">
                                            <div style="padding: 20px; background: #d4edda; border-radius: 8px;">
                                                <h2 class="text-success">{{ $stats['by_performance']['excellent'] }}</h2>
                                                <p class="mb-0"><strong>Excellent</strong></p>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div style="padding: 20px; background: #d1ecf1; border-radius: 8px;">
                                                <h2 class="text-info">{{ $stats['by_performance']['good'] }}</h2>
                                                <p class="mb-0"><strong>Good</strong></p>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div style="padding: 20px; background: #fff3cd; border-radius: 8px;">
                                                <h2 class="text-warning">{{ $stats['by_performance']['average'] }}</h2>
                                                <p class="mb-0"><strong>Average</strong></p>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div style="padding: 20px; background: #f8d7da; border-radius: 8px;">
                                                <h2 class="text-danger">{{ $stats['by_performance']['struggling'] }}</h2>
                                                <p class="mb-0"><strong>Struggling</strong></p>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div style="padding: 20px; background: #f8f9fa; border-radius: 8px;">
                                                <h2 class="text-muted">{{ $stats['by_performance']['absent'] }}</h2>
                                                <p class="mb-0"><strong>Absent</strong></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Progress Notes by Date -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header mb-4">
                                    <h4><i class="ti-list"></i> Detailed Progress Notes ({{ $progressNotes->count() }})</h4>
                                </div>
                                <div class="card-body">
                                    @if($progressNotes->count() > 0)
                                        @foreach($notesByDate as $date => $notes)
                                        <h5 class="mt-3 mb-3">{{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</h5>
                                        
                                        @foreach($notes as $note)
                                        <div class="student-note-item">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <h5 style="margin: 0 0 10px 0;">{{ $note->progressSheet->topic }}</h5>
                                                    <p style="margin: 0 0 10px 0;">
                                                        <span class="badge badge-light">{{ $note->progressSheet->class->name }}</span>
                                                        <small class="text-muted ml-2">
                                                            <i class="ti-user"></i> {{ $note->progressSheet->teacher->name }}
                                                        </small>
                                                    </p>
                                                    @if($note->notes)
                                                    <p style="margin: 0; color: #6c757d;">
                                                        <strong>Teacher's Notes:</strong> {{ $note->notes }}
                                                    </p>
                                                    @endif
                                                </div>
                                                <div class="col-md-4 text-right">
                                                    @switch($note->performance)
                                                        @case('excellent')
                                                            <span class="performance-badge badge-excellent">
                                                                ✨ Excellent
                                                            </span>
                                                            @break
                                                        @case('good')
                                                            <span class="performance-badge badge-good">
                                                                ✓ Good
                                                            </span>
                                                            @break
                                                        @case('average')
                                                            <span class="performance-badge badge-average">
                                                                ~ Average
                                                            </span>
                                                            @break
                                                        @case('struggling')
                                                            <span class="performance-badge badge-struggling">
                                                                ⚠ Struggling
                                                            </span>
                                                            @break
                                                        @case('absent')
                                                            <span class="performance-badge badge-absent">
                                                                ✗ Absent
                                                            </span>
                                                            @break
                                                    @endswitch
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        @endforeach
                                    @else
                                        <div class="text-center py-5">
                                            <i class="ti-clipboard" style="font-size: 4rem; color: #cbd5e0;"></i>
                                            <h4 class="mt-3">No Progress Notes</h4>
                                            <p class="text-muted">No progress notes found for this period.</p>
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
                                <p>MLC Classroom - Detailed Progress Report</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
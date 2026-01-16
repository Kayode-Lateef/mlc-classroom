@extends('layouts.app')

@section('title', 'Progress Reports')

@push('styles')
    <style>
        .filter-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .progress-note-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            background: white;
        }

        .progress-note-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .performance-badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .child-selector {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 2px solid #e9ecef;
        }

        .child-option {
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }

        .child-option:hover {
            background: #f8f9fa;
        }

        .child-option.active {
            background: #e7f3ff;
            border-color: #007bff;
        }

        .student-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        .student-initial {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.875rem;
            background-color: #007bff;
            color: white;
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
                                <h1>Progress Reports</h1>
                            </div>
                        </div>
                        <span>View your children's academic progress and teacher feedback</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Progress Reports</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    @if($children->isEmpty())
                        <!-- No Children State -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card alert">
                                    <div class="card-body text-center py-5">
                                        <i class="ti-user" style="font-size: 4rem; color: #cbd5e0;"></i>
                                        <h3 class="mt-3 mb-2">No Children Found</h3>
                                        <p class="text-muted mb-4">You don't have any children registered in the system.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Child Selector -->
                        @if($children->count() > 1)
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="child-selector">
                                    <h5 class="mb-3">Select Child</h5>
                                    <div class="row">
                                        @foreach($children as $child)
                                        <div class="col-md-3">
                                            <a href="{{ route('parent.progress.index', ['child_id' => $child->id]) }}" 
                                               class="child-option {{ $selectedChild && $selectedChild->id == $child->id ? 'active' : '' }}"
                                               style="display: block; text-decoration: none; color: inherit;">
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    @if($child->profile_photo)
                                                        <img src="{{ asset('storage/' . $child->profile_photo) }}" 
                                                             alt="{{ $child->full_name }}" 
                                                             class="student-avatar">
                                                    @else
                                                        <div class="student-initial">
                                                            {{ strtoupper(substr($child->first_name, 0, 1)) }}{{ strtoupper(substr($child->last_name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $child->full_name }}</strong>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($selectedChild && $stats)
                            <!-- Statistics Cards -->
                            <div class="row">
                                <div class="col-lg-2">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="stat-icon dib"><i class="ti-clipboard color-primary border-primary"></i></div>
                                            <div class="stat-content dib">
                                                <div class="stat-text">Total</div>
                                                <div class="stat-digit">{{ $stats['total'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="stat-icon dib"><i class="ti-star color-success border-success"></i></div>
                                            <div class="stat-content dib">
                                                <div class="stat-text">Excellent</div>
                                                <div class="stat-digit">{{ $stats['excellent'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="stat-icon dib"><i class="ti-check color-info border-info"></i></div>
                                            <div class="stat-content dib">
                                                <div class="stat-text">Good</div>
                                                <div class="stat-digit">{{ $stats['good'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="stat-icon dib"><i class="ti-minus color-warning border-warning"></i></div>
                                            <div class="stat-content dib">
                                                <div class="stat-text">Average</div>
                                                <div class="stat-digit">{{ $stats['average'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="stat-icon dib"><i class="ti-alert color-danger border-danger"></i></div>
                                            <div class="stat-content dib">
                                                <div class="stat-text">Struggling</div>
                                                <div class="stat-digit">{{ $stats['struggling'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="stat-icon dib"><i class="ti-stats-up color-pink border-pink"></i></div>
                                            <div class="stat-content dib">
                                                <div class="stat-text">Success Rate</div>
                                                <div class="stat-digit">{{ $stats['success_rate'] }}%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- View Detailed Report Button -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="text-center mb-4">
                                        <a href="{{ route('parent.progress.show', $selectedChild) }}" 
                                           class="btn btn-primary btn-lg">
                                            <i class="ti-bar-chart"></i> View Detailed Progress Report
                                        </a>
                                        <p class="text-muted mt-2">See comprehensive analysis, trends, and class-by-class breakdown</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Trend -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card alert">
                                        <div class="card-header">
                                            <h4><i class="ti-bar-chart"></i> 8-Week Performance Trend</h4>
                                        </div>
                                        <div class="card-body">
                                            <div style="display: flex; align-items: flex-end; gap: 10px; height: 200px; padding: 20px;">
                                                @foreach($stats['recent_trend'] as $week)
                                                <div style="flex: 1; display: flex; flex-direction: column; justify-content: flex-end; align-items: center;">
                                                    <small style="margin-bottom: 5px; font-weight: 600;">{{ $week['rate'] }}%</small>
                                                    <div class="trend-bar" style="width: 100%; height: {{ $week['rate'] }}%;" title="{{ $week['week'] }}: {{ $week['rate'] }}% ({{ $week['count'] }} notes)"></div>
                                                    <small style="margin-top: 8px; color: #6c757d;">{{ $week['week'] }}</small>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- By Class Statistics -->
                            @if(count($stats['by_class']) > 0)
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card alert">
                                        <div class="card-header">
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
                                                            <td>
                                                                <strong class="{{ $classStat['success_rate'] >= 80 ? 'text-success' : ($classStat['success_rate'] >= 60 ? 'text-info' : 'text-danger') }}">
                                                                    {{ $classStat['success_rate'] }}%
                                                                </strong>
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

                            <!-- Filters -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="filter-card">
                                        <form method="GET" action="{{ route('parent.progress.index') }}">
                                            <input type="hidden" name="child_id" value="{{ $selectedChild->id }}">
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
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Class</label>
                                                        <select name="class_id" class="form-control">
                                                            <option value="">All Classes</option>
                                                            @foreach($classes as $class)
                                                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                                {{ $class->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Performance</label>
                                                        <select name="performance" class="form-control">
                                                            <option value="">All Levels</option>
                                                            <option value="excellent" {{ request('performance') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="good" {{ request('performance') == 'good' ? 'selected' : '' }}>Good</option>
                                                            <option value="average" {{ request('performance') == 'average' ? 'selected' : '' }}>Average</option>
                                                            <option value="struggling" {{ request('performance') == 'struggling' ? 'selected' : '' }}>Struggling</option>
                                                            <option value="absent" {{ request('performance') == 'absent' ? 'selected' : '' }}>Absent</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <a href="{{ route('parent.progress.index', ['child_id' => $selectedChild->id]) }}" class="btn btn-secondary">
                                                    <i class="ti-reload"></i> Clear
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ti-filter"></i> Apply Filters
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Notes List -->
                            @if($progressNotes->count() > 0)
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card alert">
                                        <div class="card-header">
                                            <h4>Recent Progress Notes ({{ $progressNotes->total() }})</h4>
                                            <div class="card-header-right-icon">
                                                <span class="badge badge-primary">
                                                    Showing {{ $progressNotes->firstItem() ?? 0 }} - {{ $progressNotes->lastItem() ?? 0 }} of {{ $progressNotes->total() }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @foreach($progressNotes as $note)
                                            <div class="progress-note-card">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <h5 style="margin: 0 0 10px 0;">{{ $note->progressSheet->topic }}</h5>
                                                        <p style="margin: 0 0 10px 0;">
                                                            <span class="badge badge-light">{{ $note->progressSheet->class->name }}</span>
                                                            <small class="text-muted ml-2">
                                                                <i class="ti-user"></i> {{ $note->progressSheet->teacher->name }}
                                                            </small>
                                                            <small class="text-muted ml-2">
                                                                <i class="ti-calendar"></i> {{ $note->progressSheet->date->format('d M Y') }}
                                                            </small>
                                                        </p>
                                                        @if($note->notes)
                                                        <p style="margin: 10px 0 0 0; color: #6c757d;">
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

                                            <!-- Pagination -->
                                            <div class="mt-4">
                                                {{ $progressNotes->appends(request()->query())->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <!-- Empty State -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="empty-state">
                                        <i class="ti-clipboard"></i>
                                        <h3 class="mb-3">No Progress Notes Found</h3>
                                        <p class="text-muted mb-4">No progress notes match your filters.</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endif
                    @endif

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Progress Reports</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
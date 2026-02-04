@extends('layouts.app')

@section('title', 'Attendance Records')

@push('styles')
    <style>
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
        }

        .status-badge i {
            margin-right: 4px;
        }

        .filter-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .student-initial {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background-color: #e7f3ff;
            color: #007bff;
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

        .attendance-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            height: 100%;
        }

        .attendance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .progress-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto;
        }

        .progress-circle.excellent {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .progress-circle.good {
            background: linear-gradient(135deg, #17a2b8 0%, #3498db 100%);
            color: white;
        }

        .progress-circle.fair {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
        }

        .progress-circle.poor {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
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
                                <h1>Attendance Records</h1>
                            </div>
                        </div>
                        <span class="text-muted">View your children's attendance history</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Attendance</li>
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
                                            <a href="{{ route('parent.attendance.index', ['child_id' => $child->id]) }}" 
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
                                <!-- Period Stats -->
                                <div class="col-lg-3 col-md-6">
                                    <div class="card p-0">
                                        <div class="stat-widget-three">
                                            <div class="stat-icon bg-primary">
                                                <i class="ti-clipboard"></i>
                                            </div>
                                            <div class="stat-content">
                                                <div class="stat-digit">{{ $stats['period']['total'] }}</div>
                                                <div class="stat-text">Period Total</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card p-0">
                                        <div class="stat-widget-three">
                                            <div class="stat-icon bg-success">
                                                <i class="ti-check"></i>
                                            </div>
                                            <div class="stat-content">
                                                <div class="stat-digit">{{ $stats['period']['present'] }}</div>
                                                <div class="stat-text">Present</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card p-0">
                                        <div class="stat-widget-three">
                                            <div class="stat-icon bg-danger">
                                                <i class="ti-close"></i>
                                            </div>
                                            <div class="stat-content">
                                                <div class="stat-digit">{{ $stats['period']['absent'] }}</div>
                                                <div class="stat-text">Absent</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card p-0">
                                        <div class="stat-widget-three">
                                            <div class="stat-icon bg-pink">
                                                <i class="ti-stats-up"></i>
                                            </div>
                                            <div class="stat-content">
                                                <div class="stat-digit">{{ $stats['period']['rate'] }}%</div>
                                                <div class="stat-text">Attendance Rate</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- View Detailed Report Button -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="text-center mb-4">
                                        <a href="{{ route('parent.attendance.show', $selectedChild) }}" 
                                           class="btn btn-primary btn-lg">
                                            <i class="ti-bar-chart"></i> View Detailed Attendance Report
                                        </a>
                                        <p class="text-muted mt-2">See comprehensive statistics, trends, and class-by-class breakdown</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Overall & Monthly Stats -->
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card alert">
                                        <div class="card-header">
                                            <h4><i class="ti-stats-up"></i> Overall Statistics</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <p><strong>Total Sessions:</strong> {{ $stats['overall']['total'] }}</p>
                                                    <p><strong>Present:</strong> {{ $stats['overall']['present'] }}</p>
                                                </div>
                                                <div class="col-6">
                                                    <p><strong>Absent:</strong> {{ $stats['overall']['absent'] }}</p>
                                                    <p><strong>Rate:</strong> {{ $stats['overall']['rate'] }}%</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card alert">
                                        <div class="card-header">
                                            <h4><i class="ti-calendar"></i> This Month</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <p><strong>Total Sessions:</strong> {{ $stats['monthly']['total'] }}</p>
                                                    <p><strong>Present:</strong> {{ $stats['monthly']['present'] }}</p>
                                                </div>
                                                <div class="col-6">
                                                    <p><strong>Absent:</strong> {{ $stats['monthly']['absent'] }}</p>
                                                    <p><strong>Rate:</strong> {{ $stats['monthly']['rate'] }}%</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- By Class Statistics (Clickable Cards) -->
                            @if(count($stats['by_class']) > 0)
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card alert">
                                        <div class="card-header">
                                            <h4><i class="ti-book"></i> Attendance by Class</h4>
                                            <small class="text-muted">Click on any class to view detailed report</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($stats['by_class'] as $classStat)
                                                <div class="col-lg-4 col-md-6 mb-3">
                                                    <a href="{{ route('parent.attendance.show', $selectedChild) }}?class_id={{ $classStat['class']->id }}" 
                                                       style="text-decoration: none; color: inherit;">
                                                        <div class="card attendance-card">
                                                            <div class="card-body">
                                                                <h5 class="text-center mb-3">{{ $classStat['class']->name }}</h5>
                                                                
                                                                @php
                                                                    $rate = $classStat['rate'];
                                                                    if ($rate >= 90) {
                                                                        $circleClass = 'excellent';
                                                                    } elseif ($rate >= 75) {
                                                                        $circleClass = 'good';
                                                                    } elseif ($rate >= 60) {
                                                                        $circleClass = 'fair';
                                                                    } else {
                                                                        $circleClass = 'poor';
                                                                    }
                                                                @endphp
                                                                
                                                                <div class="progress-circle {{ $circleClass }} mb-3">
                                                                    {{ $rate }}%
                                                                </div>
                                                                
                                                                <div class="text-center">
                                                                    <p class="mb-1"><small class="text-muted">Teacher:</small><br>
                                                                    <strong>{{ $classStat['class']->teacher->name ?? 'N/A' }}</strong></p>
                                                                </div>
                                                                
                                                                <hr>
                                                                
                                                                <div class="row text-center">
                                                                    <div class="col-4">
                                                                        <small class="text-muted">Total</small><br>
                                                                        <strong>{{ $classStat['total'] }}</strong>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <small class="text-success">Present</small><br>
                                                                        <strong>{{ $classStat['present'] }}</strong>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <small class="text-danger">Absent</small><br>
                                                                        <strong>{{ $classStat['absent'] }}</strong>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                                @endforeach
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
                                        <form method="GET" action="{{ route('parent.attendance.index') }}">
                                            <input type="hidden" name="child_id" value="{{ $selectedChild->id }}">
                                            <div class="row">
                                                <!-- Date From -->
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Date From</label>
                                                        <input 
                                                            type="date" 
                                                            name="date_from" 
                                                            value="{{ $dateFrom }}" 
                                                            class="form-control"
                                                        >
                                                    </div>
                                                </div>

                                                <!-- Date To -->
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Date To</label>
                                                        <input 
                                                            type="date" 
                                                            name="date_to" 
                                                            value="{{ $dateTo }}" 
                                                            max="{{ now()->format('Y-m-d') }}"
                                                            class="form-control"
                                                        >
                                                    </div>
                                                </div>

                                                <!-- Class -->
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

                                                <!-- Status -->
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select name="status" class="form-control">
                                                            <option value="">All Statuses</option>
                                                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                                                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                                                            <option value="unauthorized" {{ request('status') == 'unauthorized' ? 'selected' : '' }}>Unauthorized</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="text-right">
                                                <a href="{{ route('parent.attendance.index', ['child_id' => $selectedChild->id]) }}" class="btn btn-secondary">
                                                    <i class="ti-reload"></i> Clear Filters
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ti-search"></i> Apply Filters
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Attendance Records -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card alert">
                                        <div class="card-header">
                                            <h4>Recent Attendance ({{ $attendance->total() }})</h4>
                                            <div class="card-header-right-icon">
                                                <span class="badge badge-primary">
                                                    Showing {{ $attendance->firstItem() ?? 0 }} - {{ $attendance->lastItem() ?? 0 }} of {{ $attendance->total() }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @if($attendance->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Class</th>
                                                            <th>Status</th>
                                                            <th>Time</th>
                                                            <th>Marked By</th>
                                                            <th>Notes</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($attendance as $record)
                                                        <tr>
                                                            <td>
                                                                <strong>{{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}</strong><br>
                                                                <small class="text-muted">{{ \Carbon\Carbon::parse($record->date)->format('l') }}</small>
                                                            </td>
                                                            <td>
                                                                <strong>{{ $record->class->name }}</strong><br>
                                                                @if($record->schedule)
                                                                <small class="text-muted">
                                                                    {{ \Carbon\Carbon::parse($record->schedule->start_time)->format('H:i') }} - 
                                                                    {{ \Carbon\Carbon::parse($record->schedule->end_time)->format('H:i') }}
                                                                </small>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @switch($record->status)
                                                                    @case('present')
                                                                        <span class="status-badge badge-success">
                                                                            <i class="ti-check"></i> Present
                                                                        </span>
                                                                        @break
                                                                    @case('absent')
                                                                        <span class="status-badge badge-danger">
                                                                            <i class="ti-close"></i> Absent
                                                                        </span>
                                                                        @break
                                                                    @case('late')
                                                                        <span class="status-badge badge-warning">
                                                                            <i class="ti-time"></i> Late
                                                                        </span>
                                                                        @break
                                                                    @case('unauthorized')
                                                                        <span class="status-badge badge-orange">
                                                                            <i class="ti-alert"></i> Unauthorized
                                                                        </span>
                                                                        @break
                                                                @endswitch
                                                            </td>
                                                            <td>
                                                                <small class="text-muted">{{ $record->created_at->format('H:i') }}</small>
                                                            </td>
                                                            <td>
                                                                <strong>{{ $record->markedBy->name }}</strong><br>
                                                                <small class="text-muted">{{ $record->created_at->diffForHumans() }}</small>
                                                            </td>
                                                            <td>
                                                                @if($record->notes)
                                                                    <span title="{{ $record->notes }}">{{ Str::limit($record->notes, 30) }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Pagination -->
                                            <div class="mt-4">
                                                {{ $attendance->appends(request()->query())->links() }}
                                            </div>
                                            @else
                                            <!-- Empty State -->
                                            <div class="text-center py-5">
                                                <i class="ti-clipboard" style="font-size: 4rem; color: #cbd5e0;"></i>
                                                <h3 class="mt-3 mb-2">No attendance records found</h3>
                                                <p class="text-muted mb-4">Try adjusting your filters or date range.</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Attendance Records</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
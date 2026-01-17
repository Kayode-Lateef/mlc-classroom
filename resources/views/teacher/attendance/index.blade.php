@extends('layouts.app')

@section('title', 'Attendance Records')

@push('styles')
    <style>
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-badge i {
            margin-right: 4px;
            font-size: 1rem;
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
            font-size: 0.875rem;
            background-color: #e7f3ff;
            color: #3386f7;
        }

        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .session-card {
            border-left: 4px solid #3386f7;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .session-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .session-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .session-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .attendance-summary {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .summary-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-widget-three {
            padding: 20px;
        }

        .stat-widget-three .stat-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 1.5rem;
        }

        .stat-widget-three .stat-content {
            margin-left: 70px;
        }

        .stat-widget-three .stat-digit {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .stat-widget-three .stat-text {
            font-size: 0.875rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .attendance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
            padding: 15px;
        }

        .attendance-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background-color: #fff;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .btn-mark-attendance {
            background-color: #3386f7;
            color: white;
            border: none;
        }

        .btn-mark-attendance:hover {
            background-color: #2c75d6;
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
                        <span class="text-muted">View and manage attendance for your classes</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Attendance</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Quick Actions -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="quick-actions">
                                <a href="{{ route('teacher.attendance.create') }}" class="btn btn-mark-attendance">
                                    <i class="ti-plus"></i> Mark Attendance
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon" style="background-color: #e3f2fd;">
                                        <i class="ti-clipboard" style="color: #3386f7;"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ number_format($stats['total_records']) }}</div>
                                        <div class="stat-text">Total Records</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon" style="background-color: #e8f5e9;">
                                        <i class="ti-check" style="color: #4caf50;"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ number_format($stats['present']) }}</div>
                                        <div class="stat-text">Present</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon" style="background-color: #ffebee;">
                                        <i class="ti-close" style="color: #f44336;"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ number_format($stats['absent']) }}</div>
                                        <div class="stat-text">Absent</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon" style="background-color: #fff3e0;">
                                        <i class="ti-time" style="color: #ff9800;"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ number_format($stats['late']) }}</div>
                                        <div class="stat-text">Late</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon" style="background-color: #fce4ec;">
                                        <i class="ti-alert" style="color: #e91e63;"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ number_format($stats['unauthorized']) }}</div>
                                        <div class="stat-text">Unauthorized</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon" style="background-color: #f3e5f5;">
                                        <i class="ti-stats-up" style="color: #9c27b0;"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ $stats['attendance_rate'] }}%</div>
                                        <div class="stat-text">Att. Rate</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="filter-card">
                                <form method="GET" action="{{ route('teacher.attendance.index') }}">
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
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Class</label>
                                                <select name="class_id" class="form-control">
                                                    <option value="">All Classes</option>
                                                    @foreach($myClasses as $class)
                                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                        {{ $class->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Status -->
                                        <div class="col-md-2">
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

                                        <!-- Sort -->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Sort By</label>
                                                <select name="sort_by" class="form-control">
                                                    <option value="date" {{ request('sort_by', 'date') == 'date' ? 'selected' : '' }}>Date</option>
                                                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Marked At</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <a href="{{ route('teacher.attendance.index') }}" class="btn btn-secondary">
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

                    <!-- Attendance Sessions -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4>Attendance Sessions ({{ $attendanceRecords->total() }})</h4>
                                    <div class="card-header-right-icon">
                                        <span class="badge badge-primary">
                                            Showing {{ $attendanceRecords->firstItem() ?? 0 }} - {{ $attendanceRecords->lastItem() ?? 0 }} of {{ $attendanceRecords->total() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($attendanceRecords->count() > 0)
                                        @php
                                            // Group records by session (date + class + schedule)
                                            $sessions = $attendanceRecords->groupBy(function($record) {
                                                return $record->date . '-' . $record->class_id . '-' . $record->schedule_id;
                                            });
                                        @endphp

                                        @foreach($sessions as $sessionKey => $sessionRecords)
                                            @php
                                                $firstRecord = $sessionRecords->first();
                                                $sessionStats = [
                                                    'total' => $sessionRecords->count(),
                                                    'present' => $sessionRecords->where('status', 'present')->count(),
                                                    'absent' => $sessionRecords->where('status', 'absent')->count(),
                                                    'late' => $sessionRecords->where('status', 'late')->count(),
                                                    'unauthorized' => $sessionRecords->where('status', 'unauthorized')->count(),
                                                ];
                                                $sessionStats['rate'] = $sessionStats['total'] > 0 
                                                    ? round(($sessionStats['present'] / $sessionStats['total']) * 100, 1)
                                                    : 0;
                                            @endphp

                                            <div class="session-card card mb-3">
                                                <div class="session-header">
                                                    <div class="session-meta">
                                                        <div>
                                                            <h5 class="mb-1">
                                                                <strong>{{ $firstRecord->class->name }}</strong>
                                                            </h5>
                                                            <div class="text-muted">
                                                                <i class="ti-calendar"></i> {{ \Carbon\Carbon::parse($firstRecord->date)->format('l, d M Y') }}
                                                                @if($firstRecord->schedule)
                                                                    <span class="ml-3">
                                                                        <i class="ti-time"></i>
                                                                        {{ \Carbon\Carbon::parse($firstRecord->schedule->start_time)->format('H:i') }} - 
                                                                        {{ \Carbon\Carbon::parse($firstRecord->schedule->end_time)->format('H:i') }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="attendance-summary">
                                                            <div class="summary-item">
                                                                <span class="badge badge-success">
                                                                    <i class="ti-check"></i> {{ $sessionStats['present'] }}
                                                                </span>
                                                            </div>
                                                            <div class="summary-item">
                                                                <span class="badge badge-danger">
                                                                    <i class="ti-close"></i> {{ $sessionStats['absent'] }}
                                                                </span>
                                                            </div>
                                                            <div class="summary-item">
                                                                <span class="badge badge-warning">
                                                                    <i class="ti-time"></i> {{ $sessionStats['late'] }}
                                                                </span>
                                                            </div>
                                                            @if($sessionStats['unauthorized'] > 0)
                                                            <div class="summary-item">
                                                                <span class="badge badge-orange">
                                                                    <i class="ti-alert"></i> {{ $sessionStats['unauthorized'] }}
                                                                </span>
                                                            </div>
                                                            @endif
                                                            <div class="summary-item">
                                                                <strong>{{ $sessionStats['rate'] }}%</strong>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <a href="{{ route('teacher.attendance.show', [$firstRecord->date, $firstRecord->class_id, $firstRecord->schedule_id]) }}" 
                                                               class="btn btn-sm btn-info" 
                                                               title="View Details">
                                                                <i class="ti-eye"></i> View
                                                            </a>
                                                            <a href="{{ route('teacher.attendance.edit', [$firstRecord->date, $firstRecord->class_id, $firstRecord->schedule_id]) }}" 
                                                               class="btn btn-sm btn-success" 
                                                               title="Edit Session">
                                                                <i class="ti-pencil-alt"></i> Edit
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Student Grid (Collapsible) -->
                                                <div class="collapse" id="session-{{ $loop->index }}">
                                                    <div class="attendance-grid">
                                                        @foreach($sessionRecords as $record)
                                                        <div class="attendance-item">
                                                            @if($record->student->profile_photo)
                                                                <img src="{{ asset('storage/' . $record->student->profile_photo) }}" 
                                                                     alt="{{ $record->student->full_name }}" 
                                                                     class="student-avatar">
                                                            @else
                                                                <div class="student-initial">
                                                                    {{ strtoupper(substr($record->student->first_name, 0, 1)) }}{{ strtoupper(substr($record->student->last_name, 0, 1)) }}
                                                                </div>
                                                            @endif
                                                            <div style="flex: 1;">
                                                                <strong>{{ $record->student->full_name }}</strong>
                                                            </div>
                                                            @switch($record->status)
                                                                @case('present')
                                                                    <span class="badge badge-success">
                                                                        <i class="ti-check"></i>
                                                                    </span>
                                                                    @break
                                                                @case('absent')
                                                                    <span class="badge badge-danger">
                                                                        <i class="ti-close"></i>
                                                                    </span>
                                                                    @break
                                                                @case('late')
                                                                    <span class="badge badge-warning">
                                                                        <i class="ti-time"></i>
                                                                    </span>
                                                                    @break
                                                                @case('unauthorized')
                                                                    <span class="badge badge-orange">
                                                                        <i class="ti-alert"></i>
                                                                    </span>
                                                                    @break
                                                            @endswitch
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <!-- Toggle Button -->
                                                <div class="card-footer text-center" style="background-color: #f8f9fa; padding: 10px;">
                                                    <button class="btn btn-sm btn-link" 
                                                            type="button" 
                                                            data-toggle="collapse" 
                                                            data-target="#session-{{ $loop->index }}"
                                                            style="text-decoration: none; color: #3386f7;">
                                                        <span class="when-collapsed">
                                                            <i class="ti-angle-down"></i> Show Students ({{ $sessionStats['total'] }})
                                                        </span>
                                                        <span class="when-expanded" style="display: none;">
                                                            <i class="ti-angle-up"></i> Hide Students
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach

                                        <!-- Pagination -->
                                        <div class="mt-4">
                                            {{ $attendanceRecords->appends(request()->query())->links() }}
                                        </div>
                                    @else
                                        <!-- Empty State -->
                                        <div class="empty-state">
                                            <i class="ti-clipboard" style="font-size: 4rem;"></i>
                                            <h3 class="mt-3 mb-2">No attendance records found</h3>
                                            <p class="text-muted mb-4">Try adjusting your filters or date range.</p>
                                            <a href="{{ route('teacher.attendance.create') }}" class="btn btn-mark-attendance">
                                                <i class="ti-plus"></i> Mark Attendance
                                            </a>
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
                                <p>MLC Classroom - Attendance Records</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Toggle collapse button text
    $(document).ready(function() {
        $('.collapse').on('show.bs.collapse', function() {
            $(this).siblings('.card-footer').find('.when-collapsed').hide();
            $(this).siblings('.card-footer').find('.when-expanded').show();
        });
        
        $('.collapse').on('hide.bs.collapse', function() {
            $(this).siblings('.card-footer').find('.when-collapsed').show();
            $(this).siblings('.card-footer').find('.when-expanded').hide();
        });
    });
</script>
@endpush
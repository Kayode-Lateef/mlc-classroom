@extends('layouts.app')

@section('title', 'Attendance Records')

@push('styles')
    <style>
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .status-badge i {
            margin-right: 4px;
            font-size: 1.1rem;
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
            color: #007bff;
        }

        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            margin-top: 20px;
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
                        <span class="text-muted">View and manage system-wide attendance</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
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
                               
                                <a href="{{ route('admin.attendance.create') }}" class="btn btn-success">
                                    <i class="ti-plus"></i> Mark Attendance
                                </a>
                               
                            </div>
                        </div>
                    </div>



                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-2">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-primary">
                                        <i class="ti-clipboard"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ number_format($stats['total_records']) }}</div>
                                        <div class="stat-text" style="font-size: 1.1rem">Total Records</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-danger">
                                        <i class="ti-check"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ number_format($stats['present']) }}</div>
                                        <div class="stat-text">Present</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-success">
                                        <i class="ti-close"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ number_format($stats['absent']) }}</div>
                                        <div class="stat-text">Absent</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-info">
                                        <i class="ti-time"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ number_format($stats['late']) }}</div>
                                        <div class="stat-text">Late</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-warning">
                                        <i class="ti-alert"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ number_format($stats['unauthorized']) }}</div>
                                        <div class="stat-text" style="font-size: 1.2rem">Unauthorized</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-pink">
                                        <i class="ti-stats-up"></i>
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
                                <form method="GET" action="{{ route('admin.attendance.index') }}">
                                    <div class="row">
                                        <!-- Date From -->
                                        <div class="col-md-2">
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
                                        <div class="col-md-2">
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
                                                    @foreach($classes as $class)
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

                                        <!-- Teacher -->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Marked By</label>
                                                <select name="teacher_id" class="form-control">
                                                    <option value="">All Teachers</option>
                                                    @foreach($teachers as $teacher)
                                                    <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                                        {{ $teacher->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Search -->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Search Student</label>
                                                <input 
                                                    type="text" 
                                                    name="search" 
                                                    value="{{ request('search') }}" 
                                                    placeholder="Student name..."
                                                    class="form-control"
                                                >
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">
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

                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="alert alert-success fade in alert-dismissable">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class="ti-check"></i> {{ session('success') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="alert alert-danger fade in alert-dismissable">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class="ti-alert"></i> {{ session('error') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Attendance Records Table -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4>Attendance Records ({{ $attendance->total() }})</h4>
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
                                                    <th>Student</th>
                                                    <th>Class</th>
                                                    <th>Status</th>
                                                    <th>Time</th>
                                                    <th>Marked By</th>
                                                    <th>Notes</th>
                                                    <th>Actions</th>
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
                                                        <div style="display: flex; align-items: center; gap: 10px;">
                                                            @if($record->student->profile_photo)
                                                                <img src="{{ asset('storage/' . $record->student->profile_photo) }}" alt="{{ $record->student->full_name }}" class="student-avatar">
                                                            @else
                                                                <div class="student-initial">
                                                                    {{ strtoupper(substr($record->student->first_name, 0, 1)) }}{{ strtoupper(substr($record->student->last_name, 0, 1)) }}
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <strong>{{ $record->student->full_name }}</strong><br>
                                                                <small class="text-muted">ID: {{ $record->student->id }}</small>
                                                            </div>
                                                        </div>
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
                                                    <td>
                                                        <a href="{{ route('admin.attendance.show', [$record->date, $record->class_id, $record->schedule_id]) }}" 
                                                           class="btn btn-sm btn-info" 
                                                           title="View Session">
                                                            <i class="ti-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.attendance.edit', [$record->date, $record->class_id, $record->schedule_id]) }}" 
                                                           class="btn btn-sm btn-success" 
                                                           title="Edit Session">
                                                            <i class="ti-pencil-alt"></i>
                                                        </a>
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
                                        <a href="{{ route('admin.attendance.create') }}" class="btn btn-primary">
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
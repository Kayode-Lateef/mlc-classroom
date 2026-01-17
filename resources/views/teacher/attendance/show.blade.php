@extends('layouts.app')

@section('title', 'Attendance Session Details')

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
            font-size: 2rem;
        }

        .student-row {
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .student-row:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-color: #3386f7;
        }

        .student-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .student-initial {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            background-color: #e7f3ff;
            color: #3386f7;
        }

        .status-badge-large {
            font-size: 1rem;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
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
                                <h1>Attendance Session</h1>
                            </div>
                        </div>
                        <span class="text-muted">{{ $class->name }} - {{ $attendanceDate->format('d M Y') }}</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('teacher.attendance.index') }}">Attendance</a></li>
                                    <li class="active">Session Details</li>
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
                                            <i class="ti-blackboard"></i> {{ $class->name }}
                                        </h2>
                                        <p style="margin: 0; font-size: 1.1rem; opacity: 0.9; color: white;">
                                            <i class="ti-calendar"></i> {{ $attendanceDate->format('l, d F Y') }}
                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                            <i class="ti-time"></i> {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                        </p>
                                        <p style="margin: 10px 0 0; opacity: 0.9; color: white;">
                                            <i class="ti-user"></i> Teacher: {{ $class->teacher->name ?? 'Not assigned' }}
                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                            <i class="ti-home"></i> Room: {{ $class->room ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <div class="stat-box">
                                            <h3>{{ $stats['attendance_rate'] }}%</h3>
                                            <p style="margin: 0;">Attendance Rate</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-user color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total</div>
                                        <div class="stat-digit">{{ $stats['total'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-check color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Present</div>
                                        <div class="stat-digit">{{ $stats['present'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-close color-danger border-danger"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Absent</div>
                                        <div class="stat-digit">{{ $stats['absent'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-time color-warning border-warning"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Late</div>
                                        <div class="stat-digit">{{ $stats['late'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Session Info & Actions -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <strong><i class="ti-user"></i> Marked By:</strong><br>
                                            <span class="text-muted">{{ $markedBy->name ?? 'Unknown' }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong><i class="ti-time"></i> Marked At:</strong><br>
                                            <span class="text-muted">{{ $markedAt ? $markedAt->format('d M Y, H:i') : 'Unknown' }}</span>
                                            @if($markedAt)
                                            <br><small class="text-muted">{{ $markedAt->diffForHumans() }}</small>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <div class="action-buttons">
                                                <a href="{{ route('teacher.attendance.index') }}" class="btn btn-secondary">
                                                    <i class="ti-arrow-left"></i> Back
                                                </a>
                                                <a href="{{ route('teacher.attendance.edit', [$attendanceDate->format('Y-m-d'), $class->id, $schedule->id]) }}" class="btn btn-success">
                                                    <i class="ti-pencil-alt"></i> Edit
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('teacher.attendance.destroy', [$attendanceDate->format('Y-m-d'), $class->id, $schedule->id]) }}" 
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Are you sure you want to delete this attendance session? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="ti-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Attendance List -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header mb-4">
                                    <h4><i class="ti-list"></i> Student Attendance ({{ $attendanceRecords->count() }})</h4>
                                    @if($attendanceRecords->count() > 0)
                                    <div class="card-header-right-icon">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary active" onclick="filterStatus('all')">
                                                All ({{ $stats['total'] }})
                                            </button>
                                            <button class="btn btn-outline-success" onclick="filterStatus('present')">
                                                Present ({{ $stats['present'] }})
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="filterStatus('absent')">
                                                Absent ({{ $stats['absent'] }})
                                            </button>
                                            <button class="btn btn-outline-warning" onclick="filterStatus('late')">
                                                Late ({{ $stats['late'] }})
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if($attendanceRecords->count() > 0)
                                        @foreach($attendanceRecords as $record)
                                        <div class="student-row" data-status="{{ $record->status }}">
                                            <div class="row align-items-center">
                                                <div class="col-md-5">
                                                    <div style="display: flex; align-items: center; gap: 15px;">
                                                        @if($record->student->profile_photo)
                                                            <img src="{{ asset('storage/' . $record->student->profile_photo) }}" 
                                                                 alt="{{ $record->student->full_name }}" 
                                                                 class="student-avatar">
                                                        @else
                                                            <div class="student-initial">
                                                                {{ strtoupper(substr($record->student->first_name, 0, 1)) }}{{ strtoupper(substr($record->student->last_name, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <strong>{{ $record->student->full_name }}</strong><br>
                                                            <small class="text-muted">
                                                                <i class="ti-id-badge"></i> ID: {{ $record->student->id }}
                                                                @if($record->student->parent)
                                                                &nbsp;|&nbsp; <i class="ti-user"></i> {{ $record->student->parent->name }}
                                                                @endif
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    @switch($record->status)
                                                        @case('present')
                                                            <span class="status-badge-large badge badge-success">
                                                                <i class="ti-check"></i> Present
                                                            </span>
                                                            @break
                                                        @case('absent')
                                                            <span class="status-badge-large badge badge-danger">
                                                                <i class="ti-close"></i> Absent
                                                            </span>
                                                            @break
                                                        @case('late')
                                                            <span class="status-badge-large badge badge-warning">
                                                                <i class="ti-time"></i> Late
                                                            </span>
                                                            @break
                                                        @case('unauthorized')
                                                            <span class="status-badge-large badge badge-orange">
                                                                <i class="ti-alert"></i> Unauthorized
                                                            </span>
                                                            @break
                                                    @endswitch
                                                </div>

                                                <div class="col-md-4">
                                                    @if($record->notes)
                                                        <div style="background-color: #f8f9fa; padding: 8px; border-radius: 4px;">
                                                            <small><strong><i class="ti-comment"></i> Notes:</strong></small><br>
                                                            <small>{{ $record->notes }}</small>
                                                        </div>
                                                    @else
                                                        <small class="text-muted">No notes</small>
                                                    @endif
                                                </div>

                                                <div class="col-md-1 text-right">
                                                    <small class="text-muted">
                                                        <i class="ti-time"></i><br>
                                                        {{ $record->created_at->format('H:i') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-5">
                                            <i class="ti-clipboard" style="font-size: 4rem; color: #cbd5e0;"></i>
                                            <h4 class="mt-3">No Attendance Records</h4>
                                            <p class="text-muted">No attendance has been marked for this session.</p>
                                            <a href="{{ route('teacher.attendance.create', ['date' => $attendanceDate->format('Y-m-d'), 'class_id' => $class->id, 'schedule_id' => $schedule->id]) }}" 
                                               class="btn btn-primary mt-3">
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
                                <p>MLC Classroom - Attendance Session Details</p>
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
    function filterStatus(status) {
        const rows = document.querySelectorAll('.student-row');
        const buttons = document.querySelectorAll('.btn-group button');
        
        // Update active button
        buttons.forEach(btn => {
            btn.classList.remove('active');
            if ((status === 'all' && btn.textContent.includes('All')) ||
                (status !== 'all' && btn.textContent.toLowerCase().includes(status))) {
                btn.classList.add('active');
            }
        });
        
        // Filter rows
        rows.forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = 'block';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endpush
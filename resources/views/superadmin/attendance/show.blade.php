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
            color: #007bff;
        }

        .status-badge-large {
            font-size: 1rem;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 600;
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
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('superadmin.attendance.index') }}">Attendance</a></li>
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
                                        <p style="margin: 0; font-size: 1.1rem; opacity: 0.9;  color: white;">
                                            <i class="ti-calendar"></i> {{ $attendanceDate->format('l, d F Y') }}
                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                            <i class="ti-time"></i> {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                        </p>
                                        <p style="margin: 10px 0 0; opacity: 0.9; color: white;">
                                            <i class="ti-user"></i> Teacher: {{ $class->teacher->name ?? 'Not assigned' }}
                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                            <i class="ti-home"></i> Room: {{ $class->room_number ?? 'N/A' }}
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

                    <!-- Session Info -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Marked By:</strong><br>
                                            {{ $markedBy->name ?? 'Unknown' }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Marked At:</strong><br>
                                            {{ $markedAt ? $markedAt->format('d M Y, H:i') : 'Unknown' }}
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <a href="{{ route('superadmin.attendance.edit', [$attendanceDate->format('Y-m-d'), $class->id, $schedule->id]) }}" class="btn btn-success">
                                                <i class="ti-pencil-alt"></i> Edit Session
                                            </a>
                                            <a href="{{ route('superadmin.attendance.index') }}" class="btn btn-info">
                                                <i class="ti-back-left"></i> Back to List
                                            </a>
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
                                </div>
                                <div class="card-body">
                                    @if($attendanceRecords->count() > 0)
                                        @foreach($attendanceRecords as $record)
                                        <div class="student-row">
                                            <div class="row align-items-center">
                                                <div class="col-md-5">
                                                    <div style="display: flex; align-items: center; gap: 15px;">
                                                        @if($record->student->profile_photo)
                                                            <img src="{{ asset('storage/' . $record->student->profile_photo) }}" alt="{{ $record->student->full_name }}" class="student-avatar">
                                                        @else
                                                            <div class="student-initial">
                                                                {{ strtoupper(substr($record->student->first_name, 0, 1)) }}{{ strtoupper(substr($record->student->last_name, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <strong>{{ $record->student->full_name }}</strong><br>
                                                            <small class="text-muted">Student ID: {{ $record->student->id }}</small>
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
                                                        <small><strong>Notes:</strong> {{ $record->notes }}</small>
                                                    @else
                                                        <small class="text-muted">No notes</small>
                                                    @endif
                                                </div>

                                                <div class="col-md-1 text-right">
                                                    <small class="text-muted">{{ $record->created_at->format('H:i') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-5">
                                            <i class="ti-clipboard" style="font-size: 4rem; color: #cbd5e0;"></i>
                                            <h4 class="mt-3">No Attendance Records</h4>
                                            <p class="text-muted">No attendance has been marked for this session.</p>
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
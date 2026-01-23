@extends('layouts.app')

@section('title', $class->name)

@push('styles')
    <style>
        .profile-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .info-item {
            margin-bottom: 20px;
        }

        .info-label {
            color: #6c757d;
            margin-bottom: 5px;
        }

        .info-value {
            /* font-size: 1rem; */
            color: #212529;
            font-weight: 500;
        }

        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0 5px;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .schedule-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .schedule-card:hover {
            box-shadow: 0 3px 10px rgba(0,123,255,0.1);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            /* font-size: 4rem; */
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .action-button-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .student-avatar-initial {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1rem;
            background-color: #007bff;
            color: white;
        }

        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
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
                                <h1>{{ $class->name }}</h1>
                            </div>
                        </div>
                        <span>{{ $class->description }}</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.classes.index') }}">Classes</a></li>
                                    <li class="active">{{ $class->name }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="action-button-group" style="margin-bottom: 10px; margin-top: 10px;">
                                <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-primary">
                                    <i class="ti-pencil-alt"></i> Edit Class
                                </a>
                                 <form action="{{ route('admin.classes.destroy', $class) }}" 
                                    method="POST" 
                                    style="display: inline-block;"
                                    id="deleteClassForm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="ti-trash"></i> Delete Class
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Class Info Card -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-item">
                                                <div class="info-label">Subject</div>
                                                <div class="info-value">{{ $class->subject }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="info-item">
                                                <div class="info-label">Level</div>
                                                <div class="info-value">{{ $class->level ?? 'N/A' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="info-item">
                                                <div class="info-label">Room Number</div>
                                                <div class="info-value">{{ $class->room_number ?? 'N/A' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="info-item">
                                                <div class="info-label">Teacher</div>
                                                <div class="info-value">{{ $class->teacher->name ?? 'Not assigned' }}</div>
                                            </div>
                                        </div>

                                        @if($class->description)
                                        <div class="col-md-12">
                                            <div class="info-item">
                                                <div class="info-label">Description</div>
                                                <div class="info-value">{{ $class->description }}</div>
                                            </div>
                                        </div>
                                        @endif
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
                                        <div class="stat-text">Enrolled Students</div>
                                        <div class="stat-digit">{{ $stats['enrolled'] }} / {{ $class->capacity }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-calendar color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Schedules</div>
                                        <div class="stat-digit">{{ $class->schedules->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-book color-pink border-pink"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Homework</div>
                                        <div class="stat-digit">{{ $class->homeworkAssignments->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-book color-info border-info"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Progress Sheets</div>
                                        <div class="stat-digit">{{ $class->progressSheets->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                      
                    </div>


                    <!-- Tabs -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="custom-tab">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li role="presentation" class="active">
                                                <a href="#students" aria-controls="students" role="tab" data-toggle="tab">
                                                    <i class="ti-user"></i> Enrolled Students ({{ $stats['enrolled'] }})
                                                </a>
                                            </li>
                                            <li role="presentation">
                                                <a href="#enrollment-history" aria-controls="enrollment-history" role="tab" data-toggle="tab">
                                                    <i class="ti-time"></i> Enrollment History ({{ $enrollmentHistory->count() }})
                                                </a>
                                            </li>
                                            <li role="presentation">
                                                <a href="#schedules" aria-controls="schedules" role="tab" data-toggle="tab">
                                                    <i class="ti-calendar"></i> Schedules
                                                </a>
                                            </li>
                                            <li role="presentation">
                                                <a href="#enroll" aria-controls="enroll" role="tab" data-toggle="tab">
                                                    <i class="ti-plus"></i> Enroll Students
                                                </a>
                                            </li>
                                        </ul>

                                        <div class="tab-content">
                                            <!-- Students Tab -->
                                            <div role="tabpanel" class="tab-pane active" id="students">
                                                @if($class->students->count() > 0)
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Student</th>
                                                                    <th>Parent</th>
                                                                    <th>Enrollment Date</th>
                                                                    <th>Status</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($class->students as $student)
                                                                <tr>
                                                                    <td>
                                                                        <div style="display: flex; align-items: center;">
                                                                            @if($student->profile_photo)
                                                                                <img src="{{ asset('storage/' . $student->profile_photo) }}" alt="{{ $student->full_name }}" class="student-avatar" style="margin-right: 10px;">
                                                                            @else
                                                                                <div class="student-avatar-initial" style="margin-right: 10px;">
                                                                                    {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                                                                </div>
                                                                            @endif
                                                                            <a href="{{ route('admin.students.show', $student) }}" class="text-primary">
                                                                                {{ $student->full_name }}
                                                                            </a>
                                                                        </div>
                                                                    </td>
                                                                    <td>{{ $student->parent->name ?? 'N/A' }}</td>
                                                                    <td>{{ \Carbon\Carbon::parse($student->pivot->enrollment_date)->format('d M Y') }}</td>
                                                                    <td>
                                                                        <span class="badge badge-success">ACTIVE</span>
                                                                    </td>
                                                                    <td>
                                                                        <form action="{{ route('admin.classes.unenroll', [$class, $student]) }}" 
                                                                            method="POST" 
                                                                            style="display: inline-block;"
                                                                            class="unenroll-student-form"
                                                                            data-student-name="{{ $student->full_name }}"
                                                                            data-class-name="{{ $class->name }}">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                                <i class="ti-trash"></i> Remove
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-user"></i>
                                                        <h4>No Students Enrolled</h4>
                                                        <p>Use the "Enroll Students" tab to add students to this class.</p>
                                                    </div>
                                                @endif
                                            </div>

                                             <!-- Enrollment History Tab -->
                                            <div role="tabpanel" class="tab-pane" id="enrollment-history">
                                                @if($enrollmentHistory->count() > 0)
                                                    <!-- Info Alert -->
                                                    <div class="alert alert-info" style="margin-bottom: 20px;">
                                                        <i class="ti-info-alt"></i> 
                                                        <strong>Enrollment History:</strong> This shows complete enrollment records including dropped students. 
                                                        Students with multiple enrollments are grouped together to show their enrollment timeline.
                                                    </div>

                                                    <!-- Enrollment History Cards -->
                                                    <div class="row">
                                                        @foreach($enrollmentHistory as $studentId => $history)
                                                        <div class="col-lg-6 mb-4">
                                                            <div class="card alert">
                                                                <div class="panel lobipanel-basic panel-info">
                                                                    <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
                                                                        <div style="display: flex; align-items: center;">
                                                                            @if($history['student']->profile_photo)
                                                                                <img src="{{ asset('storage/' . $history['student']->profile_photo) }}" 
                                                                                    alt="{{ $history['student']->full_name }}" 
                                                                                    style="width: 40px; height: 40px; border-radius: 50%; margin-right: 10px; border: 2px solid white;">
                                                                            @else
                                                                                <div style="width: 40px; height: 40px; border-radius: 50%; background: white; color: #667eea; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 10px;">
                                                                                    {{ strtoupper(substr($history['student']->first_name, 0, 1)) }}
                                                                                </div>
                                                                            @endif
                                                                            <div>
                                                                                <h5 style="margin: 0; color: white;">
                                                                                    <a href="{{ route('superadmin.students.show', $history['student']) }}" 
                                                                                    style="color: white; text-decoration: none;">
                                                                                        {{ $history['student']->full_name }}
                                                                                    </a>
                                                                                </h5>
                                                                                <small style="opacity: 0.9;">{{ $history['student']->parent->name ?? 'No parent assigned' }}</small>
                                                                            </div>
                                                                        </div>
                                                                        <div>
                                                                            @if($history['current_status'] === 'active')
                                                                                <span class="badge" style="background: #10b981; color: white; padding: 5px 12px;">
                                                                                    <i class="ti-check"></i> ACTIVE
                                                                                </span>
                                                                            @elseif($history['current_status'] === 'dropped')
                                                                                <span class="badge" style="background: #6b7280; color: white; padding: 5px 12px;">
                                                                                    <i class="ti-close"></i> DROPPED
                                                                                </span>
                                                                            @else
                                                                                <span class="badge" style="background: #3b82f6; color: white; padding: 5px 12px;">
                                                                                    <i class="ti-flag"></i> {{ strtoupper($history['current_status']) }}
                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="card-body" style="padding: 20px;">
                                                                    <!-- Enrollment Summary -->
                                                                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; padding: 15px; background: #f9fafb; border-radius: 8px;">
                                                                        <div>
                                                                            <div style="font-size: 11px; color: #6b7280; text-transform: uppercase; font-weight: 600; margin-bottom: 5px;">
                                                                                Total Enrollments
                                                                            </div>
                                                                            <div style="font-size: 24px; font-weight: bold; color: #1f2937;">
                                                                                {{ $history['total_enrollments'] }}
                                                                            </div>
                                                                        </div>
                                                                        <div>
                                                                            <div style="font-size: 11px; color: #6b7280; text-transform: uppercase; font-weight: 600; margin-bottom: 5px;">
                                                                                First Enrolled
                                                                            </div>
                                                                            <div style="font-size: 14px; font-weight: 600; color: #4b5563;">
                                                                                {{ $history['first_enrollment']->format('M d, Y') }}
                                                                            </div>
                                                                        </div>
                                                                        @if($history['total_enrollments'] > 1)
                                                                        <div>
                                                                            <div style="font-size: 11px; color: #6b7280; text-transform: uppercase; font-weight: 600; margin-bottom: 5px;">
                                                                                Last Enrolled
                                                                            </div>
                                                                            <div style="font-size: 14px; font-weight: 600; color: #4b5563;">
                                                                                {{ $history['last_enrollment']->format('M d, Y') }}
                                                                            </div>
                                                                        </div>
                                                                        @endif
                                                                    </div>

                                                                    <!-- Enrollment Timeline -->
                                                                    <div style="margin-top: 20px;">
                                                                        <h6 style="margin-bottom: 15px; color: #374151; font-weight: 600;">
                                                                            <i class="ti-time"></i> Enrollment Timeline
                                                                        </h6>
                                                                        <div style="position: relative; padding-left: 30px;">
                                                                            @foreach($history['enrollments'] as $index => $enrollment)
                                                                            <div style="position: relative; margin-bottom: {{ $loop->last ? '0' : '20px' }};">
                                                                                <!-- Timeline Dot -->
                                                                                <div style="position: absolute; left: -30px; width: 12px; height: 12px; border-radius: 50%; 
                                                                                            background: {{ $enrollment->status === 'active' ? '#10b981' : '#6b7280' }}; 
                                                                                            border: 3px solid white; box-shadow: 0 0 0 2px {{ $enrollment->status === 'active' ? '#10b981' : '#6b7280' }};">
                                                                                </div>
                                                                                
                                                                                <!-- Timeline Line -->
                                                                                @if(!$loop->last)
                                                                                <div style="position: absolute; left: -25px; top: 12px; width: 2px; height: calc(100% + 20px); background: #e5e7eb;"></div>
                                                                                @endif

                                                                                <!-- Enrollment Info -->
                                                                                <div style="padding: 12px; background: white; border: 1px solid #e5e7eb; border-radius: 8px;">
                                                                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                                                                        <div>
                                                                                            <div style="font-weight: 600; color: #1f2937; margin-bottom: 5px;">
                                                                                                Enrollment #{{ $history['total_enrollments'] - $index }}
                                                                                            </div>
                                                                                            <div style="font-size: 13px; color: #6b7280;">
                                                                                                <i class="ti-calendar"></i> 
                                                                                                Enrolled: {{ $enrollment->enrollment_date->format('M d, Y') }}
                                                                                            </div>
                                                                                            <div style="font-size: 13px; color: #6b7280; margin-top: 3px;">
                                                                                                <i class="ti-time"></i> 
                                                                                                {{ $enrollment->created_at->diffForHumans() }}
                                                                                            </div>
                                                                                        </div>
                                                                                        <div>
                                                                                            @if($enrollment->status === 'active')
                                                                                                <span class="badge badge-success">Active</span>
                                                                                            @elseif($enrollment->status === 'dropped')
                                                                                                <span class="badge badge-secondary">Dropped</span>
                                                                                            @else
                                                                                                <span class="badge badge-info">{{ ucfirst($enrollment->status) }}</span>
                                                                                            @endif
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>

                                                                    <!-- Re-enrollment Badge -->
                                                                    @if($history['total_enrollments'] > 1)
                                                                    <div style="margin-top: 15px; padding: 10px; background: #fef3c7; border-radius: 4px;">
                                                                        <i class="ti-reload" style="color: #f59e0b;"></i> 
                                                                        <strong style="color: #92400e;">Re-enrolled Student</strong>
                                                                        <span style="color: #78350f; font-size: 13px;">
                                                                            - This student has enrolled {{ $history['total_enrollments'] }} times
                                                                        </span>
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-time"></i>
                                                        <h4>No Enrollment History</h4>
                                                        <p>No students have been enrolled in this class yet.</p>
                                                    </div>
                                                @endif
                                            </div>   


                                            <!-- Schedules Tab -->
                                            <div role="tabpanel" class="tab-pane" id="schedules">
                                                @if($class->schedules->count() > 0)
                                                    <div style="display: flex; flex-direction: column; gap: 15px;">
                                                        @foreach($class->schedules->sortBy('day_of_week') as $schedule)
                                                        <div class="schedule-card">
                                                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                                                <div>
                                                                    <h4 style="margin-bottom: 10px;">{{ $schedule->day_of_week }}</h4>
                                                                    <p class="text-muted mb-2"><i class="ti-time"></i> {{ $schedule->time_range }}</p>
                                                                    @if($schedule->recurring)
                                                                        <span class="badge badge-info">Recurring</span>
                                                                    @endif
                                                                </div>
                                                                <div>
                                                                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-sm btn-primary">
                                                                        <i class="ti-pencil-alt"></i> Edit
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-calendar"></i>
                                                        <h4>No Schedules Created</h4>
                                                        <p class="mb-4">Create a schedule for this class to set up recurring sessions.</p>
                                                        <a href="{{ route('admin.schedules.create', ['class_id' => $class->id]) }}" class="btn btn-primary">
                                                            <i class="ti-plus"></i> Add Schedule
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Enroll Students Tab -->
                                            <div role="tabpanel" class="tab-pane" id="enroll">
                                                @if($class->isFull())
                                                <div class="warning-box">
                                                    <div class="d-flex">
                                                        <i class="ti-alert mr-3"></i>
                                                        <div>
                                                            <strong>Class at Full Capacity</strong>
                                                            <p class="mb-0">This class has reached its maximum capacity of {{ $class->capacity }} students.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                                <form method="POST" action="{{ route('admin.classes.enroll', $class) }}">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="student_id" class="required-field">Select Student to Enroll</label>
                                                                <select 
                                                                    name="student_id" 
                                                                    id="student_id" 
                                                                    required
                                                                    class="form-control @error('student_id') is-invalid @enderror"
                                                                    {{ $class->isFull() ? 'disabled' : '' }}
                                                                >
                                                                    <option value="">Choose a student...</option>
                                                                    @foreach($availableStudents as $student)
                                                                    <option value="{{ $student->id }}">
                                                                        {{ $student->full_name }} (Parent: {{ $student->parent->name }})
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('student_id')
                                                                <span class="invalid-feedback">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="enrollment_date" class="required-field">Enrollment Date</label>
                                                                <input 
                                                                    type="date" 
                                                                    name="enrollment_date" 
                                                                    id="enrollment_date" 
                                                                    value="{{ date('Y-m-d') }}"
                                                                    required
                                                                    class="form-control @error('enrollment_date') is-invalid @enderror"
                                                                    {{ $class->isFull() ? 'disabled' : '' }}
                                                                >
                                                                @error('enrollment_date')
                                                                <span class="invalid-feedback">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <button 
                                                            type="submit" 
                                                            class="btn btn-primary"
                                                            {{ $class->isFull() ? 'disabled' : '' }}
                                                        >
                                                            <i class="ti-check"></i> Enroll Student
                                                        </button>
                                                    </div>
                                                </form>

                                                @if($availableStudents->isEmpty() && !$class->isFull())
                                                    <div class="alert alert-info">
                                                        <i class="ti-info-alt"></i> All active students are already enrolled in this class.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Class Details</p>
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
        $(document).ready(function() {
            // Handle student unenrollment with SweetAlert
            $('.unenroll-student-form').on('submit', function(e) {
                e.preventDefault(); // Prevent default submission
                
                const form = this;
                const studentName = $(this).data('student-name');
                const className = $(this).data('class-name');
                
                swal({
                    title: "Remove Student from Class?",
                    text: "Are you sure you want to remove '" + studentName + "' from '" + className + "'?\n\nThis will:\n• Mark the enrollment as 'dropped'\n• Keep the enrollment history for records\n• Student can be re-enrolled if needed\n\nDo you want to continue?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, remove student",
                    cancelButtonText: "No, cancel",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function(isConfirm){
                    if (isConfirm) {
                        form.submit(); // Submit the form
                    }
                });
                
                return false;
            });

            // Handle class deletion (if this script is on the show page)
            $('#deleteClassForm').on('submit', function(e) {
                e.preventDefault();
                
                const form = this;
                const className = "{{ $class->name }}";
                const enrolledCount = {{ $class->enrollments()->where('status', 'active')->count() ?? 0 }};
                const scheduleCount = {{ $class->schedules()->count() ?? 0 }};
                
                // Prevent deletion if class has active enrollments
                if (enrolledCount > 0) {
                    swal({
                        title: "Cannot Delete Class!",
                        text: "Class '" + className + "' has " + enrolledCount + " actively enrolled student(s).\n\nPlease remove all students from this class before deleting.\n\nYou can use the 'Remove' button for each student in the Students tab.",
                        type: "error",
                        confirmButtonText: "OK"
                    });
                    return false;
                }
                
                let warningText = "You want to delete class '" + className + "'?\n\n";
                
                if (scheduleCount > 0) {
                    warningText += "⚠️ This class has " + scheduleCount + " scheduled session(s) that will also be deleted.\n\n";
                }
                
                warningText += "This action cannot be undone!";
                
                swal({
                    title: "Are you sure?",
                    text: warningText,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel!",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function(isConfirm){
                    if (isConfirm) {
                        form.submit();
                    }
                });
                
                return false;
            });
        });
    </script>
@endpush

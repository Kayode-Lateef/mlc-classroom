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
                                    <li><a href="{{ route('superadmin.classes.index') }}">Classes</a></li>
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
                                <a href="{{ route('superadmin.classes.edit', $class) }}" class="btn btn-primary">
                                    <i class="ti-pencil-alt"></i> Edit Class
                                </a>
                                <form action="{{ route('superadmin.classes.destroy', $class) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this class? This action cannot be undone.');">
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
                                                                            <a href="{{ route('superadmin.students.show', $student) }}" class="text-primary">
                                                                                {{ $student->full_name }}
                                                                            </a>
                                                                        </div>
                                                                    </td>
                                                                    <td>{{ $student->parent->name }}</td>
                                                                    <td>{{ \Carbon\Carbon::parse($student->pivot->enrollment_date)->format('d M Y') }}</td>
                                                                    <td>
                                                                        @if($student->pivot->status === 'active')
                                                                            <span class="badge badge-success">Active</span>
                                                                        @else
                                                                            <span class="badge badge-secondary">{{ ucfirst($student->pivot->status) }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <form action="{{ route('superadmin.classes.unenroll', [$class, $student]) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to remove this student from the class?');">
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
                                                                    <a href="{{ route('superadmin.schedules.edit', $schedule) }}" class="btn btn-sm btn-primary">
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
                                                        <a href="{{ route('superadmin.schedules.create', ['class_id' => $class->id]) }}" class="btn btn-primary">
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

                                                <form method="POST" action="{{ route('superadmin.classes.enroll', $class) }}">
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

                    <!-- Success Messages -->
                    @if(session('success'))
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="alert alert-success alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <i class="ti-check"></i> {{ session('success') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <i class="ti-alert"></i> {{ session('error') }}
                                </div>
                            </div>
                        </div>
                    @endif

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
            // Tab functionality is handled by Bootstrap
        });
    </script>
@endpush
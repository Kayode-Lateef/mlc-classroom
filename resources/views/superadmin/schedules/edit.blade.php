@extends('layouts.app')

@section('title', 'Edit Schedule')

@push('styles')
    <style>
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .required-field::after {
            content: " *";
            color: #dc3545;
        }

        .schedule-info-card {
            background-color: #e7f3ff;
            padding: 15px;
            margin-bottom: 20px;
        }

        .warning-box {
            background-color: #fff3cd;
            border-radius: 8px;
            padding: 15px;
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
                                <h1>Edit Schedule</h1>
                            </div>
                        </div>
                        <span>Modify existing class schedule</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.schedules.index') }}">Schedules</a></li>
                                    <li><a href="{{ route('superadmin.schedules.show', $schedule) }}">{{ $schedule->class->name }}</a></li>
                                    <li class="active">Edit</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
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

                    <div class="row">
                        <!-- Form Section -->
                        <div class="col-lg-8">
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <h4><i class="ti-calendar"></i> Edit Schedule Details</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Current Schedule Info -->
                                    <div class="schedule-info-card">
                                        <h5 class="mb-2">
                                            <i class="ti-info-alt"></i> Current Schedule
                                        </h5>
                                        <p class="mb-1">
                                            <strong>Class:</strong> {{ $schedule->class->name }}
                                        </p>
                                        <p class="mb-1">
                                            <strong>Day:</strong> {{ $schedule->day_of_week }}
                                        </p>
                                        <p class="mb-1">
                                            <strong>Time:</strong> {{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}
                                        </p>
                                        <p class="mb-0">
                                            <strong>Recurring:</strong> 
                                            @if($schedule->recurring)
                                                <span class="badge badge-success">Yes</span>
                                            @else
                                                <span class="badge badge-secondary">No</span>
                                            @endif
                                        </p>
                                    </div>

                                    <form method="POST" action="{{ route('superadmin.schedules.update', $schedule) }}">
                                        @csrf
                                        @method('PUT')

                                        <!-- Class Selection -->
                                        <div class="form-group">
                                            <label for="class_id" class="required-field">Select Class</label>
                                            <select 
                                                name="class_id" 
                                                id="class_id" 
                                                required
                                                class="form-control @error('class_id') is-invalid @enderror"
                                            >
                                                <option value="">-- Select a class --</option>
                                                @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ old('class_id', $schedule->class_id) == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }} 
                                                    @if($class->teacher)
                                                    - {{ $class->teacher->name }}
                                                    @endif
                                                    @if($class->room_number)
                                                    (Room {{ $class->room_number }})
                                                    @endif
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('class_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <i class="ti-info-alt"></i> Change the class for this schedule if needed
                                            </small>
                                        </div>

                                        <!-- Day of Week -->
                                        <div class="form-group">
                                            <label for="day_of_week" class="required-field">Day of Week</label>
                                            <select 
                                                name="day_of_week" 
                                                id="day_of_week" 
                                                required
                                                class="form-control @error('day_of_week') is-invalid @enderror"
                                            >
                                                <option value="">-- Select day --</option>
                                                @foreach($days as $day)
                                                <option value="{{ $day }}" {{ old('day_of_week', $schedule->day_of_week) == $day ? 'selected' : '' }}>
                                                    {{ $day }}
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('day_of_week')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <i class="ti-time"></i> Change the day when this class meets
                                            </small>
                                        </div>

                                        <!-- Time Range -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="start_time" class="required-field">Start Time</label>
                                                    <input 
                                                        type="time" 
                                                        name="start_time" 
                                                        id="start_time" 
                                                        value="{{ old('start_time', $schedule->start_time->format('H:i')) }}"
                                                        required
                                                        class="form-control @error('start_time') is-invalid @enderror"
                                                    >
                                                    @error('start_time')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="end_time" class="required-field">End Time</label>
                                                    <input 
                                                        type="time" 
                                                        name="end_time" 
                                                        id="end_time" 
                                                        value="{{ old('end_time', $schedule->end_time->format('H:i')) }}"
                                                        required
                                                        class="form-control @error('end_time') is-invalid @enderror"
                                                    >
                                                    @error('end_time')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-info">
                                            <i class="ti-info-alt"></i> 
                                            <strong>Time Format:</strong> Use 24-hour format (e.g., 09:00, 14:00)
                                        </div>

                                        <!-- Recurring -->
                                        <div class="form-group">
                                            <div class="checkbox">
                                                <label>
                                                    <input 
                                                        type="checkbox" 
                                                        name="recurring" 
                                                        value="1"
                                                        {{ old('recurring', $schedule->recurring) ? 'checked' : '' }}
                                                    >
                                                    <strong>Recurring Schedule</strong>
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="ti-reload"></i> If checked, this schedule will repeat every week
                                            </small>
                                        </div>

                                        <!-- Warning about attendance -->
                                        @if($schedule->attendance()->count() > 0)
                                        <div class="warning-box">
                                            <div class="d-flex">
                                                <i class="ti-alert mr-3"></i>
                                                <div>
                                                    <strong>Warning:</strong> This schedule has {{ $schedule->attendance()->count() }} attendance record(s). 
                                                    Changing the schedule may affect existing attendance data.
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('superadmin.schedules.show', $schedule) }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti-check"></i> Update Schedule
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar: Related Info -->
                        <div class="col-lg-4">
                            <!-- Schedule Statistics -->
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <h4><i class="ti-stats-up"></i> Schedule Statistics</h4>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Attendance Records:</strong>
                                        <span class="float-right badge badge-primary">
                                            {{ $schedule->attendance()->count() }}
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Progress Sheets:</strong>
                                        <span class="float-right badge badge-info">
                                            {{ $schedule->progressSheets()->count() }}
                                        </span>
                                    </div>
                                    <div class="mb-0">
                                        <strong>Created:</strong>
                                        <span class="float-right text-muted">
                                            {{ $schedule->created_at->format('d M Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Class Information -->
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <h4><i class="ti-blackboard"></i> Class Information</h4>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Class Name:</strong>
                                        <p class="mb-0">{{ $schedule->class->name }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Subject:</strong>
                                        <p class="mb-0">{{ $schedule->class->subject }}</p>
                                    </div>
                                    @if($schedule->class->teacher)
                                    <div class="mb-3">
                                        <strong>Teacher:</strong>
                                        <p class="mb-0">{{ $schedule->class->teacher->name }}</p>
                                    </div>
                                    @endif
                                    @if($schedule->class->room_number)
                                    <div class="mb-0">
                                        <strong>Room:</strong>
                                        <p class="mb-0">{{ $schedule->class->room_number }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <h4><i class="ti-settings"></i> Quick Actions</h4>
                                </div>
                                <div class="card-body">
                                    <a href="{{ route('superadmin.schedules.show', $schedule) }}" class="btn btn-info btn-block mb-2">
                                        <i class="ti-eye"></i> View Schedule
                                    </a>
                                    <a href="{{ route('superadmin.classes.show', $schedule->class) }}" class="btn btn-primary btn-block mb-2">
                                        <i class="ti-blackboard"></i> View Class
                                    </a>
                                    @if($schedule->attendance()->count() === 0)
                                    <form action="{{ route('superadmin.schedules.destroy', $schedule) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-block">
                                            <i class="ti-trash"></i> Delete Schedule
                                        </button>
                                    </form>
                                    @else
                                    <button type="button" class="btn btn-secondary btn-block" disabled title="Cannot delete schedule with attendance records">
                                        <i class="ti-lock"></i> Delete Disabled
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Edit Schedule</p>
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
            // Track original values for change detection
            const originalValues = {
                class_id: $('#class_id').val(),
                day_of_week: $('#day_of_week').val(),
                start_time: $('#start_time').val(),
                end_time: $('#end_time').val(),
                room: $('#room').val()
            };

            // Time validation with SweetAlert
            $('#end_time').on('change', function() {
                const startTime = $('#start_time').val();
                const endTime = $(this).val();
                
                if (startTime && endTime && endTime <= startTime) {
                    swal({
                        title: "Invalid Time!",
                        text: "End time must be after start time.\n\nPlease select a later time.",
                        type: "error",
                        confirmButtonText: "OK"
                    }, function() {
                        $('#end_time').val('{{ $schedule->end_time->format('H:i') }}').focus();
                    });
                }
            });

            // Highlight changed fields
            $('select, input').on('change', function() {
                const fieldName = $(this).attr('id');
                const currentValue = $(this).val();
                
                if (originalValues[fieldName] && originalValues[fieldName] !== currentValue) {
                    $(this).addClass('border-warning');
                } else {
                    $(this).removeClass('border-warning');
                }
            });

            // Form submission validation with SweetAlert
            $('form').on('submit', function(e) {
                e.preventDefault(); // Prevent default submission
                
                const form = this;
                const startTime = $('#start_time').val();
                const endTime = $('#end_time').val();
                const attendanceCount = {{ $schedule->attendance()->count() ?? 0 }};

                // Validate time order
                if (endTime <= startTime) {
                    swal({
                        title: "Invalid Time Range!",
                        text: "End time must be after start time.\n\nStart: " + startTime + "\nEnd: " + endTime,
                        type: "error",
                        confirmButtonText: "OK"
                    }, function() {
                        $('#end_time').focus();
                    });
                    return false;
                }

                // Check if any fields were changed
                let hasChanges = false;
                let changedFields = [];
                
                for (let field in originalValues) {
                    const currentValue = $('#' + field).val();
                    if (originalValues[field] !== currentValue) {
                        hasChanges = true;
                        changedFields.push(field.replace('_', ' ').toUpperCase());
                    }
                }

                if (!hasChanges) {
                    swal({
                        title: "No Changes Detected",
                        text: "You haven't made any changes to this schedule.",
                        type: "info",
                        confirmButtonText: "OK"
                    });
                    return false;
                }

                // Calculate duration
                const start = new Date('2000-01-01 ' + startTime);
                const end = new Date('2000-01-01 ' + endTime);
                const durationMinutes = (end - start) / (1000 * 60);

                // Warn about very short classes
                if (durationMinutes < 30) {
                    swal({
                        title: "Very Short Class!",
                        text: "This class is only " + durationMinutes + " minutes long.\n\nAre you sure this is correct?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f0ad4e",
                        confirmButtonText: "Yes, continue",
                        cancelButtonText: "No, let me change",
                        closeOnConfirm: false
                    }, function(isConfirm) {
                        if (isConfirm) {
                            proceedWithSubmission(form, attendanceCount);
                        } else {
                            $('#start_time').focus();
                        }
                    });
                    return false;
                }

                // Warn about very long classes
                if (durationMinutes > 240) {
                    swal({
                        title: "Very Long Class!",
                        text: "This class is " + (durationMinutes / 60).toFixed(1) + " hours long.\n\nAre you sure this is correct?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f0ad4e",
                        confirmButtonText: "Yes, continue",
                        cancelButtonText: "No, let me change",
                        closeOnConfirm: false
                    }, function(isConfirm) {
                        if (isConfirm) {
                            proceedWithSubmission(form, attendanceCount);
                        } else {
                            $('#end_time').focus();
                        }
                    });
                    return false;
                }

                // Proceed with submission
                proceedWithSubmission(form, attendanceCount);
                
                return false;
            });

            // Function to handle submission with attendance warning
            function proceedWithSubmission(form, attendanceCount) {
                // Warn if schedule has existing attendance records
                if (attendanceCount > 0) {
                    swal({
                        title: "Schedule Has Attendance Records!",
                        text: "⚠️ This schedule has " + attendanceCount + " attendance record(s).\n\nUpdating this schedule may affect:\n• Existing attendance data\n• Class reports\n• Student records\n\nAre you sure you want to proceed?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, update schedule",
                        cancelButtonText: "No, cancel",
                        closeOnConfirm: false
                    }, function(isConfirm) {
                        if (isConfirm) {
                            $('#submitBtn').prop('disabled', true).html('<i class="ti-reload fa-spin"></i> Updating Schedule...');
                            form.submit();
                        }
                    });
                } else {
                    // No attendance records, just submit
                    $('#submitBtn').prop('disabled', true).html('<i class="ti-reload fa-spin"></i> Updating Schedule...');
                    form.submit();
                }
            }
        });
    </script>
@endpush
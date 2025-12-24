@extends('layouts.app')

@section('title', 'Create Schedule')

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

        .guideline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }

        .guideline-icon {
            color: #007bff;
            margin-right: 10px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .time-slot-example {
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin-bottom: 8px;
            font-family: monospace;
            font-size: 1.3rem;
        }

        .help-card {
            position: sticky;
            top: 20px;
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
                                <h1>Create New Schedule</h1>
                            </div>
                        </div>
                        <span>Add a new class schedule to the timetable</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.schedules.index') }}">Schedules</a></li>
                                    <li class="active">Create</li>
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
                                <div class="card-header">
                                    <h4><i class="ti-calendar"></i> Schedule Details</h4>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('superadmin.schedules.store') }}">
                                        @csrf

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
                                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
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
                                                <i class="ti-info-alt"></i> Select the class for this schedule
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
                                                <option value="{{ $day }}" {{ old('day_of_week') == $day ? 'selected' : '' }}>
                                                    {{ $day }}
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('day_of_week')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <i class="ti-time"></i> Choose the day when this class meets
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
                                                        value="{{ old('start_time', '09:00') }}"
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
                                                        value="{{ old('end_time', '10:00') }}"
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
                                                        {{ old('recurring', true) ? 'checked' : '' }}
                                                    >
                                                    <strong>Recurring Schedule</strong>
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="ti-reload"></i> If checked, this schedule will repeat every week
                                            </small>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('superadmin.schedules.index') }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti-check"></i> Create Schedule
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar: Guidelines & Help -->
                        <div class="col-lg-4">
                            <div class="card alert help-card">
                                <div class="card-header">
                                    <h4><i class="ti-help-alt"></i> Schedule Guidelines</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Guidelines -->
                                    <div class="guideline-item">
                                        <i class="ti-check guideline-icon"></i>
                                        <p class="mb-0">Each class can have multiple schedules on different days</p>
                                    </div>

                                    <div class="guideline-item">
                                        <i class="ti-check guideline-icon"></i>
                                        <p class="mb-0">The system will check for schedule conflicts automatically</p>
                                    </div>

                                    <div class="guideline-item">
                                        <i class="ti-check guideline-icon"></i>
                                        <p class="mb-0">Recurring schedules repeat every week automatically</p>
                                    </div>

                                    <div class="guideline-item">
                                        <i class="ti-check guideline-icon"></i>
                                        <p class="mb-0">Teachers can view their schedules on their dashboard</p>
                                    </div>

                                    <div class="guideline-item">
                                        <i class="ti-check guideline-icon"></i>
                                        <p class="mb-0">Students see their schedules through class enrollments</p>
                                    </div>

                                    <!-- Common Time Slots -->
                                    <div class="mt-4 pt-3 border-top">
                                        <h5 class="mb-3">
                                            <i class="ti-time"></i> Common Time Slots
                                        </h5>
                                        
                                        <div class="time-slot-example">
                                            <strong>Morning:</strong> 09:00 - 10:00
                                        </div>
                                        
                                        <div class="time-slot-example">
                                            <strong>Mid-Morning:</strong> 10:00 - 11:00
                                        </div>
                                        
                                        <div class="time-slot-example">
                                            <strong>Late Morning:</strong> 11:00 - 12:00
                                        </div>
                                        
                                        <div class="time-slot-example">
                                            <strong>Afternoon:</strong> 13:00 - 14:00
                                        </div>
                                        
                                        <div class="time-slot-example">
                                            <strong>Mid-Afternoon:</strong> 14:00 - 15:00
                                        </div>
                                        
                                        <div class="time-slot-example">
                                            <strong>Late Afternoon:</strong> 15:00 - 16:00
                                        </div>
                                    </div>

                                    <!-- Conflict Warning -->
                                    <div class="alert alert-warning text-dark mt-4">
                                        <i class="ti-alert"></i>
                                        <strong>Note:</strong> The same class cannot have overlapping schedules on the same day.
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Tips Card -->
                            <div class="card alert mt-3">
                                <div class="card-header">
                                    <h4><i class="ti-light-bulb"></i> Quick Tips</h4>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="ti-angle-right text-primary"></i> 
                                            <small>Create all schedules before marking attendance</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="ti-angle-right text-primary"></i> 
                                            <small>Use consistent time slots for better organization</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="ti-angle-right text-primary"></i> 
                                            <small>Allow 5-10 minute breaks between classes</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="ti-angle-right text-primary"></i> 
                                            <small>Review the calendar view to spot conflicts</small>
                                        </li>
                                        <li class="mb-0">
                                            <i class="ti-angle-right text-primary"></i> 
                                            <small>Assign teachers before creating schedules</small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Create Schedule</p>
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
            // Time validation
            $('#end_time').on('change', function() {
                const startTime = $('#start_time').val();
                const endTime = $(this).val();
                
                if (startTime && endTime && endTime <= startTime) {
                    alert('End time must be after start time!');
                    $(this).val('');
                }
            });

            // Show selected class details
            $('#class_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const className = selectedOption.text();
                
                if ($(this).val()) {
                    console.log('Selected class:', className);
                }
            });

            // Day selection highlight
            $('#day_of_week').on('change', function() {
                if ($(this).val()) {
                    $(this).removeClass('border-gray-300').addClass('border-success');
                } else {
                    $(this).removeClass('border-success').addClass('border-gray-300');
                }
            });

            // Form submission validation
            $('form').on('submit', function(e) {
                const startTime = $('#start_time').val();
                const endTime = $('#end_time').val();
                
                if (endTime <= startTime) {
                    e.preventDefault();
                    alert('End time must be after start time!');
                    return false;
                }
            });
        });
    </script>
@endpush
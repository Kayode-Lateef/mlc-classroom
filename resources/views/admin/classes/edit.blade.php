@extends('layouts.app')

@section('title', 'Edit Class')

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

        .info-box {
            background-color: #fff3cd;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }

        .info-box i {
            color: #ffc107;
            font-size: 1.2rem;
        }

        .warning-box {
            background-color: #fff3cd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .form-helper-text {
            font-size: 1.2rem;
            color: #6c757d;
            margin-top: 5px;
        }

        .form-helper-text i {
            margin-right: 5px;
        }

        .current-info-badge {
            display: inline-block;
            padding: 4px 10px;
            background-color: #e7f3ff;
            border-radius: 4px;
            font-size: 1rem;
            color: #0066cc;
            margin-top: 5px;
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
                                <h1>Edit Class: {{ $class->name }}</h1>
                            </div>
                        </div>
                        <span>{{ $class->description }}</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.classes.index') }}">Classes</a></li>
                                    <li><a href="{{ route('admin.classes.show', $class) }}">{{ $class->name }}</a></li>
                                    <li class="active">Edit</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <!-- Capacity Warning -->
                                    @php
                                        $enrolledCount = $class->enrollments()->where('status', 'active')->count();
                                    @endphp
                                    @if($enrolledCount > 0)
                                    <div class="warning-box">
                                        <div class="d-flex">
                                            <i class="ti-info-alt mr-3"></i>
                                            <div>
                                                <strong>Current Enrollment: {{ $enrolledCount }} student(s)</strong>
                                                <p class="mb-0 mt-1">You cannot reduce the capacity below the current number of enrolled students.</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <form method="POST" action="{{ route('admin.classes.update', $class) }}">
                                        @csrf
                                        @method('PUT')

                                        <!-- Basic Information Section -->
                                        <div class="form-section">
                                            <h4 class="mb-3">
                                                <i class="ti-info-alt"></i> Basic Information
                                            </h4>
                                            <div class="row">
                                                <!-- Class Name -->
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="name" class="required-field">Class Name</label>
                                                        <input 
                                                            type="text" 
                                                            name="name" 
                                                            id="name" 
                                                            value="{{ old('name', $class->name) }}" 
                                                            placeholder="e.g. Maths 11+, English GCSE, Year 6 Science"
                                                            required
                                                            class="form-control @error('name') is-invalid @enderror"
                                                        >
                                                        <small class="form-helper-text">
                                                            <i class="ti-info"></i> Descriptive name for this class
                                                        </small>
                                                        @error('name')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Subject -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="subject" class="required-field">Subject</label>
                                                        <input 
                                                            type="text" 
                                                            name="subject" 
                                                            id="subject" 
                                                            value="{{ old('subject', $class->subject) }}" 
                                                            placeholder="e.g. Mathematics, English, Science"
                                                            required
                                                            class="form-control @error('subject') is-invalid @enderror"
                                                        >
                                                        <span class="current-info-badge">
                                                            <i class="ti-book"></i> Current: {{ $class->subject }}
                                                        </span>
                                                        @error('subject')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Level -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="level">Level</label>
                                                        <input 
                                                            type="text" 
                                                            name="level" 
                                                            id="level" 
                                                            value="{{ old('level', $class->level) }}" 
                                                            placeholder="e.g. Year 6, 11+, GCSE, A-Level"
                                                            class="form-control @error('level') is-invalid @enderror"
                                                        >
                                                        @if($class->level)
                                                        <span class="current-info-badge">
                                                            <i class="ti-bar-chart"></i> Current: {{ $class->level }}
                                                        </span>
                                                        @else
                                                        <small class="form-helper-text">
                                                            <i class="ti-bar-chart"></i> No level currently set
                                                        </small>
                                                        @endif
                                                        @error('level')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Class Details Section -->
                                        <div class="form-section">
                                            <h4 class="mb-3">
                                                <i class="ti-settings"></i> Class Details
                                            </h4>
                                            <div class="row">
                                                <!-- Room Number -->
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="room_number">Room Number</label>
                                                        <input 
                                                            type="text" 
                                                            name="room_number" 
                                                            id="room_number" 
                                                            value="{{ old('room_number', $class->room_number) }}" 
                                                            placeholder="e.g. A101, Main Hall, Lab 2"
                                                            class="form-control @error('room_number') is-invalid @enderror"
                                                        >
                                                        @if($class->room_number)
                                                        <span class="current-info-badge">
                                                            <i class="ti-home"></i> Current: {{ $class->room_number }}
                                                        </span>
                                                        @else
                                                        <small class="form-helper-text">
                                                            <i class="ti-home"></i> No room assigned
                                                        </small>
                                                        @endif
                                                        @error('room_number')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Teacher -->
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="teacher_id">Teacher</label>
                                                        <select 
                                                            name="teacher_id" 
                                                            id="teacher_id"
                                                            class="form-control @error('teacher_id') is-invalid @enderror"
                                                        >
                                                            <option value="">No teacher assigned</option>
                                                            @foreach($teachers as $teacher)
                                                            <option value="{{ $teacher->id }}" {{ old('teacher_id', $class->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                                                {{ $teacher->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                        @if($class->teacher)
                                                        <span class="current-info-badge">
                                                            <i class="ti-user"></i> Current: {{ $class->teacher->name }}
                                                        </span>
                                                        @else
                                                        <small class="form-helper-text">
                                                            <i class="ti-user"></i> No teacher currently assigned
                                                        </small>
                                                        @endif
                                                        @error('teacher_id')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Capacity -->
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="capacity" class="required-field">Capacity</label>
                                                        <input 
                                                            type="number" 
                                                            name="capacity" 
                                                            id="capacity" 
                                                            value="{{ old('capacity', $class->capacity) }}" 
                                                            min="{{ $enrolledCount }}"
                                                            max="100"
                                                            required
                                                            class="form-control @error('capacity') is-invalid @enderror"
                                                        >
                                                        <small class="form-helper-text" id="capacity-helper">
                                                            <i class="ti-id-badge"></i> Currently enrolled: {{ $enrolledCount }} student(s)
                                                        </small>
                                                        @error('capacity')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Description -->
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="description">Description</label>
                                                        <textarea 
                                                            name="description" 
                                                            id="description" 
                                                            rows="4"
                                                            class="form-control @error('description') is-invalid @enderror"
                                                            placeholder="Add any additional information about this class, curriculum details, objectives, etc..."
                                                        >{{ old('description', $class->description) }}</textarea>
                                                        <small class="form-helper-text">
                                                            <i class="ti-write"></i> Optional: Additional details about the class
                                                        </small>
                                                        @error('description')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Info Box -->
                                        <div class="info-box">
                                            <div class="d-flex">
                                                <i class="ti-info-alt mr-3"></i>
                                                <div>
                                                    <strong>Class Information</strong>
                                                    <ul class="mb-0 mt-2">
                                                        <li>Created: {{ $class->created_at->format('d M Y, H:i') }}</li>
                                                        <li>Last Updated: {{ $class->updated_at->diffForHumans() }}</li>
                                                        <li>Active Students: {{ $enrolledCount }}</li>
                                                        @if($class->schedules->count() > 0)
                                                            <li>Schedules: {{ $class->schedules->count() }} session(s)</li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('admin.classes.show', $class) }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti-check"></i> Update Class
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Edit Class</p>
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
            var currentEnrolled = {{ $enrolledCount }};

            // Capacity validation with dynamic warnings
            $('#capacity').on('input', function() {
                var capacity = parseInt($(this).val());
                var helper = $('#capacity-helper');
                
                if (capacity < currentEnrolled) {
                    helper.html('<i class="ti-alert"></i> Cannot be less than current enrollment (' + currentEnrolled + ' students)')
                           .css('color', '#dc3545');
                    $(this).addClass('is-invalid');
                } else if (capacity > 30) {
                    helper.html('<i class="ti-alert"></i> Large class size - consider splitting into multiple classes')
                           .css('color', '#ffc107');
                    $(this).removeClass('is-invalid');
                } else if (capacity - currentEnrolled < 5 && capacity - currentEnrolled > 0) {
                    helper.html('<i class="ti-info"></i> Only ' + (capacity - currentEnrolled) + ' spots remaining')
                           .css('color', '#28a745');
                    $(this).removeClass('is-invalid');
                } else {
                    helper.html('<i class="ti-id-badge"></i> Currently enrolled: ' + currentEnrolled + ' student(s)')
                           .css('color', '#6c757d');
                    $(this).removeClass('is-invalid');
                }
            });

            // Teacher change confirmation
            var originalTeacherId = '{{ $class->teacher_id ?? "" }}';
            $('#teacher_id').on('change', function() {
                var newTeacherId = $(this).val();
                
                if (originalTeacherId && newTeacherId && originalTeacherId != newTeacherId) {
                    if (!confirm('Are you sure you want to change the teacher? This may affect scheduled classes and communications.')) {
                        $(this).val(originalTeacherId);
                    }
                }
            });

            // Form validation before submit
            $('form').on('submit', function(e) {
                var capacity = parseInt($('#capacity').val());
                
                if (capacity < currentEnrolled) {
                    e.preventDefault();
                    alert('Capacity cannot be less than the current number of enrolled students (' + currentEnrolled + ').');
                    $('#capacity').focus();
                    return false;
                }

                if (capacity < 1 || capacity > 100) {
                    e.preventDefault();
                    alert('Capacity must be between 1 and 100 students.');
                    $('#capacity').focus();
                    return false;
                }

                return true;
            });

            // Auto-capitalize first letter
            $('#name, #subject').on('blur', function() {
                var value = $(this).val();
                if (value.length > 0) {
                    $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
                }
            });
        });
    </script>
@endpush
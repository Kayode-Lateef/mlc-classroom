@extends('layouts.app')

@section('title', 'Add Class')

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
            background-color: #e7f3ff;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }

        .info-box i {
            color: #0066cc;
            font-size: 1.2rem;
        }

        .form-helper-text {
            font-size: 1.2rem;
            color: #6c757d;
            margin-top: 5px;
        }

        .form-helper-text i {
            margin-right: 5px;
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
                                <h1>Add New Class</h1>
                            </div>
                        </div>
                        <span>Define a new class for the system</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.classes.index') }}">Classes</a></li>
                                    <li class="active">Add Class</li>
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
                                    <form method="POST" action="{{ route('superadmin.classes.store') }}">
                                        @csrf

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
                                                            value="{{ old('name') }}" 
                                                            placeholder="e.g. Maths 11+, English GCSE, Year 6 Science"
                                                            required
                                                            class="form-control @error('name') is-invalid @enderror"
                                                        >
                                                        <small class="form-helper-text">
                                                            <i class="ti-info"></i> Enter a descriptive name for this class
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
                                                            value="{{ old('subject') }}" 
                                                            placeholder="e.g. Mathematics, English, Science"
                                                            required
                                                            class="form-control @error('subject') is-invalid @enderror"
                                                        >
                                                        <small class="form-helper-text">
                                                            <i class="ti-book"></i> Main subject area
                                                        </small>
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
                                                            value="{{ old('level') }}" 
                                                            placeholder="e.g. Year 6, 11+, GCSE, A-Level"
                                                            class="form-control @error('level') is-invalid @enderror"
                                                        >
                                                        <small class="form-helper-text">
                                                            <i class="ti-bar-chart"></i> Educational level (optional)
                                                        </small>
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
                                                            value="{{ old('room_number') }}" 
                                                            placeholder="e.g. A101, Main Hall, Lab 2"
                                                            class="form-control @error('room_number') is-invalid @enderror"
                                                        >
                                                        <small class="form-helper-text">
                                                            <i class="ti-home"></i> Physical location of the class
                                                        </small>
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
                                                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                                                {{ $teacher->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                        <small class="form-helper-text">
                                                            <i class="ti-user"></i> You can assign a teacher later if needed
                                                        </small>
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
                                                            value="{{ old('capacity') }}" 
                                                            min="1"
                                                            max="{{ (int) \App\Models\SystemSetting::get('max_class_capacity', 100) }}"max="100"
                                                            required
                                                            class="form-control @error('capacity') is-invalid @enderror"
                                                        >
                                                        <small class="form-helper-text">
                                                            <i class="ti-id-badge"></i> Maximum number of students
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
                                                        >{{ old('description') }}</textarea>
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
                                                    <strong>Next Steps</strong>
                                                    <p class="mb-0">After creating the class, you can:</p>
                                                    <ul class="mb-0 mt-2">
                                                        <li>Set up class schedules</li>
                                                        <li>Enrol students into the class</li>
                                                        <li>Assign homework and track progress</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('superadmin.classes.index') }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti-check"></i> Add Class
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
                                <p>MLC Classroom - Add Class</p>
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
            // Auto-capitalize first letter of class name
            $('#name').on('blur', function() {
                var value = $(this).val();
                if (value.length > 0) {
                    $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
                }
            });

            // Capacity validation warning
            $('#capacity').on('input', function() {
                var capacity = parseInt($(this).val());
                var helper = $(this).siblings('.form-helper-text');
                
                if (capacity > 30) {
                    helper.html('<i class="ti-alert"></i> Large class size - consider splitting into multiple classes')
                           .css('color', '#ffc107');
                } else if (capacity < 5) {
                    helper.html('<i class="ti-info"></i> Small class size - perfect for focused learning')
                           .css('color', '#28a745');
                } else {
                    helper.html('<i class="ti-id-badge"></i> Maximum number of students')
                           .css('color', '#6c757d');
                }
            });

            // Form validation before submit with SweetAlert
            $('form').on('submit', function(e) {
                e.preventDefault(); // Prevent default submission
                
                const form = this;
                var name = $('#name').val().trim();
                var subject = $('#subject').val().trim();
                var capacity = parseInt($('#capacity').val());

                // Validate class name
                if (name.length < 3) {
                    swal({
                        title: "Invalid Class Name!",
                        text: "Class name must be at least 3 characters long.",
                        type: "error",
                        confirmButtonText: "OK"
                    }, function() {
                        $('#name').focus();
                    });
                    return false;
                }

                // Validate subject
                if (subject.length < 2) {
                    swal({
                        title: "Invalid Subject!",
                        text: "Subject name must be at least 2 characters long.",
                        type: "error",
                        confirmButtonText: "OK"
                    }, function() {
                        $('#subject').focus();
                    });
                    return false;
                }

                // Validate capacity
              if (capacity < 1 || capacity > {{ (int) \App\Models\SystemSetting::get('max_class_capacity', 100) }}) {
                    swal({
                        title: "Invalid Capacity!",
                        text: "Capacity must be between 1 and 100 students.",
                        type: "error",
                        confirmButtonText: "OK"
                    }, function() {
                        $('#capacity').focus();
                    });
                    return false;
                }

                // Optional: Warn about large class sizes
                if (capacity > 30) {
                    swal({
                        title: "Large Class Size!",
                        text: "You're creating a class with " + capacity + " students.\n\nLarge classes may be difficult to manage. Consider splitting into multiple smaller classes for better learning outcomes.\n\nDo you want to continue?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f0ad4e",
                        confirmButtonText: "Yes, create class",
                        cancelButtonText: "No, let me change",
                        closeOnConfirm: false
                    }, function(isConfirm) {
                        if (isConfirm) {
                            // Disable submit button and submit form
                            $('#submitBtn').prop('disabled', true).html('<i class="ti-reload fa-spin"></i> Creating Class...');
                            form.submit();
                        } else {
                            $('#capacity').focus();
                        }
                    });
                    return false;
                }

                // If all validations pass, submit form
                $('#submitBtn').prop('disabled', true).html('<i class="ti-reload fa-spin"></i> Creating Class...');
                form.submit();
                
                return true;
            });
        });
    </script>
@endpush
@extends('layouts.app')

@section('title', 'Edit Student')

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

        .profile-photo-preview {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            display: none;
        }

        .current-photo {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }

        .form-group label {
            font-weight: 500;
        }

        /* Force error messages to display */
        .invalid-feedback {
            display: block !important;
            color: #dc3545 !important;
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #fff5f5 !important;
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
                                <h1>Edit Student: {{ $student->full_name }}</h1>
                            </div>
                        </div>
                        <span>Update student information</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.students.index') }}">Students</a></li>
                                    <li><a href="{{ route('superadmin.students.show', $student) }}">{{ $student->full_name }}</a></li>
                                    <li class="active">Edit</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Messages Summary -->
                @if($errors->any())
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-danger alert-dismissible fade in">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <h5><i class="ti-alert"></i> Validation Errors</h5>
                            <ul style="margin-bottom: 0;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <div id="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <form method="POST" action="{{ route('superadmin.students.update', $student) }}" enctype="multipart/form-data" id="studentEditForm">
                                        @csrf
                                        @method('PUT')

                                        <!-- Basic Information Section -->
                                        <div class="form-section">
                                            <h4 class="mb-3"><i class="ti-user"></i> Basic Information</h4>
                                            <div class="row">
                                                <!-- First Name -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="first_name" class="required-field">First Name</label>
                                                        <input 
                                                            type="text" 
                                                            name="first_name" 
                                                            id="first_name" 
                                                            value="{{ old('first_name', $student->first_name) }}" 
                                                            placeholder="e.g. John"
                                                            required
                                                            maxlength="255"
                                                            class="form-control @error('first_name') is-invalid @enderror"
                                                        >
                                                        @error('first_name')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Last Name -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="last_name" class="required-field">Last Name</label>
                                                        <input 
                                                            type="text" 
                                                            name="last_name" 
                                                            id="last_name" 
                                                            value="{{ old('last_name', $student->last_name) }}" 
                                                            placeholder="e.g. Smith"
                                                            required
                                                            maxlength="255"
                                                            class="form-control @error('last_name') is-invalid @enderror"
                                                        >
                                                        @error('last_name')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Date of Birth -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="date_of_birth" class="required-field">Date of Birth</label>
                                                        <input 
                                                            type="date" 
                                                            name="date_of_birth" 
                                                            id="date_of_birth" 
                                                            value="{{ old('date_of_birth', $student->date_of_birth->format('Y-m-d')) }}" 
                                                            required
                                                            class="form-control @error('date_of_birth') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-calendar"></i> Current age: {{ $student->date_of_birth->age }} years
                                                        </small>
                                                        @error('date_of_birth')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Parent -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="parent_id" class="required-field">Parent/Guardian</label>
                                                        <select 
                                                            name="parent_id" 
                                                            id="parent_id" 
                                                            required
                                                            class="form-control @error('parent_id') is-invalid @enderror"
                                                        >
                                                            <option value="">Select Parent</option>
                                                            @foreach($parents as $parent)
                                                            <option value="{{ $parent->id }}" {{ old('parent_id', $student->parent_id) == $parent->id ? 'selected' : '' }}>
                                                                {{ $parent->name }} ({{ $parent->email }})
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                        <small class="form-text text-muted">
                                                            <i class="ti-user"></i> Current parent: {{ $student->parent->name }}
                                                        </small>
                                                        @error('parent_id')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Enrolment Information Section -->
                                        <div class="form-section">
                                            <h4 class="mb-3"><i class="ti-book"></i> Enrolment & Schedule Information</h4>
                                            <div class="row">
                                                <!-- Enrolment Date -->
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="enrollment_date" class="required-field">Enrolment Date</label>
                                                        <input 
                                                            type="date" 
                                                            name="enrollment_date" 
                                                            id="enrollment_date" 
                                                            value="{{ old('enrollment_date', $student->enrollment_date->format('Y-m-d')) }}" 
                                                            required
                                                            class="form-control @error('enrollment_date') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-calendar"></i> Date when student enroled
                                                        </small>
                                                        @error('enrollment_date')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="weekly_hours" class="required-field">Weekly Hours</label>
                                                        <select name="weekly_hours" id="weekly_hours" required class="form-control">
                                                            <option value="">Select Hours</option>
                                                            @for ($hours = 0.5; $hours <= 15; $hours += 0.5)
                                                                <option value="{{ number_format($hours, 1) }}" 
                                                                    {{ old('weekly_hours', $student->weekly_hours ?? '') == number_format($hours, 1) ? 'selected' : '' }}>
                                                                    {{ number_format($hours, 1) }} hours
                                                                    @if ($hours == 0.5) (30 minutes) @endif
                                                                    @if ($hours == 1.0) (1 hour) @endif
                                                                </option>
                                                            @endfor
                                                        </select>
                                                        <small class="form-text text-muted">
                                                            <i class="ti-time"></i> Total teaching hours per week for this student
                                                        </small>
                                                    </div>
                                                </div>

                                                <!-- Status -->
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="status" class="required-field">Status</label>
                                                        <select 
                                                            name="status" 
                                                            id="status" 
                                                            required
                                                            class="form-control @error('status') is-invalid @enderror"
                                                        >
                                                            <option value="active" {{ old('status', $student->status) === 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ old('status', $student->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                            <option value="graduated" {{ old('status', $student->status) === 'graduated' ? 'selected' : '' }}>Graduated</option>
                                                            <option value="withdrawn" {{ old('status', $student->status) === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                                                        </select>
                                                        <small class="form-text text-muted">
                                                            <i class="ti-flag"></i> Current enrolment status
                                                        </small>
                                                        @error('status')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Emergency Contact Section -->
                                        <div class="form-section">
                                            <h4 class="mb-3"><i class="ti-alarm-clock"></i> Emergency Contact</h4>
                                            <div class="row">
                                                <!-- Emergency Contact Name -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="emergency_contact">Emergency Contact Name</label>
                                                        <input 
                                                            type="text" 
                                                            name="emergency_contact" 
                                                            id="emergency_contact" 
                                                            value="{{ old('emergency_contact', $student->emergency_contact) }}"
                                                            placeholder="e.g. Jane Doe"
                                                            maxlength="255"
                                                            class="form-control @error('emergency_contact') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-user"></i> Name of emergency contact person
                                                        </small>
                                                        @error('emergency_contact')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Emergency Phone -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="emergency_phone">Emergency Phone</label>
                                                        <input 
                                                            type="text" 
                                                            name="emergency_phone" 
                                                            id="emergency_phone" 
                                                            value="{{ old('emergency_phone', $student->emergency_phone) }}"
                                                            minlength="10"
                                                            maxlength="20"
                                                            pattern="(\+44\s?|0)[0-9\s\-\(\)]{9,}"
                                                            class="form-control @error('emergency_phone') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-mobile"></i> UK phone number format: +44 20 1234 5678 or 020 1234 5678
                                                        </small>
                                                        @error('emergency_phone')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Medical Information -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="medical_info"><i class="ti-heart"></i> Medical Information</label>
                                                    <textarea 
                                                        name="medical_info" 
                                                        id="medical_info" 
                                                        rows="4"
                                                        placeholder="Any allergies, medical conditions, or special needs..."
                                                        class="form-control @error('medical_info') is-invalid @enderror"
                                                    >{{ old('medical_info', $student->medical_info) }}</textarea>
                                                    <small class="form-text text-muted">
                                                        <i class="ti-info-alt"></i> Include allergies, medications, conditions, or special requirements
                                                    </small>
                                                    @error('medical_info')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Current Profile Photo -->
                                        @if($student->profile_photo)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Current Profile Photo</label>
                                                    <div>
                                                        <img src="{{ asset('storage/' . $student->profile_photo) }}" alt="{{ $student->full_name }}" class="current-photo img-thumbnail">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Profile Photo -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="profile_photo">
                                                        <i class="ti-image"></i> {{ $student->profile_photo ? 'Change Profile Photo' : 'Profile Photo' }}
                                                    </label>
                                                    <input 
                                                        type="file" 
                                                        name="profile_photo" 
                                                        id="profile_photo" 
                                                        accept="image/jpeg,image/png,image/jpg,image/gif"
                                                        class="form-control-file @error('profile_photo') is-invalid @enderror"
                                                    >
                                                    <small class="form-text text-muted">
                                                        <i class="ti-info-alt"></i> Max size: 2MB. Formats: JPG, PNG, GIF
                                                    </small>
                                                    @error('profile_photo')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                    
                                                    <!-- Image Preview -->
                                                    <img id="photo-preview" class="profile-photo-preview img-thumbnail" alt="Photo preview">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Info Box -->
                                        <div class="info-box">
                                            <div class="d-flex">
                                                <i class="ti-info-alt mr-3"></i>
                                                <div>
                                                    <strong>Student Information</strong>
                                                    <ul class="mb-0 mt-2">
                                                        <li>Student ID: {{ $student->id }}</li>
                                                        <li>Enroled: {{ $student->enrollment_date->format('d M Y') }} ({{ $student->enrollment_date->diffForHumans() }})</li>
                                                        <li>Classes Enroled: {{ $student->enrollments->count() }}</li>
                                                        <li>Last Updated: {{ $student->updated_at->format('d M Y, H:i') }}</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('superadmin.students.show', $student) }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="ti-check"></i> Update Student
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
                                <p>MLC Classroom - Edit Student</p>
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
            const originalStatus = '{{ $student->status }}';
            
            // ========================================
            // IMAGE PREVIEW AND VALIDATION
            // ========================================
            $('#profile_photo').on('change', function() {
                const file = this.files[0];
                if (file) {
                    // Check file size (2MB = 2097152 bytes)
                    if (file.size > 2097152) {
                        swal({
                            title: "File Too Large!",
                            text: "File size must not exceed 2MB. Please choose a smaller image.",
                            type: "error",
                            confirmButtonText: "OK"
                        });
                        $(this).val('');
                        $('#photo-preview').hide();
                        return;
                    }
                    
                    // Check file type
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        swal({
                            title: "Invalid File Type!",
                            text: "Only JPG, PNG, and GIF images are allowed.",
                            type: "error",
                            confirmButtonText: "OK"
                        });
                        $(this).val('');
                        $('#photo-preview').hide();
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#photo-preview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#photo-preview').hide();
                }
            });

            // ========================================
            // DATE VALIDATION
            // ========================================
            // Set max date for date of birth (must be in the past)
            const today = new Date().toISOString().split('T')[0];
            $('#date_of_birth').attr('max', today);
            
            // Validate date of birth on change
            $('#date_of_birth').on('change', function() {
                const selectedDate = new Date($(this).val());
                const todayDate = new Date(today);
                
                if (selectedDate >= todayDate) {
                    swal({
                        title: "Invalid Date!",
                        text: "Date of birth must be in the past.",
                        type: "error",
                        confirmButtonText: "OK"
                    }, function() {
                        $('#date_of_birth').val('{{ $student->date_of_birth->format('Y-m-d') }}').addClass('is-invalid');
                    });
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // ========================================
            // PHONE NUMBER VALIDATION
            // ========================================
            $('#emergency_phone').on('input', function() {
                let value = $(this).val();
                // Remove any characters that aren't numbers, +, -, (, ), or spaces
                value = value.replace(/[^0-9+\-\(\)\s]/g, '');
                $(this).val(value);
            });

            // ========================================
            // NAME VALIDATION (LETTERS ONLY)
            // ========================================
            $('#first_name, #last_name').on('input', function() {
                let value = $(this).val();
                // Allow only letters and spaces
                value = value.replace(/[^a-zA-Z\s]/g, '');
                $(this).val(value);
            });

            // ========================================
            // PARENT SELECTION VALIDATION
            // ========================================
            $('#parent_id').on('change', function() {
                if ($(this).val() === '') {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // ========================================
            // STATUS CHANGE WARNING
            // ========================================
            $('#status').on('change', function() {
                const newStatus = $(this).val();
                const statusElement = $(this);
                const classCount = {{ $student->classes->count() ?? 0 }}; // Pass from controller
                
                if (newStatus !== originalStatus && (newStatus === 'graduated' || newStatus === 'withdrawn')) {
                    let warningText = "Are you sure you want to change the status to '" + newStatus.toUpperCase() + "'?\n\n";
                    
                    if (classCount > 0) {
                        warningText += "This student is currently enroled in " + classCount + " class(es).\n\n";
                    }
                    
                    warningText += "This may affect the student's class enrolments and access to the system.";
                    
                    swal({
                        title: "Change Student Status?",
                        text: warningText,
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f0ad4e",
                        confirmButtonText: "Yes, change status",
                        cancelButtonText: "No, cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    }, function(isConfirm) {
                        if (!isConfirm) {
                            statusElement.val(originalStatus);
                        }
                    });
                }
            });

            // ========================================
            // FORM SUBMISSION VALIDATION
            // ========================================
            $('#studentEditForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default submission
                
                const form = this;
                let isValid = true;
                let errors = [];

                // Validate First Name
                if ($('#first_name').val().trim() === '') {
                    isValid = false;
                    errors.push('• First name is required');
                    $('#first_name').addClass('is-invalid');
                } else {
                    $('#first_name').removeClass('is-invalid');
                }

                // Validate Last Name
                if ($('#last_name').val().trim() === '') {
                    isValid = false;
                    errors.push('• Last name is required');
                    $('#last_name').addClass('is-invalid');
                } else {
                    $('#last_name').removeClass('is-invalid');
                }

                // Validate Date of Birth
                if ($('#date_of_birth').val() === '') {
                    isValid = false;
                    errors.push('• Date of birth is required');
                    $('#date_of_birth').addClass('is-invalid');
                } else {
                    const selectedDate = new Date($('#date_of_birth').val());
                    const todayDate = new Date(today);
                    
                    if (selectedDate >= todayDate) {
                        isValid = false;
                        errors.push('• Date of birth must be in the past');
                        $('#date_of_birth').addClass('is-invalid');
                    } else {
                        $('#date_of_birth').removeClass('is-invalid');
                    }
                }

                // Validate Parent Selection
                if ($('#parent_id').val() === '') {
                    isValid = false;
                    errors.push('• Please select a parent/guardian');
                    $('#parent_id').addClass('is-invalid');
                } else {
                    $('#parent_id').removeClass('is-invalid');
                }

                // Validate Enrolment Date
                if ($('#enrollment_date').val() === '') {
                    isValid = false;
                    errors.push('• Enrolment date is required');
                    $('#enrollment_date').addClass('is-invalid');
                } else {
                    $('#enrollment_date').removeClass('is-invalid');
                }

                // If not valid, show SweetAlert with errors
                if (!isValid) {
                    swal({
                        title: "Validation Error!",
                        text: "Please fix the following errors:\n\n" + errors.join('\n'),
                        type: "error",
                        confirmButtonText: "OK",
                        html: true
                    }, function() {
                        // Scroll to first invalid field
                        const firstInvalid = $('.is-invalid').first();
                        if (firstInvalid.length) {
                            $('html, body').animate({
                                scrollTop: firstInvalid.offset().top - 100
                            }, 500);
                            firstInvalid.focus();
                        }
                    });
                    
                    return false;
                }

                // Disable submit button to prevent double submission
                $('#submitBtn').prop('disabled', true).html('<i class="ti-reload fa-spin"></i> Updating...');
                form.submit();
                
                return true;
            });
        });
    </script>
@endpush
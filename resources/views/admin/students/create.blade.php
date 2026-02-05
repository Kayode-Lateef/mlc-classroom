@extends('layouts.app')

@section('title', 'Add Student')

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
            color: #007bff;
            font-size: 1.2rem;
        }

        .profile-photo-preview {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            display: none;
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
                                <h1>Add New Student</h1>
                            </div>
                        </div>
                        <span>Enter student details to create a new record</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.students.index') }}">Students</a></li>
                                    <li class="active">Add Student</li>
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
                                    <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data" id="studentForm">
                                        @csrf

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
                                                            value="{{ old('first_name') }}" 
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
                                                            value="{{ old('last_name') }}" 
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
                                                            value="{{ old('date_of_birth') }}" 
                                                            required
                                                            class="form-control @error('date_of_birth') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-calendar"></i> Student must be between 6-18 years old
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
                                                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                                                {{ $parent->name }} ({{ $parent->email }})
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                        <small class="form-text text-muted">
                                                            <i class="ti-user"></i> Select the parent or guardian
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
                                            <h4 class="mb-3"><i class="ti-book"></i> Enrolment Information</h4>
                                            <div class="row">
                                                <!-- Enrolment Date -->
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="enrollment_date" class="required-field">Enrolment Date</label>
                                                        <input 
                                                            type="date" 
                                                            name="enrollment_date" 
                                                            id="enrollment_date" 
                                                            value="{{ old('enrollment_date', date('Y-m-d')) }}" 
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

                                                <!-- Weekly Hours - NEW FIELD -->
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="weekly_hours" class="required-field">Weekly Hours</label>
                                                        <select 
                                                            name="weekly_hours" 
                                                            id="weekly_hours" 
                                                            required
                                                            class="form-control @error('weekly_hours') is-invalid @enderror"
                                                        >
                                                            <option value="">Select Hours</option>
                                                            @php
                                                                $hours = [];
                                                                for ($i = 0.5; $i <= 15; $i += 0.5) {
                                                                    $hours[] = number_format($i, 1);
                                                                }
                                                            @endphp
                                                            @foreach($hours as $hour)
                                                                <option value="{{ $hour }}" {{ old('weekly_hours', '2.0') == $hour ? 'selected' : '' }}>
                                                                    {{ $hour }} {{ $hour == '1.0' ? 'hour' : 'hours' }}/week
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <small class="form-text text-muted">
                                                            <i class="ti-time"></i> Total teaching hours per week (30-min increments)
                                                        </small>
                                                        @error('weekly_hours')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
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
                                                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                            <option value="graduated" {{ old('status') === 'graduated' ? 'selected' : '' }}>Graduated</option>
                                                            <option value="withdrawn" {{ old('status') === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
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
                                                            value="{{ old('emergency_contact') }}"
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
                                                            value="{{ old('emergency_phone') }}"
                                                            placeholder="+44 20 1234 5678 or 020 1234 5678"
                                                            maxlength="20"
                                                            pattern="[+]?[0-9\s\-\(\)]+"
                                                            class="form-control @error('emergency_phone') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-mobile"></i>UK phone number format: +44 20 1234 5678 or 020 1234 5678
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
                                                    >{{ old('medical_info') }}</textarea>
                                                    <small class="form-text text-muted">
                                                        <i class="ti-info-alt"></i> Include allergies, medications, conditions, or special requirements
                                                    </small>
                                                    @error('medical_info')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Profile Photo -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="profile_photo"><i class="ti-image"></i> Profile Photo</label>
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
                                                    <strong>Important Information</strong>
                                                    <p class="mb-0">Please ensure all required fields are filled correctly. The parent will be notified of the new student enrolment via email.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="ti-check"></i> Add Student
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
                                <p>MLC Classroom - Add Student</p>
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
            // ========================================
            // IMAGE PREVIEW AND VALIDATION
            // ========================================
            $('#profile_photo').on('change', function() {
                const file = this.files[0];
                if (file) {
                    // Check file size (2MB = 2097152 bytes)
                    if (file.size > 2097152) {
                        alert('File size must not exceed 2MB');
                        $(this).val('');
                        $('#photo-preview').hide();
                        return;
                    }
                    
                    // Check file type
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Only JPG, PNG, and GIF images are allowed');
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
                    alert('Date of birth must be in the past');
                    $(this).val('');
                    $(this).addClass('is-invalid');
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
            // FORM SUBMISSION VALIDATION
            // ========================================
            $('#studentForm').on('submit', function(e) {
                let isValid = true;
                let errors = [];

                // Validate First Name
                if ($('#first_name').val().trim() === '') {
                    isValid = false;
                    errors.push('First name is required');
                    $('#first_name').addClass('is-invalid');
                } else {
                    $('#first_name').removeClass('is-invalid');
                }

                // Validate Last Name
                if ($('#last_name').val().trim() === '') {
                    isValid = false;
                    errors.push('Last name is required');
                    $('#last_name').addClass('is-invalid');
                } else {
                    $('#last_name').removeClass('is-invalid');
                }

                // Validate Date of Birth
                if ($('#date_of_birth').val() === '') {
                    isValid = false;
                    errors.push('Date of birth is required');
                    $('#date_of_birth').addClass('is-invalid');
                } else {
                    const selectedDate = new Date($('#date_of_birth').val());
                    const todayDate = new Date(today);
                    
                    if (selectedDate >= todayDate) {
                        isValid = false;
                        errors.push('Date of birth must be in the past');
                        $('#date_of_birth').addClass('is-invalid');
                    } else {
                        $('#date_of_birth').removeClass('is-invalid');
                    }
                }

                // Validate Parent Selection
                if ($('#parent_id').val() === '') {
                    isValid = false;
                    errors.push('Please select a parent/guardian');
                    $('#parent_id').addClass('is-invalid');
                } else {
                    $('#parent_id').removeClass('is-invalid');
                }

                // Validate Enrolment Date
                if ($('#enrollment_date').val() === '') {
                    isValid = false;
                    errors.push('Enrolment date is required');
                    $('#enrollment_date').addClass('is-invalid');
                } else {
                    $('#enrollment_date').removeClass('is-invalid');
                }

                // If not valid, prevent submission and show errors
                if (!isValid) {
                    e.preventDefault();
                    
                    // Scroll to top to show errors
                    $('html, body').animate({
                        scrollTop: 0
                    }, 500);
                    
                    // Show alert with errors
                    alert('Please fix the following errors:\n\n' + errors.join('\n'));
                    
                    return false;
                }

                // Disable submit button to prevent double submission
                $('#submitBtn').prop('disabled', true).html('<i class="ti-reload"></i> Submitting...');
                
                return true;
            });
        });
    </script>
@endpush
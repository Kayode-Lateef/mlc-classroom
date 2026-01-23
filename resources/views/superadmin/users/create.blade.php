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

        .invalid-feedback {
            display: block !important;
            color: #dc3545 !important;
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #fff5f5 !important;
        }

        /* Weekly hours highlight */
        #weekly_hours {
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
                                <h1>Add New User</h1>
                            </div>
                        </div>
                        <span>Enter user details to create a new record</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('superadmin.users.index') }}">Users</a></li>
                                    <li class="breadcrumb-item active">Create User</li>
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
                                    <form method="POST" action="{{ route('superadmin.users.store') }}" enctype="multipart/form-data" id="createUserForm">
                                        @csrf

                                        <!-- Basic Information Section -->
                                        <div class="form-section">
                                            <h4 class="mb-3"><i class="ti-id-badge"></i> Basic Information</h4>
                                            <div class="row">
                                                <!-- Full Name  -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="name" class="required-field">Full Name</label>
                                                        <input 
                                                            type="text" 
                                                            name="name" 
                                                            id="name" 
                                                            value="{{ old('name') }}" 
                                                            placeholder="Enter full name"
                                                            required
                                                            class="form-control @error('name') is-invalid @enderror"
                                                        >
                                                        @error('name')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Email Address -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="email" class="required-field">Email Address</label>
                                                        <input 
                                                            type="text" 
                                                            name="email" 
                                                            id="email" 
                                                            value="{{ old('email') }}" 
                                                            placeholder="user@example.com"
                                                            required
                                                            class="form-control @error('email') is-invalid @enderror"
                                                        >
                                                        @error('email')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                           <!-- Contact Information Section -->
                                        <div class="form-section">
                                            <h4 class="mb-3"><i class="ti-mobile"></i> Contact Information</h4>
                                            <div class="row">

                                                <!-- Phone -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="phone" class="required-field">Phone Number</label>
                                                        <input 
                                                            type="text" 
                                                            name="phone" 
                                                            id="phone" 
                                                            value="{{ old('phone') }}"
                                                            placeholder="+44 20 1234 5678 or 020 1234 5678"
                                                            required
                                                            minlength="10"
                                                            maxlength="20"
                                                            pattern="(\+44\s?|0)[0-9\s\-\(\)]{9,}"
                                                            class="form-control @error('phone') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-mobile"></i> UK phone number format: +44 20 1234 5678 or 020 1234 5678
                                                        </small>
                                                        @error('phone')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Profile Photo -->
                                                <div class="col-md-6">
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
                                        </div>

                                        <!-- Role & Access Section -->
                                        <div class="form-section">
                                            <h4 class="mb-3"><i class="ti-shield"></i> Role & Access</h4>
                                            <div class="row">
                                                <!-- Role -->
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="role" class="required-field">User Role</label>
                                                            <select 
                                                                name="role" 
                                                                id="role" 
                                                                required
                                                                class="form-control @error('role') is-invalid @enderror"
                                                            >
                                                                <option value="">Select Role</option>
                                                                <option value="superadmin" {{ old('role', 'superadmin') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                                                <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                                                <option value="parent" {{ old('role') === 'parent' ? 'selected' : '' }}>Parent</option>
                                                            </select>
                                                            @error('role')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                <!-- Status -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="status" class="required-field">Account Status</label>
                                                        <select 
                                                            name="status" 
                                                            id="status" 
                                                            required
                                                            class="form-control @error('status') is-invalid @enderror"
                                                        >
                                                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                            <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                            <option value="banned" {{ old('status') == 'banned' ? 'selected' : '' }}>Banned</option>
                                                      </select>
                                                        @error('status')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                         
                                            </div>
                                        </div>

                                        
                                        <!-- Account Credentials Section-->
                                        <div class="form-section">
                                            <h4 class="mb-3"><i class="ti-lock"></i> Account Credentials</h4>
                                            <div class="row">
                                                <!-- Password  -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="password" class="required-field">Password</label>
                                                        <input 
                                                            type="password" 
                                                            name="password" 
                                                            id="password" 
                                                            value="{{ old('password') }}" 
                                                            placeholder="Minimum 8 characters"
                                                            required
                                                            class="form-control @error('password') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-info-alt"></i> Minimum 8 characters required
                                                        </small>
                                                        @error('password')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                 <!-- Confirm Password  -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="password_confirmation" class="required-field">Confirm Password</label>
                                                        <input 
                                                            type="password" 
                                                            name="password_confirmation" 
                                                            id="password_confirmation" 
                                                            value="{{ old('password_confirmation') }}" 
                                                            placeholder="Re-enter password"
                                                            required
                                                            class="form-control @error('password_confirmation') is-invalid @enderror"
                                                        >
                                                        @error('password_confirmation')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Email Verification Option -->
                                                <div class="col-md-12">
                                                    <div class="form-group form-check">
                                                         <input type="hidden" name="requires_verification" value="0">
                                                        <input 
                                                            type="checkbox" 
                                                            name="requires_verification" 
                                                            id="requires_verification" 
                                                            class="form-check-input" 
                                                            value="1"
                                                            {{ old('requires_verification') ? 'checked' : '' }}
                                                        >
                                                        <label for="requires_verification" class="form-check-label">
                                                            <i class="ti-email"></i> Require Email Verification
                                                        </label>
                                                  
                                                    </div>
                                                    <small class="form-text text-muted" style="">
                                                        <strong>Unchecked:</strong> User can login immediately with provided credentials.<br>
                                                        <strong>Checked:</strong> User must verify email before accessing the system.
                                                    </small>
                                                    @error('requires_verification')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                            </div>

                                        </div>


                                     

                                
                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="ti-check"></i> Create User
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
            // PHONE NUMBER VALIDATION
            // ========================================
            $('#phone').on('input', function() {
                let value = $(this).val();
                // Remove any characters that aren't numbers, +, -, (, ), or spaces
                value = value.replace(/[^0-9+\-\(\)\s]/g, '');
                $(this).val(value);
            });

            // ========================================
            // NAME VALIDATION (LETTERS ONLY)
            // ========================================
            $('#name').on('input', function() {
                let value = $(this).val();
                // Allow only letters and spaces
                value = value.replace(/[^a-zA-Z\s]/g, '');
                $(this).val(value);
            });


            // ========================================
            // FORM SUBMISSION VALIDATION
            // ========================================
            $('createUserForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default submission
                
                const form = this;
                let isValid = true;
                let errors = [];

                // Validate Full Name
                if ($('#name').val().trim() === '') {
                    isValid = false;
                    errors.push('• Name is required');
                    $('#name').addClass('is-invalid');
                } else {
                    $('#name').removeClass('is-invalid');
                }

                // Validate Email
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if ($('#email').val().trim() === '') {
                    isValid = false;
                    errors.push('• Email address is required');
                    $('#email').addClass('is-invalid');
                } else if (!emailPattern.test($('#email').val().trim())) {
                    isValid = false;
                    errors.push('• Please enter a valid email address');
                    $('#email').addClass('is-invalid');
                } else {
                    $('#email').removeClass('is-invalid');
                }


                // Validate Role Selection
                if ($('#role').val() === '') {
                    isValid = false;
                    errors.push('• Please select a role');
                    $('#role').addClass('is-invalid');
                } else {
                    $('#role').removeClass('is-invalid');
                }

                // Validate Password
                if ($('#password').val().length < 8) {
                    isValid = false;
                    errors.push('• Password must be at least 8 characters');
                    $('#password').addClass('is-invalid');
                } else {
                    $('#password').removeClass('is-invalid');
                }

                // Validate Password Confirmation
                if ($('#password').val() !== $('#password_confirmation').val()) {
                    isValid = false;
                    errors.push('• Password confirmation does not match');
                    $('#password_confirmation').addClass('is-invalid');
                } else {
                    $('#password_confirmation').removeClass('is-invalid');
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

                // Show loading state with SweetAlert
                swal({
                    title: "Creating User...",
                    text: "Please wait while we save the user information.",
                    type: "info",
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                
                // Disable submit button
                $('#submitBtn').prop('disabled', true).html('<i class="ti-reload fa-spin"></i> Submitting...');
                
                // Submit form
                form.submit();
                
                return true;
            });
        });
    </script>
@endpush
@extends('layouts.app')

@section('title', 'Edit User')

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

        .current-photo-container {
            position: relative;
            display: inline-block;
        }

        .current-photo {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }

        /* Password toggle styling */
        .toggle-password:hover {
            color: #495057 !important;
        }

        .toggle-password:focus {
            outline: none;
        }

        /* Phone validation styling */
        .phone-valid {
            border-color: #28a745 !important;
        }

        .phone-invalid {
            border-color: #dc3545 !important;
        }

        .phone-feedback {
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .phone-feedback.valid {
            color: #28a745;
        }

        .phone-feedback.invalid {
            color: #dc3545;
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
                                <h1>Edit User</h1>
                            </div>
                        </div>
                        <span>Update user account information</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                                    <li class="breadcrumb-item active">Edit User</li>
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
                                <div class="card-header mb-3">
                                    <h4 class="card-title">
                                        <i class="ti-user"></i> Edit User: {{ $user->name }}
                                        <span class="badge badge-light" style="margin-left: 10px;">{{ ucfirst($user->role) }}</span>
                                    </h4>
                                </div>

                                <div class="card-body">
                                    <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" id="editUserForm">
                                        @csrf
                                        @method('PUT')

                                        <!-- Current Profile Photo -->
                                        @if($user->profile_photo)
                                        <div class="col-md-12 mb-3">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Current Profile Photo</label>
                                                <div>
                                                    <img src="{{ Storage::url($user->profile_photo) }}" 
                                                         alt="{{ $user->name }}" 
                                                         class="img-thumbnail"
                                                         style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                                </div>
                                            </div>
                                        </div>
                                        @endif

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
                                                            value="{{ old('name', $user->name) }}"
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
                                                            value="{{ old('email', $user->email) }}"
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
                                                        <label for="phone">Phone Number</label>
                                                        <input 
                                                            type="text" 
                                                            name="phone" 
                                                            id="phone" 
                                                            value="{{ old('phone', $user->phone) }}"
                                                            placeholder="+44 20 1234 5678 or 020 1234 5678"
                                                            minlength="10"
                                                            maxlength="20"
                                                            pattern="(\+44\s?|0)[0-9\s\-\(\)]{9,}"
                                                            class="form-control @error('phone') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-mobile"></i> UK phone number format: +44 20 1234 5678 or 020 1234 5678
                                                        </small>
                                                        <div id="phone-feedback" class="phone-feedback" style="display: none;"></div>
                                                        @error('phone')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Change Profile Photo -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="profile_photo"><i class="ti-image"></i> 
                                                            {{ $user->profile_photo ? 'Change Profile Photo' : 'Profile Photo' }}
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
                                                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                                            <option value="teacher" {{ old('role', $user->role) === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                                            <option value="parent" {{ old('role', $user->role) === 'parent' ? 'selected' : '' }}>Parent</option>
                                                        </select>
                                                        <small class="form-text text-muted">
                                                            <i class="ti-info-alt"></i> Cannot assign SuperAdmin role
                                                        </small>
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
                                                            <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                            <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                            <option value="banned" {{ old('status', $user->status) == 'banned' ? 'selected' : '' }}>Banned</option>
                                                        </select>
                                                        @error('status')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Account Credentials Section -->
                                        <div class="form-section">
                                            <h4 class="mb-3"><i class="ti-lock"></i> Change Password (Optional)</h4>
                                            <p class="text-muted">Leave blank if you don't want to change the password</p>

                                            <div class="row">
                                                <!-- New Password  -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="password">New Password</label>
                                                        <div style="position: relative;">
                                                            <input 
                                                                type="password" 
                                                                name="password" 
                                                                id="password" 
                                                                placeholder="Minimum 8 characters"
                                                                minlength="8"
                                                                class="form-control @error('password') is-invalid @enderror"
                                                                style="padding-right: 40px;"
                                                            >
                                                            <button type="button" class="toggle-password" data-target="password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6c757d; cursor: pointer; padding: 5px 10px;">
                                                                <i class="fa fa-eye"></i>
                                                            </button>
                                                        </div>
                                                        <small class="form-text text-muted">
                                                            <i class="ti-info-alt"></i> Minimum 8 characters required
                                                        </small>
                                                        @error('password')
                                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Confirm Password  -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="password_confirmation">Confirm New Password</label>
                                                        <div style="position: relative;">
                                                            <input 
                                                                type="password" 
                                                                name="password_confirmation" 
                                                                id="password_confirmation" 
                                                                placeholder="Re-enter password"
                                                                minlength="8"
                                                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                                                style="padding-right: 40px;"
                                                            >
                                                            <button type="button" class="toggle-password" data-target="password_confirmation" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6c757d; cursor: pointer; padding: 5px 10px;">
                                                                <i class="fa fa-eye"></i>
                                                            </button>
                                                        </div>
                                                        <small class="form-text text-muted">
                                                            <i class="ti-info-alt"></i> Re-enter password to confirm
                                                        </small>
                                                        @error('password_confirmation')
                                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="ti-check"></i> Update User
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
                                <p>MLC Classroom - Edit User</p>
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
    // PHONE NUMBER VALIDATION
    // ========================================
    const phoneRegex = /^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/;
    
    $('#phone').on('input', function() {
        let phoneValue = $(this).val().trim();
        const feedback = $('#phone-feedback');
        
        if (phoneValue === '') {
            // Empty is allowed (nullable field)
            $(this).removeClass('phone-invalid phone-valid');
            feedback.hide();
            return;
        }
        
        // Remove any letters or special characters except +, -, (, ), space, and dot
        phoneValue = phoneValue.replace(/[^0-9\+\-\(\)\s\.]/g, '');
        $(this).val(phoneValue);
        
        // Validate format
        if (phoneRegex.test(phoneValue)) {
            $(this).removeClass('phone-invalid is-invalid').addClass('phone-valid');
            feedback.removeClass('invalid').addClass('valid')
                .html('<i class="ti-check"></i> Valid phone number format')
                .show();
        } else {
            $(this).removeClass('phone-valid').addClass('phone-invalid is-invalid');
            feedback.removeClass('valid').addClass('invalid')
                .html('<i class="ti-close"></i> Invalid format. Use: +44 1234 567890 or 07123456789')
                .show();
        }
    });

    // ========================================
    // PASSWORD TOGGLE FUNCTIONALITY
    // ========================================
    $(document).on('click', '.toggle-password', function() {
        const targetId = $(this).data('target');
        const passwordInput = $('#' + targetId);
        const icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

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
    // NAME VALIDATION (LETTERS ONLY)
    // ========================================
    $('#name').on('input', function() {
        let value = $(this).val();
        // Allow only letters and spaces
        value = value.replace(/[^a-zA-Z\s]/g, '');
        $(this).val(value);
    });

    // ========================================
    // PASSWORD CONFIRMATION VALIDATION
    // ========================================
    $('#password_confirmation').on('keyup', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        
        // Only validate if password field has value
        if (password && confirmPassword && password !== confirmPassword) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<span class="invalid-feedback d-block">Passwords do not match</span>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });
    
    // Also check when password field changes
    $('#password').on('keyup', function() {
        const password = $(this).val();
        const confirmPassword = $('#password_confirmation').val();
        
        if (password && confirmPassword && password !== confirmPassword) {
            $('#password_confirmation').addClass('is-invalid');
            if (!$('#password_confirmation').next('.invalid-feedback').length) {
                $('#password_confirmation').after('<span class="invalid-feedback d-block">Passwords do not match</span>');
            }
        } else {
            $('#password_confirmation').removeClass('is-invalid');
            $('#password_confirmation').siblings('.invalid-feedback').remove();
        }
    });
    
    // ========================================
    // FORM SUBMISSION VALIDATION
    // ========================================
    $('#editUserForm').on('submit', function(e) {
        const password = $('#password').val();
        const confirmPassword = $('#password_confirmation').val();
        const phone = $('#phone').val().trim();
        let isValid = true;
        let errors = [];
        
        // Validate name
        if ($('#name').val().trim() === '') {
            isValid = false;
            errors.push('• Name is required');
            $('#name').addClass('is-invalid');
        } else {
            $('#name').removeClass('is-invalid');
        }

        // Validate email
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

        // Validate role
        if ($('#role').val() === '') {
            isValid = false;
            errors.push('• Please select a role');
            $('#role').addClass('is-invalid');
        } else {
            $('#role').removeClass('is-invalid');
        }
        
        // Validate password match if changing password
        if (password && password !== confirmPassword) {
            isValid = false;
            errors.push('• Passwords do not match');
            $('#password_confirmation').addClass('is-invalid');
        } else {
            $('#password_confirmation').removeClass('is-invalid');
        }
        
        // Validate phone format if provided
        if (phone && !phoneRegex.test(phone)) {
            isValid = false;
            errors.push('• Invalid phone number format');
            $('#phone').addClass('is-invalid');
        } else {
            $('#phone').removeClass('is-invalid');
        }

        // If not valid, show SweetAlert with errors
        if (!isValid) {
            e.preventDefault();
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
            title: "Updating User...",
            text: "Please wait while we save the changes.",
            type: "info",
            showConfirmButton: false,
            allowOutsideClick: false
        });
        
        // Disable submit button to prevent double submission
        $('#submitBtn').prop('disabled', true).html('<i class="ti-reload fa-spin"></i> Updating...');
        
        return true;
    });
});
</script>
@endpush
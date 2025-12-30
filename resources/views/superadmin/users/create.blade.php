@extends('layouts.app')

@section('title', 'Add User')

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

        /* Password toggle styling */
        .toggle-password:hover {
            color: #495057 !important;
        }

        .toggle-password:focus {
            outline: none;
        }

        /* Force error messages to display */
        .invalid-feedback {
            display: block !important;
            color: #dc3545 !important;
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
                            <div class="page-title"><h1>Add New User</h1></div>
                        </div>
                        <span>Create a new user account</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('superadmin.users.index') }}">Users</a></li>
                                    <li class="active">Add User</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Messages -->
                @if($errors->any())
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
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

                <!-- Create Form -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header mb-3">
                                <h4><i class="ti-user"></i> User Information</h4>
                            </div>
                            <div class="card-body mt">
                                <form method="POST" action="{{ route('superadmin.users.store') }}" enctype="multipart/form-data" id="createUserForm">
                                    @csrf

                                    <div class="form-section">
                                        <!-- Basic Information Section -->                                     
                                        <h4 style="margin-bottom: 20px; margin-top: 20px; padding-bottom: 10px; border-bottom: 2px solid #007bff;">
                                            <i class="ti-id-badge"></i> Basic Information
                                        </h4>
                                        <div class="row">
                                            <!-- Name -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name" style="font-weight: 500;">
                                                        Full Name <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" 
                                                        name="name" 
                                                        id="name" 
                                                        value="{{ old('name') }}"
                                                        class="form-control @error('name') is-invalid @enderror"
                                                        required>
                                                    @error('name')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Email -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="email" style="font-weight: 500;">
                                                        Email Address <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="email" 
                                                        name="email" 
                                                        id="email" 
                                                        value="{{ old('email') }}"
                                                        class="form-control @error('email') is-invalid @enderror"
                                                        required>
                                                    @error('email')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Phone -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="phone" style="font-weight: 500;">Phone Number</label>
                                                    <input type="text" 
                                                        name="phone" 
                                                        id="phone" 
                                                        value="{{ old('phone') }}"
                                                        class="form-control @error('phone') is-invalid @enderror"
                                                        placeholder="+44 1234 567890"
                                                        maxlength="20">
                                                    <small class="form-text text-muted">
                                                        <i class="ti-info-alt"></i> UK Format: +44 1234 567890 or 07123456789
                                                    </small>
                                                    <div id="phone-feedback" class="phone-feedback" style="display: none;"></div>
                                                    @error('phone')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Role -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="role" style="font-weight: 500;">
                                                        Role <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="role" 
                                                            id="role" 
                                                            class="form-control @error('role') is-invalid @enderror"
                                                            required>
                                                        <option value="">Select Role</option>
                                                        <option value="superadmin" {{ old('role') == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                                        <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                                                        <option value="parent" {{ old('role') == 'parent' ? 'selected' : '' }}>Parent</option>
                                                    </select>
                                                    @error('role')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Status -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="status" style="font-weight: 500;">
                                                        Status <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="status" 
                                                            id="status" 
                                                            class="form-control @error('status') is-invalid @enderror"
                                                            required>
                                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                    </select>
                                                    @error('status')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Profile Photo -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="profile_photo" style="font-weight: 500;">Profile Photo</label>
                                                    <input type="file" 
                                                        name="profile_photo" 
                                                        id="profile_photo" 
                                                        accept="image/jpeg,image/png,image/jpg,image/gif"
                                                        class="form-control-file @error('profile_photo') is-invalid @enderror">
                                                    <small class="form-text text-muted">
                                                        <i class="ti-image"></i> Max size: 2MB. Formats: JPG, PNG, GIF
                                                    </small>
                                                    @error('profile_photo')
                                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                    
                                                    <!-- Image Preview -->
                                                    <img id="photo-preview" class="img-thumbnail mt-2" style="display: none; max-width: 200px;" alt="Photo preview">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-section">
                                        <!-- Password Section -->
                                        <h4 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #667eea;">
                                            <i class="ti-lock"></i> Account Security
                                        </h4>
                                        <div class="row">
                                            <!-- Password -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="password" style="font-weight: 500;">
                                                        Password <span class="text-danger">*</span>
                                                    </label>
                                                    <div style="position: relative;">
                                                        <input type="password" 
                                                            name="password" 
                                                            id="password" 
                                                            class="form-control @error('password') is-invalid @enderror"
                                                            minlength="8"
                                                            required
                                                            style="padding-right: 40px;">
                                                        <button type="button" class="toggle-password" data-target="password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6c757d; cursor: pointer; padding: 5px 10px;">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        <i class="ti-info-alt"></i> Minimum 8 characters
                                                    </small>
                                                    @error('password')
                                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Confirm Password -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="password_confirmation" style="font-weight: 500;">
                                                        Confirm Password <span class="text-danger">*</span>
                                                    </label>
                                                    <div style="position: relative;">
                                                        <input type="password" 
                                                            name="password_confirmation" 
                                                            id="password_confirmation" 
                                                            class="form-control"
                                                            minlength="8"
                                                            required
                                                            style="padding-right: 40px;">
                                                        <button type="button" class="toggle-password" data-target="password_confirmation" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6c757d; cursor: pointer; padding: 5px 10px;">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        <i class="ti-info-alt"></i> Re-enter password to confirm
                                                    </small>
                                                </div>
                                            </div>

                                            <!-- Note -->
                                            <div class="col-md-12">
                                                <div class="alert alert-info">
                                                    <i class="ti-info-alt"></i> <strong>Note:</strong> The user will be automatically verified and can log in immediately. They can change their password after first login.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="form-group mt-4 pt-3 border-top text-right">
                                        <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary">
                                            <i class="ti-close"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="ti-check"></i> Add User
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
                            <p>MLC Classroom - Add User</p>
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
    // IMAGE PREVIEW
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
                alert('Invalid file type. Please upload JPG, PNG, or GIF only.');
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
    // PASSWORD CONFIRMATION VALIDATION
    // ========================================
    $('#password_confirmation').on('keyup', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword && password !== confirmPassword) {
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
        
        if (confirmPassword && password !== confirmPassword) {
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
    $('#createUserForm').on('submit', function(e) {
        const password = $('#password').val();
        const confirmPassword = $('#password_confirmation').val();
        const phone = $('#phone').val().trim();
        
        // Validate password match
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match. Please check and try again.');
            $('#password_confirmation').focus();
            return false;
        }
        
        // Validate phone format if provided
        if (phone && !phoneRegex.test(phone)) {
            e.preventDefault();
            alert('Please enter a valid phone number format (e.g., +44 1234 567890 or 07123456789)');
            $('#phone').focus().addClass('is-invalid');
            return false;
        }
        
        // Disable submit button to prevent double submission
        $('#submitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Creating User...');
        
        return true;
    });
});
</script>
@endpush
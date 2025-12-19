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
            background-color: #fff3cd;
            border: 1px solid #ffc107;
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

        .role-display-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .role-locked-notice {
            background-color: #e7f3ff;
            /* border: 1px solid #0066cc; */
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
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
                                <h1>Edit User: {{ $user->name }}</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.users.index') }}">Users</a></li>
                                    <li><a href="{{ route('superadmin.users.show', $user) }}">{{ $user->name }}</a></li>
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
                                    <!-- Current User Warning -->
                                    @if($user->id === auth()->id())
                                    <div class="warning-box">
                                        <div class="d-flex">
                                            <i class="ti-info-alt mr-3"></i>
                                            <div>
                                                <strong>Editing Your Own Account</strong>
                                                <p class="mb-0">You are editing your own account. Use caution when making changes.</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <form method="POST" action="{{ route('superadmin.users.update', $user) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')

                                        <!-- Role Display (Read-only) -->
                                        <div class="role-locked-notice">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <label class="mb-2"><strong>Current Role</strong></label>
                                                    <div>
                                                        @if($user->role === 'superadmin')
                                                            <span class="role-display-badge bg-danger text-white">
                                                                <i class="ti-crown"></i> Super Admin
                                                            </span>
                                                        @elseif($user->role === 'admin')
                                                            <span class="role-display-badge bg-success text-white">
                                                                <i class="ti-id-badge"></i> Admin
                                                            </span>
                                                        @elseif($user->role === 'teacher')
                                                            <span class="role-display-badge bg-info text-white">
                                                                <i class="ti-briefcase"></i> Teacher
                                                            </span>
                                                        @elseif($user->role === 'parent')
                                                            <span class="role-display-badge bg-warning text-white">
                                                                <i class="ti-user"></i> Parent
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <small class="text-muted">
                                                        <i class="ti-lock"></i> Role cannot be changed after creation
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Basic Information -->
                                        <div class="row">
                                            <!-- Full Name -->
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="name" class="required-field">Full Name</label>
                                                    <input 
                                                        type="text" 
                                                        name="name" 
                                                        id="name" 
                                                        value="{{ old('name', $user->name) }}" 
                                                        placeholder="e.g. John Smith"
                                                        required
                                                        class="form-control @error('name') is-invalid @enderror"
                                                    >
                                                    @error('name')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Email -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="email" class="required-field">Email Address</label>
                                                    <input 
                                                        type="email" 
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

                                            <!-- Phone -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="phone">Phone Number</label>
                                                    <input 
                                                        type="text" 
                                                        name="phone" 
                                                        id="phone" 
                                                        value="{{ old('phone', $user->phone) }}" 
                                                        placeholder="+234 800 000 0000"
                                                        class="form-control @error('phone') is-invalid @enderror"
                                                    >
                                                    <small class="form-text text-muted">
                                                        <i class="ti-mobile"></i> Required for SMS notifications
                                                    </small>
                                                    @error('phone')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Current Profile Photo -->
                                            @if($user->profile_photo)
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Current Profile Photo</label>
                                                    <div class="current-photo-container">
                                                        <img 
                                                            src="{{ asset('storage/' . $user->profile_photo) }}" 
                                                            alt="{{ $user->name }}" 
                                                            class="current-photo img-thumbnail"
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            <!-- Profile Photo Upload -->
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="profile_photo">
                                                        {{ $user->profile_photo ? 'Change Profile Photo' : 'Profile Photo' }}
                                                    </label>
                                                    <input 
                                                        type="file" 
                                                        name="profile_photo" 
                                                        id="profile_photo" 
                                                        accept="image/*"
                                                        class="form-control-file @error('profile_photo') is-invalid @enderror"
                                                    >
                                                    <small class="form-text text-muted">
                                                        <i class="ti-image"></i> Max size: 2MB. Formats: JPG, PNG, GIF
                                                    </small>
                                                    @error('profile_photo')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                    
                                                    <!-- Image Preview -->
                                                    <img id="photo-preview" class="profile-photo-preview img-thumbnail" style="display: none;" alt="Photo preview">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Change Password Section -->
                                        <div class="form-section mt-4">
                                            <h4 class="mb-3">
                                                <i class="ti-lock"></i> Change Password (Optional)
                                            </h4>
                                            <p class="text-muted mb-3">Leave blank if you don't want to change the password</p>

                                            <div class="row">
                                                <!-- New Password -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="password">New Password</label>
                                                        <input 
                                                            type="password" 
                                                            name="password" 
                                                            id="password" 
                                                            class="form-control @error('password') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-lock"></i> Minimum 8 characters
                                                        </small>
                                                        @error('password')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Confirm Password -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="password_confirmation">Confirm New Password</label>
                                                        <input 
                                                            type="password" 
                                                            name="password_confirmation" 
                                                            id="password_confirmation" 
                                                            class="form-control"
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Email Verification Status -->
                                        <div class="form-group mt-3">
                                            <div class="checkbox">
                                                <label>
                                                    <input 
                                                        type="checkbox" 
                                                        name="email_verified" 
                                                        value="1"
                                                        {{ old('email_verified', $user->email_verified_at ? true : false) ? 'checked' : '' }}
                                                    >
                                                    Email verified
                                                    @if($user->email_verified_at)
                                                        <span class="badge badge-success ml-2">
                                                            <i class="ti-check"></i> Currently Verified
                                                        </span>
                                                    @else
                                                        <span class="badge badge-warning ml-2">
                                                            <i class="ti-alert"></i> Not Verified
                                                        </span>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>

                                        <!-- User Statistics Info Box -->
                                        <div class="info-box">
                                            <div class="d-flex">
                                                <i class="ti-info-alt mr-3"></i>
                                                <div>
                                                    <strong>User Information</strong>
                                                    <ul class="mb-0 mt-2">
                                                        <li>Account Created: {{ $user->created_at->format('d M Y, H:i') }}</li>
                                                        <li>Last Updated: {{ $user->updated_at->format('d M Y, H:i') }}</li>
                                                        @if($user->role === 'teacher')
                                                            <li>Teaching {{ $user->teachingClasses()->count() }} classes</li>
                                                        @elseif($user->role === 'parent')
                                                            <li>Parent of {{ $user->children()->count() }} student(s)</li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('superadmin.users.show', $user) }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
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
            // Image preview for new photo
            $('#profile_photo').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#photo-preview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#photo-preview').hide();
                }
            });

            // Password confirmation validation
            $('#password_confirmation').on('keyup', function() {
                const password = $('#password').val();
                const confirmPassword = $(this).val();
                
                if (password && confirmPassword && password !== confirmPassword) {
                    $(this).addClass('is-invalid');
                    if (!$(this).next('.invalid-feedback').length) {
                        $(this).after('<span class="invalid-feedback d-block">Passwords do not match</span>');
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                }
            });

            // Show warning if changing own password
            @if($user->id === auth()->id())
            $('#password').on('focus', function() {
                if (!$('#password-warning').length) {
                    $(this).after('<small id="password-warning" class="form-text text-warning"><i class="ti-alert"></i> You are changing your own password. Make sure to remember it!</small>');
                }
            });
            @endif

            // Validate password strength
            $('#password').on('keyup', function() {
                const password = $(this).val();
                if (password.length > 0 && password.length < 8) {
                    $(this).addClass('is-invalid');
                    if (!$(this).siblings('.password-strength-error').length) {
                        $(this).after('<span class="invalid-feedback d-block password-strength-error">Password must be at least 8 characters</span>');
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.password-strength-error').remove();
                }
            });
        });
    </script>
@endpush
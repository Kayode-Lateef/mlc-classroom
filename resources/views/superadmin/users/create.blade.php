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
            display: none;
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
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.users.index') }}">Users</a></li>
                                    <li class="active">Add User</li>
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
                                    <form method="POST" action="{{ route('superadmin.users.store') }}" enctype="multipart/form-data">
                                        @csrf

                                        <!-- Role Selection (Important) -->
                                        <div class="form-section">
                                            <div class="form-group">
                                                <label for="role" class="required-field"><strong>User Role</strong></label>
                                                <select 
                                                    name="role" 
                                                    id="role" 
                                                    required
                                                    class="form-control @error('role') is-invalid @enderror"
                                                >
                                                    <option value="">Select a role</option>
                                                    <option value="superadmin" {{ old('role') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                                    <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                                    <option value="parent" {{ old('role') === 'parent' ? 'selected' : '' }}>Parent</option>
                                                </select>
                                                <small class="form-text text-info">
                                                    <i class="ti-info-alt"></i> Choose the appropriate role for this user
                                                </small>
                                                @error('role')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
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
                                                        value="{{ old('name') }}" 
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

                                            <!-- Phone -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="phone">Phone Number</label>
                                                    <input 
                                                        type="text" 
                                                        name="phone" 
                                                        id="phone" 
                                                        value="{{ old('phone') }}" 
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

                                            <!-- Password -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="password" class="required-field">Password</label>
                                                    <input 
                                                        type="password" 
                                                        name="password" 
                                                        id="password" 
                                                        required
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
                                                    <label for="password_confirmation" class="required-field">Confirm Password</label>
                                                    <input 
                                                        type="password" 
                                                        name="password_confirmation" 
                                                        id="password_confirmation" 
                                                        required
                                                        class="form-control"
                                                    >
                                                </div>
                                            </div>

                                            <!-- Profile Photo -->
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="profile_photo">Profile Photo</label>
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
                                                    <img id="photo-preview" class="profile-photo-preview img-thumbnail" alt="Photo preview">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Email Verification Checkbox -->
                                        <div class="form-group">
                                            <div class="checkbox">
                                                <label>
                                                    <input 
                                                        type="checkbox" 
                                                        name="email_verified" 
                                                        value="1"
                                                        {{ old('email_verified') ? 'checked' : '' }}
                                                    >
                                                    Mark email as verified (user won't need to verify)
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Info Box -->
                                        <div class="info-box">
                                            <div class="d-flex">
                                                <i class="ti-info-alt mr-3"></i>
                                                <div>
                                                    <strong>Password Information</strong>
                                                    <p class="mb-0">The user will receive login credentials via email. They can change their password after first login.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
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
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Image preview
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
                
                if (confirmPassword && password !== confirmPassword) {
                    $(this).addClass('is-invalid');
                    if (!$(this).next('.invalid-feedback').length) {
                        $(this).after('<span class="invalid-feedback d-block">Passwords do not match</span>');
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                }
            });
        });
    </script>
@endpush
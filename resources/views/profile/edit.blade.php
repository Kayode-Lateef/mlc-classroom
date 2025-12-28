@extends('layouts.app')
@section('title', 'My Profile')

@push('styles')
    <style>
        .profile-photo-large {
            width: 150px;
            height: 150px;
            border-radius: 12px;
            object-fit: cover;
            border: 3px solid #e9ecef;
        }

        .profile-initial-large {
            width: 150px;
            height: 150px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 3rem;
            border: 3px solid #e9ecef;
        }

        /* Force error messages to display */
        .invalid-feedback {
            display: block !important;
            color: #dc3545 !important;
            margin-top: 0.25rem !important;
            font-weight: 500;
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #fff5f5 !important;
        }

        /* Alert styling */
        .alert {
            border-radius: 4px;
        }

        .alert ul {
            list-style-type: disc;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row mb-3">
                    <div class="col-lg-8 p-r-0 title-margin-right">
                        <div class="page-header">
                            <div class="page-title"><h1>My Profile</h1></div>
                        </div>
                        <span>Manage your account settings and preferences</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route(auth()->user()->role . '.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Profile</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                    <!-- Success Messages Only -->
                    @if(session('success'))
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="alert alert-success fade in alert-dismissable">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                <strong><i class="ti-check"></i> Success!</strong> {{ session('success') }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="alert alert-danger fade in alert-dismissable">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                <strong><i class="ti-alert"></i> Error!</strong> {{ session('error') }}
                            </div>
                        </div>
                    </div>
                    @endif



                    <!-- User Info Card -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="user-profile">
                                        <div style="display: flex; align-items: center;">
                                            <!-- Profile Photo -->
                                            <div class="user-photo m-b-30">
                                                @if($user->profile_photo)
                                                    <img src="{{ Storage::url($user->profile_photo) }}" alt="{{ $user->name }}" class="img-responsive" width="150" height="150" style="border-radius: 12px; object-fit: cover;">
                                                @else
                                                    <div class="profile-initial-large bg-primary text-white">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="user-profile-name">{{ $user->name }}</div>
                                                 <div class="info-item" style="padding: 0 15px">
                                                    <div class="info-value">
                                                        <span class="badge badge-light role-badge">{{ ucfirst($user->role) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                               
                                        <div class="custom-tab user-profile-tab">
                                            <ul class="nav nav-tabs" role="tablist">
                                                <li role="presentation" class="active"><a href="#1" aria-controls="1" role="tab" data-toggle="tab">Profile information</a></li>
                                            </ul>
                                            <div class="tab-content">
                                                <div role="tabpanel" class="tab-pane active" id="1">
                                                    <div class="contact-information">
                                                        
                                                        <div class="phone-content">
                                                            <span class="contact-title">Full Name:</span>
                                                            <span class="phone-number">{{ $user->name }}</span>
                                                        </div>
                                                        <div class="email-content">
                                                            <span class="contact-title">Email:</span>
                                                            <span class="contact-email">{{ $user->email }}</span>
                                                        </div>
                                                        <div class="address-content">
                                                            <span class="contact-title">Member Since:</span>
                                                            <span class="info-value">{{ $userStats['member_since'] }}</span>
                                                        </div>
                                                        <div class="website-content">
                                                            <span class="contact-title">Last Login:</span>
                                                            <span class="contact-website">{{ $userStats['last_login'] }}</span>
                                                        </div>
                                                        @if(isset($userStats['classes']))
                                                        <div class="birthday-content">
                                                            <span class="contact-title">Classes Teaching:</span>
                                                            <span class="birth-date">{{ $userStats['classes'] }}</span>
                                                        </div>
                                                        @endif
                                                        @if(isset($userStats['children']))
                                                        <div class="birthday-content">
                                                            <span class="contact-title">Children:</span>
                                                            <span class="birth-date">{{ $userStats['children'] }}</span>
                                                        </div>
                                                        @endif
                                                        <div class="birthday-content">
                                                            <span class="contact-title">Total Actions:</span>
                                                            <span class="birth-date">{{ number_format($userStats['total_actions']) }}</span>
                                                        </div>

                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /# column -->
                     
                    </div>
                    <!-- /# row -->
               

                      <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="custom-tab">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li role="presentation" class="active"><a href="#profile-info" aria-controls="profile-info" role="tab" data-toggle="tab"><i class="ti-user"></i> Profile Information</a></li>
                                            <li role="presentation"><a href="#change-password" aria-controls="change-password" role="tab" data-toggle="tab"><i class="ti-lock"></i> Change Password</a></li>
                                            <li role="presentation"><a href="#activity-log" aria-controls="activity-log" role="tab" data-toggle="tab"> <i class="ti-time"></i> Activity Log</a></li>
                                            <li role="presentation"><a href="#security" aria-controls="security" role="tab" data-toggle="tab"> <i class="ti-shield"></i> Security</a></li>
                                        </ul>
                                        <div class="tab-content">
                                            <!-- profile-info Tab -->
                                            <div role="tabpanel" class="tab-pane active" id="profile-info">
                                                @include('profile.partials.profile-info')
                                            </div>
                                            <!-- Change Password Tab -->
                                            <div role="tabpanel" class="tab-pane" id="change-password">
                                                @include('profile.partials.change-password') 
                                            </div>
                                            <!-- Activity Log Tab -->
                                            <div role="tabpanel" class="tab-pane" id="activity-log">
                                                 @include('profile.partials.activity-log')
                                            </div>
                                            <!-- Security Tab -->
                                            <div role="tabpanel" class="tab-pane" id="security">
                                                 @include('profile.partials.delete-account')
                                            </div>
                                          
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /# column -->
                    </div>


                <!-- Footer -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer">
                            <p>MLC Classroom - My Profile</p>
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
    console.log('Profile scripts loaded');

    // ========================================
    // TAB SWITCHING FOR VALIDATION ERRORS
    // ========================================
    @if($errors->updatePassword->any())
        setTimeout(function() {
            console.log('Switching to change-password tab');
            // Remove active class from all tabs
            $('.nav-tabs li').removeClass('active');
            $('.tab-pane').removeClass('active in');
            
            // Add active class to change-password tab
            $('.nav-tabs a[href="#change-password"]').parent().addClass('active');
            $('#change-password').addClass('active in');
            
            // Scroll to tab
            $('html, body').animate({
                scrollTop: $('.nav-tabs').offset().top - 20
            }, 300);
        }, 100);
    @endif

    @if($errors->userDeletion->any())
        setTimeout(function() {
            console.log('Switching to security tab');
            // Remove active class from all tabs
            $('.nav-tabs li').removeClass('active');
            $('.tab-pane').removeClass('active in');
            
            // Add active class to security tab
            $('.nav-tabs a[href="#security"]').parent().addClass('active');
            $('#security').addClass('active in');
            
            // Scroll to tab
            $('html, body').animate({
                scrollTop: $('.nav-tabs').offset().top - 20
            }, 300);
        }, 100);
    @endif

    // For default (profile info) errors - switch to profile-info tab
    @if($errors->any() && !$errors->updatePassword->any() && !$errors->userDeletion->any())
        setTimeout(function() {
            console.log('Switching to profile-info tab');
            // Remove active class from all tabs
            $('.nav-tabs li').removeClass('active');
            $('.tab-pane').removeClass('active in');
            
            // Add active class to profile-info tab
            $('.nav-tabs a[href="#profile-info"]').parent().addClass('active');
            $('#profile-info').addClass('active in');
            
            // Scroll to tab
            $('html, body').animate({
                scrollTop: $('.nav-tabs').offset().top - 20
            }, 300);
        }, 100);
    @endif

    // ========================================
    // PROFILE INFO TAB SCRIPTS
    // ========================================
    // Image preview for new photo
    $('#profile_photo').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Check file size (2MB max)
            if (file.size > 2048000) {
                alert('File size must not exceed 2MB');
                $(this).val('');
                $('#photo-preview-container').hide();
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#photo-preview').attr('src', e.target.result);
                $('#photo-preview-container').show();
            }
            reader.readAsDataURL(file);
        } else {
            $('#photo-preview-container').hide();
        }
    });

    // Handle photo deletion
    $(document).on('click', '#delete-photo-btn', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete your profile photo?')) {
            const deleteUrl = $(this).data('url');
            
            // Create a temporary form to submit the DELETE request
            const form = $('<form>', {
                'method': 'POST',
                'action': deleteUrl
            });
            
            // Add CSRF token
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': $('meta[name="csrf-token"]').attr('content')
            }));
            
            // Add DELETE method
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_method',
                'value': 'DELETE'
            }));
            
            // Append to body and submit
            form.appendTo('body').submit();
        }
    });

    // ========================================
    // CHANGE PASSWORD TAB SCRIPTS
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

    // Password strength checker
    $('#password').on('keyup', function() {
        const password = $(this).val();
        
        if (password.length === 0) {
            $('#password-strength').hide();
            return;
        }
        
        $('#password-strength').show();
        
        let strength = 0;
        let strengthText = '';
        let strengthColor = '';
        
        // Length check
        if (password.length >= 8) strength += 25;
        if (password.length >= 12) strength += 25;
        
        // Character variety checks
        if (/[a-z]/.test(password)) strength += 15;
        if (/[A-Z]/.test(password)) strength += 15;
        if (/[0-9]/.test(password)) strength += 10;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 10;
        
        // Set strength text and color
        if (strength < 40) {
            strengthText = 'Weak';
            strengthColor = 'danger';
        } else if (strength < 70) {
            strengthText = 'Fair';
            strengthColor = 'warning';
        } else if (strength < 90) {
            strengthText = 'Good';
            strengthColor = 'info';
        } else {
            strengthText = 'Strong';
            strengthColor = 'success';
        }
        
        $('#strength-text').text('Password strength: ' + strengthText)
            .removeClass('text-danger text-warning text-info text-success')
            .addClass('text-' + strengthColor);
        
        $('#strength-bar')
            .css('width', strength + '%')
            .removeClass('bg-danger bg-warning bg-info bg-success')
            .addClass('bg-' + strengthColor);
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
            $(this).siblings('.invalid-feedback').remove();
        }
    });

    // ========================================
    // SECURITY TAB SCRIPTS
    // ========================================
    // Enable delete button only when checkbox is checked
    $(document).on('change', '#confirm-delete', function() {
        console.log('Checkbox changed:', this.checked);
        $('#delete-btn').prop('disabled', !this.checked);
    });
    
    // Confirm before deleting
    $(document).on('submit', '#delete-account-form', function(e) {
        console.log('Delete form submitted');
        
        if (!confirm('Are you absolutely sure you want to delete your account? This action cannot be undone!')) {
            console.log('First confirmation cancelled');
            e.preventDefault();
            return false;
        }
        
        if (!confirm('This is your last chance. Are you really sure?')) {
            console.log('Second confirmation cancelled');
            e.preventDefault();
            return false;
        }
        
        console.log('Account deletion confirmed, submitting form');
    });
    
    // Debug: Check if delete button exists
    console.log('Delete button exists:', $('#delete-btn').length > 0);
    console.log('Delete form exists:', $('#delete-account-form').length > 0);
    console.log('Confirm checkbox exists:', $('#confirm-delete').length > 0);

    // Auto-dismiss success messages after 5 seconds
    setTimeout(function() {
        $('.alert-success').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
});
</script>
@endpush
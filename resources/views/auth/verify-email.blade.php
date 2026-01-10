@extends('layouts.auth.master')

@section('title', 'Verify Email')

@push('styles')
    <style>
        /* Alert messages */
        /* .alert {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid transparent;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }
        
        .login-logo span {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }

        .verification-message {
            font-size: 0.9rem;
            color: #495057;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }

        .verification-icon {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .verification-icon i {
            font-size: 4rem;
            color: #007bff;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        .btn-link {
            background: none;
            border: none;
            color: #6c757d;
            text-decoration: underline;
            cursor: pointer;
            padding: 0;
            font-size: 0.875rem;
        }

        .btn-link:hover {
            color: #495057;
        }

        @media (max-width: 576px) {
            .btn-group {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn-group form {
                width: 100%;
            }
            
            .btn-group button {
                width: 100%;
            }
        } */
    </style>
@endpush

@section('main_content')
    <div class="unix-login">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-lg-offset-3">
                    <div class="login-content">
                        <div class="login-logo">
                            <a href="{{ url('/') }}">
                                @if(config('app.logo'))
                                    <img src="{{ asset('storage/' . config('app.logo')) }}" alt="{{ config('app.name', 'MLC Classroom') }}" class="logo-img">
                                @else
                                    {{-- <span>MLC Classroom</span> --}}
                                @endif
                                <span>MLC CLASSROOM PORTAL</span>
                            </a>
                        </div>
                        <div class="login-form">
                            <!-- Verification Icon -->
                            <div class="verification-icon">
                                <i class="fa fa-envelope-o"></i>
                            </div>

                            <h4>Verify Your Email Address</h4>
                            
                            <!-- Main Verification Message -->
                            <div class="verification-message">
                                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                            </div>

                            <!-- Success Message (When Resend Link is Clicked) -->
                            @if (session('status') == 'verification-link-sent')
                                <div class="alert alert-success" role="alert">
                                    <i class="fa fa-check-circle"></i>
                                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="btn-group">
                                <!-- Resend Verification Email Form -->
                                <form method="POST" action="{{ route('verification.send') }}" style="flex: 1;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-flat" style="width: 100%;">
                                        <i class="fa fa-paper-plane"></i> Resend Verification Email
                                    </button>
                                </form>

                                <!-- Logout Form -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn-link">
                                        <i class="fa fa-sign-out"></i> Log Out
                                    </button>
                                </form>
                            </div>

                            <!-- Additional Help Text -->
                            <div class="register-link m-t-15 text-center">
                                <p style="font-size: 0.875rem; color: #6c757d;">
                                    <i class="fa fa-question-circle"></i> 
                                    Didn't receive the email? Check your spam folder or 
                                    <a href="#" onclick="event.preventDefault(); document.querySelector('form[action=\'{{ route('verification.send') }}\']').submit();">
                                        click here to resend
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- <script>
        // Auto-hide success alert after 10 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-success');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 10000);
            });
        });

        // Add loading state to resend button
        document.querySelectorAll('form[action="{{ route('verification.send') }}"]').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';
            });
        });

        // Add confirmation to logout button
        document.querySelector('form[action="{{ route('logout') }}"]').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to log out? You will need to verify your email before accessing your account again.')) {
                e.preventDefault();
                return false;
            }
        });
    </script> --}}
@endpush
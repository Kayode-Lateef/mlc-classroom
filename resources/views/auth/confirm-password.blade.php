@extends('layouts.auth.master')

@section('title', 'Confirm Password')

@push('styles')
    <style>
        /* Error message styling */
        /* .text-danger {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        } */
        
        /* Invalid input styling */
        /* .is-invalid {
            border-color: #dc3545 !important;
        }
         */
        /* Alert messages */
        /* .alert {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid transparent;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        } */
        
        /* Form enhancements */
        /* .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .login-logo span {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }

        .security-message {
            font-size: 0.9rem;
            color: #495057;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
        }

        .security-icon {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .security-icon i {
            font-size: 4rem;
            color: #ffc107;
        }

        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            text-align: center;
            margin-top: 1rem;
        }

        .password-input-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 5px 10px;
            font-size: 1rem;
        }

        .toggle-password:hover {
            color: #495057;
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
                                @php
                                    // Get logo from database, fallback to .env
                                    $logo = \App\Models\SystemSetting::get('school_logo') ?? config('app.logo');
                                @endphp
                
                                @if(config('app.logo'))
                                    <img src="{{ asset('storage/' . $logo) . '?v=' . time() }}" alt="{{ config('app.name', 'MLC Classroom') }}" class="logo-img">
                                @else
                                    {{-- <span>MLC Classroom</span> --}}
                                @endif
                                <span>MLC CLASSROOM PORTAL</span>
                            </a>
                        </div>
                        <div class="login-form">
                            <!-- Security Icon -->
                            <div class="security-icon">
                                <i class="fa fa-shield"></i>
                            </div>

                            <h4>Confirm Your Password</h4>
                            
                            <!-- Security Message -->
                            <div class="security-message">
                                <i class="fa fa-lock"></i>
                                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                            </div>

                            <!-- Error Messages -->
                            @if ($errors->any() && !$errors->has('password'))
                                <div class="alert alert-danger" role="alert">
                                    <strong>Whoops!</strong> There were some problems with your input.
                                </div>
                            @endif

                            <form method="POST" action="{{ route('password.confirm') }}">
                                @csrf

                                <!-- Password -->
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <div class="password-input-wrapper">
                                        <input 
                                            id="password"
                                            type="password" 
                                            name="password" 
                                            class="form-control @error('password') is-invalid @enderror" 
                                            placeholder="Enter your password"
                                            required 
                                            autofocus
                                            autocomplete="current-password">
                                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                                            <i class="fa fa-eye" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                    
                                    @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary btn-flat m-b-15">
                                    <i class="fa fa-check"></i> Confirm
                                </button>

                                <!-- Help Text -->
                                <div class="help-text">
                                    <i class="fa fa-info-circle"></i>
                                    For your security, we need to verify your identity before proceeding.
                                </div>

                                <!-- Forgot Password Link -->
                                @if (Route::has('password.request'))
                                    <div class="register-link m-t-15 text-center">
                                        <p>
                                            <a href="{{ route('password.request') }}">
                                                <i class="fa fa-question-circle"></i> Forgot your password?
                                            </a>
                                        </p>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- <script>
        // Toggle password visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Auto-hide alert messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
        });

        // Add loading state to submit button
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Confirming...';
        });

        // Focus on password input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('password').focus();
        });

        // Add keyboard shortcut (Enter key)
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
        });
    </script> --}}
@endpush

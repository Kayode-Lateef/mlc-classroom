@extends('layouts.auth.master')

@section('title', 'Reset Password')

@push('styles')
    <style>
        /* Error message styling */
        /* .text-danger {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
         */
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
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
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
        } */

        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        /* Password strength indicator */

        /* .password-requirements {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }

        .password-requirements ul {
            margin: 0.5rem 0 0 1.25rem;
            padding: 0;
        }

        .password-requirements li {
            margin-bottom: 0.25rem;
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
                            <h4>Reset Password</h4>
                            
                            <!-- Help Text -->
                            <div class="help-text">
                                Enter your email address and choose a new password for your account.
                            </div>

                            <!-- Session Status (Success/Error Messages) -->
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <!-- General Error Messages -->
                            @if ($errors->any() && !$errors->has('email') && !$errors->has('password') && !$errors->has('password_confirmation'))
                                <div class="alert alert-danger" role="alert">
                                    <strong>Whoops!</strong> There were some problems with your input.
                                </div>
                            @endif

                            <form method="POST" action="{{ route('password.store') }}">
                                @csrf

                                <!-- Password Reset Token (Hidden) -->
                                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                                <!-- Email Address -->
                                <div class="form-group">
                                    <label for="email">Email address</label>
                                    <input 
                                        id="email"
                                        type="email" 
                                        name="email" 
                                        class="form-control @error('email') is-invalid @enderror" 
                                        placeholder="Enter your email address"
                                        value="{{ old('email', $request->email) }}"
                                        required 
                                        autofocus 
                                        autocomplete="username">
                                    
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- New Password -->
                                <div class="form-group">
                                    <label for="password">New Password</label>
                                    <input 
                                        id="password"
                                        type="password" 
                                        name="password" 
                                        class="form-control @error('password') is-invalid @enderror" 
                                        placeholder="Enter new password"
                                        required 
                                        autocomplete="new-password">
                                    
                                    @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @else
                                        <div class="password-requirements">
                                            <ul>
                                                <li>At least 8 characters long</li>
                                                <li>Mix of letters, numbers, and symbols recommended</li>
                                            </ul>
                                        </div>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password</label>
                                    <input 
                                        id="password_confirmation"
                                        type="password" 
                                        name="password_confirmation" 
                                        class="form-control @error('password_confirmation') is-invalid @enderror" 
                                        placeholder="Confirm your new password"
                                        required 
                                        autocomplete="new-password">
                                    
                                    @error('password_confirmation')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary btn-flat m-b-15">
                                    Reset Password
                                </button>

                                <!-- Back to Login Link -->
                                <div class="register-link text-center">
                                    <p>Remember your password? <a href="{{ route('login') }}">Back to Login</a></p>
                                </div>
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
        // Auto-hide alert messages after 8 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 8000);
            });
        });

        // Password confirmation validation
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');

        confirmPasswordInput.addEventListener('input', function() {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        });

        passwordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        });

        // Add loading state to submit button
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Check if passwords match before submitting
            if (passwordInput.value !== confirmPasswordInput.value) {
                e.preventDefault();
                alert('Passwords do not match. Please check and try again.');
                return false;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Resetting password...';
        });

        // Show/hide password toggle (optional enhancement)
        function addPasswordToggle() {
            const passwordFields = document.querySelectorAll('input[type="password"]');
            
            passwordFields.forEach(function(field) {
                const wrapper = field.parentElement;
                const toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.className = 'btn btn-sm btn-outline-secondary';
                toggleBtn.style.cssText = 'position: absolute; right: 10px; top: 32px; z-index: 10;';
                toggleBtn.innerHTML = '<i class="fa fa-eye"></i>';
                
                toggleBtn.addEventListener('click', function() {
                    if (field.type === 'password') {
                        field.type = 'text';
                        toggleBtn.innerHTML = '<i class="fa fa-eye-slash"></i>';
                    } else {
                        field.type = 'password';
                        toggleBtn.innerHTML = '<i class="fa fa-eye"></i>';
                    }
                });
                
                wrapper.style.position = 'relative';
                wrapper.appendChild(toggleBtn);
            });
        }
        
        // Uncomment to enable password visibility toggle
        // addPasswordToggle();
    </script> --}}
@endpush
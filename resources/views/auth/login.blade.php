@extends('layouts.auth.master')

@section('title', 'Login')

@push('styles')
    <style>
        /* Error message styling */
        .text-danger {
            color: #dc3545;
            font-size: 1.2rem;
            margin-top: 0.25rem;
            display: block;
        }
        
        /* Invalid input styling */
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        /* Alert messages */
        .alert {
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
        
        /* Form enhancements */
        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        /* .login-logo span {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        } */

        /* Password toggle styling */
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
        }

        .toggle-password:focus {
            outline: none;
        }
    </style>
@endpush

@section('main_content')
    <div class="unix-login">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-lg-offset-3">
                    <div class="login-content">
                        <div class="login-logo">
                            <a href="{{ url('/') }}"><span>MLC CLASSROOM MANAGEMENT</span></a>
                        </div>
                        <div class="login-form">
                            <h4>Login</h4>
                            
                            <!-- Session Status Messages -->
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <!-- General Error Messages (if any) -->
                            @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
                                <div class="alert alert-danger" role="alert">
                                    <strong>Whoops!</strong> There were some problems with your input.
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <!-- Email Address -->
                                <div class="form-group">
                                    <label for="email">Email address</label>
                                    <input 
                                        id="email"
                                        type="email" 
                                        name="email" 
                                        class="form-control @error('email') is-invalid @enderror" 
                                        placeholder="Email"
                                        value="{{ old('email') }}"
                                        required 
                                        autofocus 
                                        autocomplete="username">
                                    
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Password with Toggle -->
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <div class="password-input-wrapper">
                                        <input 
                                            id="password"
                                            type="password" 
                                            name="password" 
                                            class="form-control @error('password') is-invalid @enderror" 
                                            placeholder="Password"
                                            required 
                                            autocomplete="current-password"
                                            style="padding-right: 40px;">
                                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                                            <i class="fa fa-eye" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                    
                                    @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Remember Me & Forgot Password -->
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> 
                                        Remember Me
                                    </label>
                                    
                                    @if (Route::has('password.request'))
                                        <label class="pull-right">
                                            <a href="{{ route('password.request') }}">Forgotten Password?</a>
                                        </label>
                                    @endif
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary btn-flat m-b-30 m-t-30">
                                    Sign in
                                </button>
                        
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
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

        // Optional: Add loading state to submit button
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Signing in...';
        });
    </script>
@endpush
@extends('layouts.auth.master')

@section('title', 'Forgot Password')

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
        } */
        
        /* Alert messages */
        /* .alert {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid transparent;
        } */
        
        /* .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        } */
        
        /* .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        } */
        
        /* .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
         */
        /* Form enhancements */
        /* .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
         */
        /* .login-logo span {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        } */

        .help-text {
            color: #6c757d;
            margin-bottom: 1.5rem;
            line-height: 1.5;
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
                            <h4>Forgot Password</h4>
                            
                            <!-- Help Text -->
                            <div class="help-text">
                                {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                            </div>

                            <!-- Session Status (Success Message) -->
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <!-- General Error Messages -->
                            @if ($errors->any() && !$errors->has('email'))
                                <div class="alert alert-danger" role="alert">
                                    <strong>Whoops!</strong> There were some problems with your input.
                                </div>
                            @endif

                            <form method="POST" action="{{ route('password.email') }}">
                                @csrf

                                <!-- Email Address -->
                                <div class="form-group">
                                    <label for="email">Email address</label>
                                    <input 
                                        id="email"
                                        type="email" 
                                        name="email" 
                                        class="form-control @error('email') is-invalid @enderror" 
                                        placeholder="Enter your email address"
                                        value="{{ old('email') }}"
                                        required 
                                        autofocus 
                                        autocomplete="email">
                                    
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary btn-flat m-b-15">
                                    Email Password Reset Link
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
        // Auto-hide alert messages after 8 seconds (longer for password reset confirmation)
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

        // Optional: Add loading state to submit button
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending reset link...';
        });
    </script> --}}
@endpush

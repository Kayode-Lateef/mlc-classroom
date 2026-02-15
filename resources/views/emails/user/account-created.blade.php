@extends('emails.layouts.base')

@section('additional-styles')
    /* Account Created — unique styles */
    .welcome-banner {
        background: linear-gradient(135deg, #E06829 0%, #3386F7 100%);
        color: white;
        padding: 30px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 25px;
    }
    .welcome-banner .hero-icon {
        font-size: 48px;
        margin-bottom: 10px;
    }
    .welcome-banner h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
    }
    .welcome-banner p {
        margin: 10px 0 0 0;
        font-size: 16px;
        opacity: 0.95;
    }

    .role-badge {
        display: inline-block;
        background: #3386F7;
        color: white;
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 14px;
        margin: 15px 0;
    }

    .welcome-message {
        font-size: 16px;
        color: #555;
        line-height: 1.8;
        margin-bottom: 25px;
        text-align: center;
    }

    .credentials-box {
        background: #fff3e0;
        border-left: 4px solid #E06829;
        padding: 25px;
        margin: 25px 0;
        border-radius: 4px;
    }
    .credentials-box h3 {
        margin: 0 0 15px 0;
        color: #E06829;
        font-size: 18px;
    }
    .credential-row {
        background: white;
        padding: 15px;
        margin: 10px 0;
        border-radius: 4px;
        border: 1px solid #e9ecef;
    }
    .credential-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    .credential-value {
        font-size: 16px;
        color: #333;
        font-weight: 500;
        font-family: 'Courier New', monospace;
    }

    .steps-box {
        background: #e3f2fd;
        border-left: 4px solid #3386F7;
        padding: 20px;
        margin: 25px 0;
        border-radius: 4px;
    }
    .steps-box h4 {
        margin: 0 0 10px 0;
        color: #3386F7;
        font-size: 16px;
    }
    .steps-box ul {
        margin: 10px 0 0 0;
        padding-left: 20px;
    }
    .steps-box li {
        margin: 8px 0;
        color: #555;
    }

    .verification-box {
        background: #fff3e0;
        padding: 15px 20px;
        margin: 20px 0;
        border-radius: 4px;
        border-left: 4px solid #E06829;
    }
@endsection

@section('content')
    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div class="hero-icon">🎉</div>
        <h2>Welcome to MLC Classroom!</h2>
        <p>Your account has been created successfully</p>
    </div>

    {{-- Role Badge --}}
    <div style="text-align: center;">
        <span class="role-badge">{{ ucfirst($role ?? $data['role'] ?? 'User') }} Account</span>
    </div>

    {{-- Welcome Message --}}
    <div class="welcome-message">
        <p>Hello <strong>{{ $user_name ?? $data['user_name'] ?? 'there' }}</strong>,</p>
        <p>Your MLC Classroom account has been created by <strong>{{ $created_by ?? $data['created_by'] ?? 'an administrator' }}</strong>.
        You can now access the platform using the details below.</p>
    </div>

    {{-- Password Setup Section --}}
    @if(isset($setup_url) || isset($data['setup_url']))
        @php $setupLink = $setup_url ?? $data['setup_url']; @endphp

        <div class="credentials-box">
            <h3>🔑 Set Your Password</h3>

            <div class="credential-row">
                <div class="credential-label">Email Address</div>
                <div class="credential-value">{{ $user_email ?? $data['user_email'] ?? '' }}</div>
            </div>

            @if((isset($requires_verification) && $requires_verification) || (isset($data['requires_verification']) && $data['requires_verification']))
                <div class="verification-box">
                    <p style="margin: 0; color: #856404; font-size: 14px;">
                        <strong>Step 1:</strong> Verify your email using the separate verification email.<br>
                        <strong>Step 2:</strong> Click the button below to set your password.
                    </p>
                </div>
            @else
                <p style="margin: 15px 0 5px 0; color: #555; font-size: 15px;">
                    Click the button below to set your password and start using your account:
                </p>
            @endif
        </div>

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ $setupLink }}" class="button" style="background: #28a745; padding: 16px 50px;">
                Set My Password
            </a>
        </div>

        <div class="alert-box warning" style="display: flex; align-items: start; gap: 12px;">
            <span style="font-size: 24px; flex-shrink: 0;">⏰</span>
            <p style="margin: 0; color: #856404; font-size: 14px;">
                <strong>Important:</strong> This link expires in 60 minutes for security.
                If it expires, use the "Forgot Password" option on the login page.
            </p>
        </div>
    @else
        {{-- Fallback: User already has access --}}
        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ $login_url ?? $data['login_url'] ?? route('login') }}" class="button">
                Log In to Your Account
            </a>
        </div>
    @endif

    {{-- Email Verification Notice --}}
    @if((isset($requires_verification) && $requires_verification) || (isset($data['requires_verification']) && $data['requires_verification']))
        <div class="steps-box">
            <h4>📧 Email Verification Required</h4>
            <p style="margin: 5px 0 0 0; color: #555;">
                Before you can log in, you need to verify your email address.
                Please check your inbox for a verification email and click the verification link.
            </p>
        </div>
    @endif

    {{-- Next Steps --}}
    <div class="steps-box">
        <h4>📋 Next Steps</h4>
        <ul>
            @if((isset($requires_verification) && $requires_verification) || (isset($data['requires_verification']) && $data['requires_verification']))
                <li>Check your email for the verification link</li>
                <li>Click the link to verify your email address</li>
            @endif
            <li>Set your password using the button above</li>
            <li>Login to your account</li>
            <li>Complete your profile information</li>
            <li>Explore the platform features</li>
        </ul>
    </div>
@endsection
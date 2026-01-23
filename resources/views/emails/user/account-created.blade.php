<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Welcome hero - MLC Orange to Blue gradient */
        .hero {
            background: linear-gradient(135deg, #E06829 0%, #3386F7 100%);
            color: white;
            padding: 50px 30px;
            text-align: center;
        }
        .hero-icon {
            font-size: 56px;
            margin-bottom: 15px;
        }
        .hero h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
        }
        .hero p {
            margin: 15px 0 0 0;
            font-size: 18px;
            opacity: 0.95;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .welcome-message {
            font-size: 16px;
            color: #555;
            line-height: 1.8;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .credentials-box {
            background: #fff3e0;
            border-left: 4px solid #E06829;
            padding: 25px;
            margin: 30px 0;
            border-radius: 4px;
        }
        .credentials-box h3 {
            margin: 0 0 15px 0;
            color: #E06829;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .role-badge {
            display: inline-block;
            background: #3386F7;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            margin: 20px 0;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #3386F7;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-box h4 {
            margin: 0 0 10px 0;
            color: #3386F7;
            font-size: 16px;
        }
        .info-box ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        .info-box li {
            margin: 8px 0;
            color: #555;
        }
        
        .warning-box {
            background: #fff3e0;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
            border-left: 4px solid #E06829;
            display: flex;
            align-items: start;
            gap: 12px;
        }
        .warning-box .icon {
            font-size: 24px;
            flex-shrink: 0;
        }
        .warning-box p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }
        
        .button {
            display: inline-block;
            padding: 16px 40px;
            background: #3386F7;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 25px 0;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .button:hover {
            background: #2872d9;
            box-shadow: 0 4px 12px rgba(51, 134, 247, 0.3);
        }
        
        .footer {
            background-color: #f8f9fc;
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
        
        @media only screen and (max-width: 600px) {
            .container {
                margin: 10px;
            }
            .hero h1 {
                font-size: 26px;
            }
            .content {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Hero Section -->
        <div class="hero">
            <div class="hero-icon">🎉</div>
            <h1>Welcome to MLC Classroom!</h1>
            <p>Your account has been created successfully</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div style="text-align: center;">
                <span class="role-badge">{{ ucfirst($role) }} Account</span>
            </div>
            
            <div class="welcome-message">
                <p>Hello <strong>{{ $user_name }}</strong>,</p>
                <p>Your MLC Classroom account has been created by <strong>{{ $created_by }}</strong>. 
                You can now access the platform using the credentials below.</p>
            </div>
            
            <!-- Credentials Box -->
            @if(isset($temporary_password))
            <div class="credentials-box">
                <h3>🔑 Your Login Credentials</h3>
                
                <div class="credential-row">
                    <div class="credential-label">Email Address</div>
                    <div class="credential-value">{{ $user_email }}</div>
                </div>
                
                <div class="credential-row">
                    <div class="credential-label">Temporary Password</div>
                    <div class="credential-value">{{ $temporary_password }}</div>
                </div>
            </div>
            
            <div class="warning-box">
                <div class="icon">⚠️</div>
                <p>
                    <strong>Important:</strong> Please change your password after your first login for security. 
                    You can do this from your profile settings.
                </p>
            </div>
            @endif
            
            @if(isset($requires_verification) && $requires_verification)
            <div class="info-box">
                <h4>📧 Email Verification Required</h4>
                <p>Before you can log in, you need to verify your email address. 
                Please check your inbox for a verification email and click the verification link.</p>
            </div>
            @else
            <div style="text-align: center;">
                <a href="{{ $url ?? route('login') }}" class="button">Login to Your Account</a>
            </div>
            @endif
            
            <!-- Next Steps -->
            <div class="info-box">
                <h4>📋 Next Steps</h4>
                <ul>
                    @if(isset($requires_verification) && $requires_verification)
                    <li>Check your email for verification link</li>
                    <li>Click the link to verify your email address</li>
                    @endif
                    <li>Login with your credentials</li>
                    <li>Change your password in profile settings</li>
                    <li>Complete your profile information</li>
                    <li>Explore the platform features</li>
                </ul>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p style="color: #6c757d; font-size: 13px;">
                <strong>Maidstone Learning Centre</strong><br>
                Maidstone, Kent, United Kingdom
            </p>
            
            <p style="margin-top: 20px;">
                <a href="{{ config('app.url') }}" style="color: #3386F7; text-decoration: none;">Visit Website</a>
                <span style="color: #dee2e6; margin: 0 8px;">|</span>
                <a href="{{ route('login') }}" style="color: #3386F7; text-decoration: none;">Login to Portal</a>
            </p>
            
            <p style="margin-top: 15px; font-size: 12px; color: #868e96;">
                This is an automated notification from MLC Classroom Management System.<br>
                Please do not reply directly to this email.
            </p>
            
            <p style="margin-top: 10px; font-size: 11px; color: #adb5bd;">
                © {{ date('Y') }} Maidstone Learning Centre. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
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
        .header {
            background: #ffffff;
            color: #333333;
            padding: 40px 30px;
            text-align: center;
            border-bottom: 3px solid #3386F7;
        }
        .header .logo-img {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #333333;
        }
        .header .subtitle {
            margin: 8px 0 0 0;
            font-size: 14px;
            opacity: 0.7;
            font-weight: 400;
            color: #666666;
        }
        .content {
            padding: 40px 30px;
        }
        .message-title {
            font-size: 22px;
            font-weight: 700;
            color: #333333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #E06829;
        }
        .message-body {
            font-size: 16px;
            color: #555555;
            margin-bottom: 25px;
            line-height: 1.7;
        }
        .button {
            display: inline-block;
            padding: 14px 35px;
            background: #3386F7;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .button:hover {
            background: #2872d9;
            box-shadow: 0 4px 12px rgba(51, 134, 247, 0.3);
        }
        .info-box {
            background-color: #f8f9fc;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-box strong {
            color: #3386F7;
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .info-box ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        .info-box li {
            margin: 8px 0;
            color: #555555;
        }
        .alert-box {
            background-color: #fff3e0;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .alert-box strong {
            color: #E06829;
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .footer {
            background-color: #f8f9fc;
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 8px 0;
        }
        .footer a {
            color: #3386F7;
            text-decoration: none;
            font-weight: 500;
        }
        .footer a:hover {
            color: #E06829;
            text-decoration: underline;
        }
        .footer-logo {
            font-weight: 700;
            font-size: 16px;
            color: #3386F7;
            margin-bottom: 10px;
        }
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e9ecef, transparent);
            margin: 20px 0;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 4px;
            }
            .header {
                padding: 30px 20px;
            }
            .content {
                padding: 30px 20px;
            }
            .message-title {
                font-size: 20px;
            }
            .button {
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            @if(config('app.logo'))
                <img src="{{ asset('storage/' . config('app.logo')) }}" alt="{{ config('app.name', 'MLC Classroom') }}" class="logo-img">
            @endif
           {{-- <h1>{{ config('app.name', 'MLC Classroom') }}</h1> --}}
            <p class="subtitle">Maidstone Learning Centre</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="message-title">{{ $title }}</div>
            
            <div class="message-body">
                {!! nl2br(e($content)) !!}
            </div>
            
            <!-- Action Button -->
            @if(isset($url) && $url)
                <div style="text-align: center;">
                    <a href="{{ $url }}" class="button">View Details</a>
                </div>
            @endif
            
            <!-- Additional Information Box -->
            @if(isset($data) && is_array($data) && count($data) > 0)
                @php
                    $displayData = array_filter($data, function($key) {
                        return !in_array($key, ['url', 'type', 'title', 'message', 'icon']);
                    }, ARRAY_FILTER_USE_KEY);
                @endphp
                
                @if(count($displayData) > 0)
                    <div class="info-box">
                        <strong>📋 Additional Details:</strong>
                        <ul>
                            @foreach($displayData as $key => $value)
                                @if(is_string($value) || is_numeric($value))
                                    <li>
                                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> 
                                        {{ $value }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
            
            <!-- Alert Box for Emergency/Important Notifications -->
            @if(isset($type) && $type === 'emergency')
                <div class="alert-box">
                    <strong>⚠️ Important Notice</strong>
                    <p style="margin: 5px 0 0 0; color: #555;">
                        This is an urgent notification. Please take immediate action if required.
                    </p>
                </div>
            @endif
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-logo">{{ config('app.name', 'MLC Classroom') }}</div>
            <p style="color: #6c757d; font-size: 13px;">
                Maidstone Learning Centre<br>
                Maidstone, Kent, United Kingdom
            </p>
            
            <div class="divider"></div>
            
            <p>
                <a href="{{ config('app.url') }}">Visit Website</a>
                <span style="color: #dee2e6; margin: 0 8px;">|</span>
                <a href="{{ route('login') }}">Login to Portal</a>
            </p>
            
            <p style="margin-top: 20px; font-size: 12px; color: #868e96;">
                This is an automated notification from MLC Classroom Management System.<br>
                Please do not reply directly to this email.
            </p>
            
            <p style="margin-top: 15px; font-size: 11px; color: #adb5bd;">
                © {{ date('Y') }} Maidstone Learning Centre. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
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
        
        /* ✅ NEW: Type-specific title colors */
        .message-title.student-enrolled { border-color: #28a745; }
        .message-title.student-unenrolled { border-color: #dc3545; }
        .message-title.homework { border-color: #17a2b8; }
        .message-title.attendance { border-color: #ffc107; }
        .message-title.user-deleted { border-color: #dc3545; }
        .message-title.emergency { border-color: #dc3545; animation: pulse 2s infinite; }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
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
        
        /* ✅ NEW: Type-specific info boxes */
        .info-box {
            background-color: #f8f9fc;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            border-left: 4px solid #3386F7;
        }
        .info-box.student { border-left-color: #28a745; }
        .info-box.homework { border-left-color: #17a2b8; }
        .info-box.attendance { border-left-color: #ffc107; }
        .info-box.user { border-left-color: #6c757d; }
        
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
        
        /* ✅ NEW: Enhanced alert box */
        .alert-box {
            background-color: #fff3e0;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            border-left: 4px solid #E06829;
        }
        .alert-box.danger {
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }
        .alert-box.success {
            background-color: #d4edda;
            border-left-color: #28a745;
        }
        .alert-box.warning {
            background-color: #fff3cd;
            border-left-color: #ffc107;
        }
        
        .alert-box strong {
            color: #E06829;
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .alert-box.danger strong { color: #dc3545; }
        .alert-box.success strong { color: #28a745; }
        .alert-box.warning strong { color: #ffc107; }
        
        /* ✅ NEW: Student/User card - MLC Brand Colors */
        .user-card {
            background: linear-gradient(135deg, #3386F7 0%, #E06829 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .user-card .name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .user-card .role {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* ✅ NEW: Homework details card - MLC Blue */
        .homework-card {
            background: linear-gradient(135deg, #3386F7 0%, #2872d9 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .homework-card .title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .homework-card .due-date {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* ✅ NEW: Class/Schedule card - MLC Orange */
        .class-card {
            background: linear-gradient(135deg, #E06829 0%, #c55a22 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .class-card .title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .class-card .subtitle {
            font-size: 14px;
            opacity: 0.9;
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
            <p class="subtitle">Maidstone Learning Centre</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            {{-- ✅ Dynamic title color based on type --}}
            <div class="message-title {{ $data['type'] ?? 'general' }}">{{ $title }}</div>
            
            <div class="message-body">
                {!! nl2br(e($content)) !!}
            </div>
            
            {{-- ✅ NEW: Type-specific sections --}}
            
            {{-- Student Enrollment Card --}}
            @if(isset($data['student_name']) && in_array($data['type'] ?? '', ['enrollment', 'student_enrolled', 'student_unenrolled']))
                <div class="user-card">
                    <div class="name">👨‍🎓 {{ $data['student_name'] }}</div>
                    @if(isset($data['class_name']))
                        <div class="role">{{ $data['class_name'] }}</div>
                    @endif
                    @if(isset($data['weekly_hours']))
                        <div class="role" style="margin-top: 8px;">{{ $data['weekly_hours'] }} hours per week</div>
                    @endif
                </div>
            @endif
            
            {{-- User Account Card --}}
            @if(isset($data['user_name']) && in_array($data['type'] ?? '', ['user_created', 'user_deleted', 'account_created']))
                <div class="user-card">
                    <div class="name">👤 {{ $data['user_name'] }}</div>
                    @if(isset($data['user_role']))
                        <div class="role">{{ ucfirst($data['user_role']) }}</div>
                    @endif
                    @if(isset($data['user_email']))
                        <div class="role" style="margin-top: 8px;">{{ $data['user_email'] }}</div>
                    @endif
                </div>
            @endif
            
            {{-- Homework Card --}}
            @if(isset($data['homework_title']) && in_array($data['type'] ?? '', ['homework_assigned', 'homework_graded', 'homework_reminder', 'homework_overdue']))
                <div class="homework-card">
                    <div class="title">📝 {{ $data['homework_title'] }}</div>
                    @if(isset($data['class_name']))
                        <div class="due-date">Class: {{ $data['class_name'] }}</div>
                    @endif
                    @if(isset($data['due_date']))
                        <div class="due-date" style="margin-top: 5px;">Due: {{ \Carbon\Carbon::parse($data['due_date'])->format('d M Y') }}</div>
                    @endif
                    @if(isset($data['grade']))
                        <div class="due-date" style="margin-top: 5px; font-size: 18px; font-weight: 700;">Grade: {{ $data['grade'] }}</div>
                    @endif
                </div>
            @endif
            
            {{-- Action Button --}}
            @if(isset($url) && $url)
                <div style="text-align: center;">
                    <a href="{{ $url }}" class="button">View Details</a>
                </div>
            @endif
            
            {{-- Additional Information Box --}}
            @if(isset($data) && is_array($data) && count($data) > 0)
                @php
                    // Filter out already displayed or system fields
                    $excludeKeys = ['url', 'type', 'title', 'message', 'icon', 'student_name', 'student_id', 
                                   'user_name', 'user_email', 'user_role', 'user_id', 
                                   'homework_title', 'homework_id', 'class_name', 'class_id', 
                                   'due_date', 'grade', 'weekly_hours'];
                    
                    $displayData = array_filter($data, function($key) use ($excludeKeys) {
                        return !in_array($key, $excludeKeys);
                    }, ARRAY_FILTER_USE_KEY);
                @endphp
                
                @if(count($displayData) > 0)
                    <div class="info-box {{ $data['type'] ?? 'general' }}">
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
            
            {{-- Alert Boxes Based on Type --}}
            @if(isset($type))
                @if($type === 'emergency' || $type === 'absence')
                    <div class="alert-box danger">
                        <strong>⚠️ Urgent Notice</strong>
                        <p style="margin: 5px 0 0 0; color: #721c24;">
                            This is an urgent notification. Please take immediate action if required.
                        </p>
                    </div>
                @elseif($type === 'homework_overdue')
                    <div class="alert-box warning">
                        <strong>⏰ Overdue Alert</strong>
                        <p style="margin: 5px 0 0 0; color: #856404;">
                            This homework is now overdue. Please submit as soon as possible.
                        </p>
                    </div>
                @elseif($type === 'account_activated' || $type === 'student_enrolled')
                    <div class="alert-box success">
                        <strong>✅ Success</strong>
                        <p style="margin: 5px 0 0 0; color: #155724;">
                            Action completed successfully. You can now proceed.
                        </p>
                    </div>
                @endif
            @endif
            
            {{-- Temporary Password Display --}}
            @if(isset($data['temporary_password']))
                <div class="alert-box warning">
                    <strong>🔑 Login Credentials</strong>
                    <p style="margin: 10px 0; color: #856404;">
                        <strong>Email:</strong> {{ $data['user_email'] ?? 'Your registered email' }}<br>
                        <strong>Temporary Password:</strong> <code style="background: #fff; padding: 5px 10px; border-radius: 3px; font-size: 14px;">{{ $data['temporary_password'] }}</code>
                    </p>
                    <p style="margin: 5px 0 0 0; color: #856404; font-size: 14px;">
                        ⚠️ Please change your password after your first login for security.
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
            
            @if(isset($data['sent_by']))
                <p style="margin-top: 10px; font-size: 12px; color: #adb5bd;">
                    Sent by: {{ $data['sent_by'] }}
                    @if(isset($data['sent_at']))
                        on {{ $data['sent_at'] }}
                    @endif
                </p>
            @endif
            
            <p style="margin-top: 15px; font-size: 11px; color: #adb5bd;">
                © {{ date('Y') }} Maidstone Learning Centre. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
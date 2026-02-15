<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'MLC Classroom' }}</title>
    <style>
        /* ══════════════════════════════════════════
           MLC EMAIL BASE STYLES
           Shared across ALL email templates
           ══════════════════════════════════════════ */
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

        /* Type-specific title underlines */
        .message-title.student-enrolled,
        .message-title.student_enrolled,
        .message-title.enrollment { border-color: #28a745; }
        .message-title.student-unenrolled,
        .message-title.student_unenrolled { border-color: #dc3545; }
        .message-title.homework,
        .message-title.homework_assigned,
        .message-title.homework_graded { border-color: #17a2b8; }
        .message-title.attendance,
        .message-title.attendance_marked { border-color: #ffc107; }
        .message-title.user-deleted,
        .message-title.user_deleted { border-color: #dc3545; }
        .message-title.emergency { border-color: #dc3545; }
        .message-title.account_created,
        .message-title.account_activated { border-color: #28a745; }

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
        }

        /* Info boxes */
        .info-box {
            background-color: #f8f9fc;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            border-left: 4px solid #3386F7;
        }
        .info-box.student, .info-box.enrollment, .info-box.student_enrolled { border-left-color: #28a745; }
        .info-box.homework, .info-box.homework_assigned, .info-box.homework_graded { border-left-color: #17a2b8; }
        .info-box.attendance, .info-box.attendance_marked { border-left-color: #ffc107; }
        .info-box.user, .info-box.user_created, .info-box.user_deleted { border-left-color: #6c757d; }
        .info-box strong {
            color: #3386F7;
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .info-box ul { margin: 10px 0 0 0; padding-left: 20px; }
        .info-box li { margin: 8px 0; color: #555555; }

        /* Alert boxes */
        .alert-box {
            background-color: #fff3e0;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            border-left: 4px solid #E06829;
        }
        .alert-box.danger { background-color: #f8d7da; border-left-color: #dc3545; }
        .alert-box.success { background-color: #d4edda; border-left-color: #28a745; }
        .alert-box.warning { background-color: #fff3cd; border-left-color: #ffc107; }
        .alert-box strong { display: block; margin-bottom: 10px; font-size: 16px; }
        .alert-box.danger strong { color: #dc3545; }
        .alert-box.success strong { color: #28a745; }
        .alert-box.warning strong { color: #856404; }

        /* Cards (MLC brand colours) */
        .user-card {
            background: linear-gradient(135deg, #3386F7 0%, #E06829 100%);
            color: white; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;
        }
        .user-card .name { font-size: 20px; font-weight: 700; margin-bottom: 5px; }
        .user-card .role { font-size: 14px; opacity: 0.9; }

        .homework-card {
            background: linear-gradient(135deg, #3386F7 0%, #2872d9 100%);
            color: white; padding: 20px; border-radius: 8px; margin: 20px 0;
        }
        .homework-card .title { font-size: 18px; font-weight: 700; margin-bottom: 10px; }
        .homework-card .due-date { font-size: 14px; opacity: 0.9; }

        .class-card {
            background: linear-gradient(135deg, #E06829 0%, #c55a22 100%);
            color: white; padding: 20px; border-radius: 8px; margin: 20px 0;
        }
        .class-card .title { font-size: 18px; font-weight: 700; margin-bottom: 10px; }
        .class-card .subtitle { font-size: 14px; opacity: 0.9; }

        .footer {
            background-color: #f8f9fc;
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
        .footer p { margin: 8px 0; }
        .footer a { color: #3386F7; text-decoration: none; font-weight: 500; }
        .footer-logo { font-weight: 700; font-size: 16px; color: #3386F7; margin-bottom: 10px; }
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e9ecef, transparent);
            margin: 20px 0;
        }

        @media only screen and (max-width: 600px) {
            .container { margin: 10px; border-radius: 4px; }
            .header { padding: 30px 20px; }
            .content { padding: 30px 20px; }
            .message-title { font-size: 20px; }
            .button { display: block; text-align: center; }
        }

        @yield('additional-styles')
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
            @yield('content')
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
                &copy; {{ date('Y') }} Maidstone Learning Centre. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
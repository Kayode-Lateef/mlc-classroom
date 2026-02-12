<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Maintenance - MLC Classroom</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .maintenance-container {
            text-align: center;
            max-width: 540px;
            padding: 40px 30px;
        }
        .maintenance-icon {
            width: 100px;
            height: 100px;
            background: rgba(51, 134, 247, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        .maintenance-icon svg {
            width: 48px;
            height: 48px;
            fill: none;
            stroke: #3386f7;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        h1 {
            color: #2d3748;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .message {
            color: #4a5568;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .status-bar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(224, 104, 41, 0.08);
            border: 1px solid rgba(224, 104, 41, 0.2);
            border-radius: 24px;
            padding: 8px 20px;
            font-size: 0.85rem;
            color: #e06829;
            font-weight: 600;
            margin-bottom: 30px;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            background: #e06829;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .footer-text {
            color: #a0aec0;
            font-size: 0.8rem;
            line-height: 1.6;
        }
        .footer-text a {
            color: #3386f7;
            text-decoration: none;
        }
        .footer-text a:hover {
            text-decoration: underline;
        }
        .logout-link {
            display: inline-block;
            margin-top: 20px;
            color: #3386f7;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 8px 20px;
            border: 1px solid #3386f7;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .logout-link:hover {
            background: #3386f7;
            color: #fff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">
            <svg viewBox="0 0 24 24">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
        </div>

        <h1>System Maintenance</h1>

        <div class="status-bar">
            <span class="status-dot"></span>
            Maintenance in progress
        </div>

        <p class="message">
            {{ $message ?? 'The system is currently undergoing scheduled maintenance. We apologise for the inconvenience and will be back shortly.' }}
        </p>

        @php
            // Safe lookup — if the DB is unreachable during real maintenance,
            // fall back to sensible defaults rather than throwing an error.
            try {
                $schoolName  = \App\Models\SystemSetting::get('school_name', 'Maidstone Learning Centre');
                $schoolEmail = \App\Models\SystemSetting::get('school_email', 'info@maidstonelearning.co.uk');
            } catch (\Exception $e) {
                $schoolName  = 'Maidstone Learning Centre';
                $schoolEmail = 'info@maidstonelearning.co.uk';
            }
        @endphp

        <p class="footer-text">
            {{ $schoolName }}<br>
            If you need urgent assistance, please contact
            <a href="mailto:{{ $schoolEmail }}">{{ $schoolEmail }}</a>
        </p>

        @auth
            <a href="{{ route('logout') }}" class="logout-link"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Sign Out
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        @endauth
    </div>
</body>
</html>
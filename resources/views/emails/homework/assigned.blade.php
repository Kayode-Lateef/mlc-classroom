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
        
        /* Homework-specific hero - MLC Blue */
        .hero {
            background: linear-gradient(135deg, #3386F7 0%, #2872d9 100%);
            color: white;
            padding: 50px 30px;
            text-align: center;
        }
        .hero-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .hero h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .hero p {
            margin: 15px 0 0 0;
            font-size: 16px;
            opacity: 0.95;
        }
        
        .homework-card {
            background: white;
            margin: -30px 30px 30px 30px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        .homework-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .homework-details {
            background: #f8f9fc;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        .detail-value {
            color: #333;
            font-weight: 500;
        }
        .due-date-badge {
            background: #E06829;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .description-box {
            background: #fff;
            border-left: 4px solid #3386F7;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .description-box h3 {
            margin: 0 0 10px 0;
            color: #3386F7;
            font-size: 16px;
        }
        .description-box p {
            margin: 0;
            color: #555;
            line-height: 1.6;
        }
        
        .content {
            padding: 30px;
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
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Hero Section -->
        <div class="hero">
            <div class="hero-icon">📝</div>
            <h1>New Homework Assigned</h1>
            <p>You have a new assignment to complete</p>
        </div>
        
        <!-- Homework Card -->
        <div class="homework-card">
            <div class="homework-title">{{ $homework_title }}</div>
            
            <div class="homework-details">
                <div class="detail-row">
                    <span class="detail-label">Class:</span>
                    <span class="detail-value">{{ $class_name }}</span>
                </div>
                @if(isset($teacher_name))
                <div class="detail-row">
                    <span class="detail-label">Teacher:</span>
                    <span class="detail-value">{{ $teacher_name }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Assigned:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($assigned_date)->format('d M Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Due Date:</span>
                    <span class="detail-value">
                        <span class="due-date-badge">{{ \Carbon\Carbon::parse($due_date)->format('d M Y') }}</span>
                    </span>
                </div>
            </div>
            
            @if(isset($description) && $description)
            <div class="description-box">
                <h3>Assignment Description:</h3>
                <p>{{ $description }}</p>
            </div>
            @endif
            
            <div style="text-align: center; margin-top: 25px;">
                <a href="{{ $url }}" class="button">View Assignment</a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p style="color: #6c757d; font-size: 13px;">
                <strong>Maidstone Learning Centre</strong><br>
                Maidstone, Kent, United Kingdom
            </p>
            
            <p style="margin-top: 15px; font-size: 12px; color: #868e96;">
                This is an automated notification from MLC Classroom Management System.
            </p>
            
            <p style="margin-top: 10px; font-size: 11px; color: #adb5bd;">
                © {{ date('Y') }} Maidstone Learning Centre. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
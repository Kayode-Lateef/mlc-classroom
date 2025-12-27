<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 30px;
        }
        .message {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .badge-general { background-color: #6c757d; color: white; }
        .badge-emergency { background-color: #dc3545; color: white; }
        .badge-homework { background-color: #007bff; color: white; }
        .badge-progress_report { background-color: #28a745; color: white; }
        .badge-schedule_change { background-color: #ffc107; color: #333; }
        .badge-absence { background-color: #17a2b8; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MLC Classroom</h1>
        </div>
        
        <div class="content">
            <span class="badge badge-{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
            
            <h2 style="margin: 10px 0; color: #212529;">{{ $title }}</h2>
            
            <div class="message">
                <p style="margin: 0; white-space: pre-line;">{{ $message }}</p>
            </div>
            
            <p style="margin: 20px 0 5px 0; font-size: 14px; color: #6c757d;">
                <strong>Sent by:</strong> {{ $sent_by }}
            </p>
            <p style="margin: 0; font-size: 14px; color: #6c757d;">
                <strong>Date:</strong> {{ $sent_at }}
            </p>
        </div>
        
        <div class="footer">
            <p style="margin: 0 0 10px 0;">&copy; {{ date('Y') }} Maidstone Learning Centre. All rights reserved.</p>
            <p style="margin: 0;">This is an automated notification. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low SMS Credit Balance Alert</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .alert-box {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
        }
        .balance {
            font-size: 32px;
            font-weight: bold;
            color: #ef4444;
            text-align: center;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            background: #f3f4f6;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-radius: 0 0 10px 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚠ Low SMS Credit Balance</h1>
        <p>Action Required</p>
    </div>
    
    <div class="content">
        <p>Hello {{ $admin_name }},</p>
        
        <div class="alert-box">
            <strong>Alert:</strong> Your SMS credit balance has fallen below the threshold and requires immediate attention.
        </div>
        
        <p><strong>Current Balance:</strong></p>
        <div class="balance">£{{ number_format($balance, 2) }}</div>
        
        <p><strong>Low Balance Threshold:</strong> £{{ number_format($threshold, 2) }}</p>
        
        <p>To ensure uninterrupted SMS notifications for student absences and important alerts, please top up your SMS credits as soon as possible.</p>
        
        <p><strong>What happens if credits run out?</strong></p>
        <ul>
            <li>Parent absence notifications will not be sent</li>
            <li>Emergency alerts cannot be delivered</li>
            <li>Schedule change notifications will be delayed</li>
        </ul>
        
        <p style="text-align: center;">
            <a href="{{ config('app.url') }}/admin/sms-config" class="button">Top Up SMS Credits</a>
        </p>
        
        <p>If you have any questions, please contact your Twilio account representative or check your Twilio dashboard.</p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>This is an automated system alert.</p>
    </div>
</body>
</html>
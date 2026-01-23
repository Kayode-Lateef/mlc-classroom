<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        /* Same base styles as notification.blade.php */
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
        
        /* Student-specific hero section - MLC Brand Colors */
        .hero {
            background: linear-gradient(135deg, #3386F7 0%, #E06829 100%);
            color: white;
            padding: 50px 30px;
            text-align: center;
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
        
        .student-card {
            background: white;
            margin: -30px 30px 30px 30px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .student-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3386F7 0%, #E06829 100%);
            border-radius: 50%;
            margin: 0 auto 20px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
        }
        .student-name {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        .student-details {
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Hero Section -->
        <div class="hero">
            <h1>🎉 Student Enrolled Successfully!</h1>
            <p>A new student has been added to MLC Classroom</p>
        </div>
        
        <!-- Student Card -->
        <div class="student-card">
            <div class="student-avatar">👨‍🎓</div>
            <div class="student-name">{{ $student_name }}</div>
            
            <div class="student-details">
                <div class="detail-row">
                    <span class="detail-label">Parent/Guardian:</span>
                    <span class="detail-value">{{ $parent_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Weekly Hours:</span>
                    <span class="detail-value">{{ $weekly_hours }} hours</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Enrollment Date:</span>
                    <span class="detail-value">{{ $enrollment_date }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span style="background: #28a745; color: white; padding: 3px 10px; border-radius: 12px; font-size: 12px;">
                            Active
                        </span>
                    </span>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 25px;">
                <a href="{{ $url }}" class="button">View Student Profile</a>
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
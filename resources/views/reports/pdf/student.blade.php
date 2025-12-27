<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Report - {{ $student->full_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #333; margin: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
        h1 { margin: 0; font-size: 22pt; }
        .section { margin: 30px 0; }
        .section-title { color: #667eea; font-size: 14pt; border-bottom: 2px solid #667eea; padding-bottom: 5px; margin-bottom: 15px; }
        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 30%; padding: 8px; font-weight: bold; background-color: #f8f9fa; }
        .info-value { display: table-cell; padding: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #667eea; color: white; padding: 8px; text-align: left; font-size: 10pt; }
        td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 10pt; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 3px; font-size: 9pt; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .footer { margin-top: 50px; text-align: center; font-size: 9pt; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Performance Report</h1>
        <p>{{ $student->full_name }}</p>
    </div>

    <div class="section">
        <div class="section-title">Student Information</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Name:</div><div class="info-value">{{ $student->full_name }}</div></div>
            <div class="info-row"><div class="info-label">Student ID:</div><div class="info-value">{{ $student->id }}</div></div>
            <div class="info-row"><div class="info-label">Parent:</div><div class="info-value">{{ $student->parent->name ?? 'N/A' }}</div></div>
            <div class="info-row"><div class="info-label">Status:</div><div class="info-value">{{ ucfirst($student->status) }}</div></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Performance Summary</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Attendance Rate:</div><div class="info-value"><strong>{{ $attendanceData['rate'] }}%</strong></div></div>
            <div class="info-row"><div class="info-label">Homework Completion:</div><div class="info-value"><strong>{{ $homeworkData['rate'] }}%</strong></div></div>
            <div class="info-row"><div class="info-label">Average Grade:</div><div class="info-value"><strong>{{ $homeworkData['average_grade'] }}</strong></div></div>
            <div class="info-row"><div class="info-label">Progress Notes:</div><div class="info-value"><strong>{{ $progressData['count'] }}</strong></div></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Recent Attendance (Last 10 Records)</div>
        <table>
            <tr><th>Date</th><th>Class</th><th>Status</th></tr>
            @foreach($attendanceData['records'] as $att)
            <tr>
                <td>{{ $att->date->format('d/m/Y') }}</td>
                <td>{{ $att->class->name }}</td>
                <td><span class="badge badge-{{ $att->status == 'present' ? 'success' : 'warning' }}">{{ ucfirst($att->status) }}</span></td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Maidstone Learning Centre | Generated: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Class Report - {{ $class->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; margin: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
        h1 { margin: 0; font-size: 22pt; }
        .section-title { color: #667eea; font-size: 14pt; border-bottom: 2px solid #667eea; padding-bottom: 5px; margin: 20px 0 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #667eea; color: white; padding: 8px; text-align: left; font-size: 10pt; }
        td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 10pt; }
        .info-grid { margin: 20px 0; }
        .info-row { padding: 5px 0; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 3px; font-size: 9pt; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: #333; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Class Performance Report</h1>
        <p>{{ $class->name }}</p>
        <p style="font-size: 11pt;">Teacher: {{ $class->teacher->name ?? 'N/A' }}</p>
    </div>

    <div class="section-title">Class Statistics</div>
    <div class="info-grid">
        <div class="info-row"><strong>Total Students:</strong> {{ $class->students->count() }}</div>
        <div class="info-row"><strong>Attendance Rate:</strong> {{ $attendanceStats['rate'] }}%</div>
        <div class="info-row"><strong>Homework Completion:</strong> {{ $homeworkStats['average_completion'] }}%</div>
    </div>

    <div class="section-title">Student Performance</div>
    <table>
        <tr><th>Student</th><th>Attendance %</th><th>Homework %</th><th>Avg Grade</th></tr>
        @foreach($studentStats as $stat)
        <tr>
            <td>{{ $stat['student']->full_name }}</td>
            <td><span class="badge badge-{{ $stat['attendance_rate'] >= 80 ? 'success' : 'warning' }}">{{ $stat['attendance_rate'] }}%</span></td>
            <td><span class="badge badge-{{ $stat['homework_rate'] >= 80 ? 'success' : 'warning' }}">{{ $stat['homework_rate'] }}%</span></td>
            <td>{{ $stat['average_grade'] }}</td>
        </tr>
        @endforeach
    </table>

    <div style="margin-top: 50px; text-align: center; font-size: 9pt; color: #666;">
        &copy; {{ date('Y') }} Maidstone Learning Centre | {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
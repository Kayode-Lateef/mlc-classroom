<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Homework Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; margin: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
        h1 { margin: 0; font-size: 22pt; }
        .stats-box { display: inline-block; width: 23%; padding: 10px; margin: 10px 5px; border: 2px solid #667eea; text-align: center; }
        .stat-label { font-size: 9pt; color: #666; text-transform: uppercase; }
        .stat-value { font-size: 18pt; font-weight: bold; color: #667eea; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #667eea; color: white; padding: 8px; text-align: left; font-size: 10pt; }
        td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 10pt; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Homework Report</h1>
        <p>{{ $dateFrom }} to {{ $dateTo }}</p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <div class="stats-box"><div class="stat-label">Total</div><div class="stat-value">{{ $stats['total_assignments'] }}</div></div>
        <div class="stats-box"><div class="stat-label">Completion</div><div class="stat-value">{{ $stats['average_completion'] }}%</div></div>
        <div class="stats-box"><div class="stat-label">Pending</div><div class="stat-value">{{ $stats['grading_pending'] }}</div></div>
        <div class="stats-box"><div class="stat-label">Overdue</div><div class="stat-value">{{ $stats['overdue'] }}</div></div>
    </div>

    <table>
        <tr><th>Assignment</th><th>Class</th><th>Due Date</th><th>Completion</th><th>Avg Grade</th></tr>
        @foreach($homeworkAssignments as $hw)
        <tr>
            <td>{{ $hw->title }}</td>
            <td>{{ $hw->class->name }}</td>
            <td>{{ $hw->due_date->format('d/m/Y') }}</td>
            <td>{{ $hw->completion_rate }}%</td>
            <td>{{ $hw->average_grade }}</td>
        </tr>
        @endforeach
    </table>

    <div style="margin-top: 50px; text-align: center; font-size: 9pt; color: #666;">
        &copy; {{ date('Y') }} Maidstone Learning Centre | {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
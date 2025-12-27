<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Attendance Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #333; margin: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24pt; }
        .header p { margin: 5px 0 0 0; font-size: 12pt; }
        .stats-grid { display: table; width: 100%; margin-bottom: 30px; }
        .stat-box { display: table-cell; width: 25%; padding: 15px; text-align: center; border: 2px solid #667eea; margin: 0 5px; }
        .stat-label { font-size: 10pt; color: #666; text-transform: uppercase; }
        .stat-value { font-size: 20pt; font-weight: bold; color: #667eea; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #667eea; color: white; padding: 10px; text-align: left; font-size: 11pt; }
        td { padding: 8px; border-bottom: 1px solid #ddd; font-size: 10pt; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 9pt; font-weight: bold; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 9pt; color: #666; padding: 10px; border-top: 1px solid #ddd; }
        .page-number:before { content: "Page " counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <h1>MLC Classroom</h1>
        <p>Attendance Report</p>
        <p style="font-size: 10pt;">{{ $dateFrom }} to {{ $dateTo }}</p>
    </div>

    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-label">Total Records</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Present</div>
            <div class="stat-value" style="color: #28a745;">{{ number_format($stats['present']) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Absent</div>
            <div class="stat-value" style="color: #dc3545;">{{ number_format($stats['absent']) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Attendance Rate</div>
            <div class="stat-value">{{ $stats['attendance_rate'] }}%</div>
        </div>
    </div>

    <h3 style="color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px;">Attendance Details</h3>
    
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Student</th>
                <th>Class</th>
                <th>Status</th>
                <th>Marked By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendanceRecords as $record)
            <tr>
                <td>{{ $record->date->format('d/m/Y') }}</td>
                <td>{{ $record->student->full_name }}</td>
                <td>{{ $record->class->name }}</td>
                <td>
                    @if($record->status == 'present')
                        <span class="badge badge-success">Present</span>
                    @elseif($record->status == 'absent')
                        <span class="badge badge-danger">Absent</span>
                    @else
                        <span class="badge badge-warning">{{ ucfirst($record->status) }}</span>
                    @endif
                </td>
                <td>{{ $record->markedBy->name ?? 'System' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Maidstone Learning Centre | Generated on {{ now()->format('d/m/Y H:i') }} | <span class="page-number"></span></p>
    </div>
</body>
</html>
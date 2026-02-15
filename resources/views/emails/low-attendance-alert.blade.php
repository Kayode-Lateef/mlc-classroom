@extends('emails.layouts.base')

@section('content')
    <div class="message-title" style="border-color: #dc3545;">Low Attendance Alert</div>

    <div class="message-body">
        <p>Dear Parent/Guardian,</p>
        <p><strong>{{ $student->full_name }}</strong>'s attendance has dropped below the acceptable threshold and requires your attention.</p>
    </div>

    <div class="alert-box danger">
        <strong>⚠️ Attendance Warning</strong>
        <p style="margin: 10px 0 0 0; color: #721c24;">
            Your child's attendance is currently at <strong style="font-size: 18px;">{{ $attendance_percentage }}%</strong>,
            which is below the <strong>{{ $threshold }}%</strong> minimum threshold.
        </p>
    </div>

    <div class="info-box attendance" style="border-left-color: #ffc107;">
        <strong>📊 Attendance Summary:</strong>
        <ul>
            <li><strong>Student:</strong> {{ $student->full_name }}</li>
            <li><strong>Current Attendance:</strong> {{ $attendance_percentage }}%</li>
            <li><strong>Required Minimum:</strong> {{ $threshold }}%</li>
        </ul>
    </div>

    <div class="message-body">
        <p>Regular attendance is essential for your child's learning progress. If there are circumstances affecting attendance, please contact us so we can provide appropriate support.</p>
    </div>

    <div style="text-align: center;">
        <a href="{{ route('parent.attendance.show', $student->id) }}" class="button">View Attendance Record</a>
    </div>
@endsection
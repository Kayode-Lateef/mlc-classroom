@extends('emails.layouts.base')

@section('additional-styles')
    /* Attendance Marked — unique styles */
    .attendance-hero {
        background: linear-gradient(135deg, #ffc107 0%, #E06829 100%);
        color: white;
        padding: 30px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 25px;
    }
    .attendance-hero .hero-icon { font-size: 48px; margin-bottom: 10px; }
    .attendance-hero h2 { margin: 0; font-size: 24px; font-weight: 700; }
    .attendance-hero p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.95; }

    .status-badge {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 16px;
        margin: 10px 0;
    }
    .status-present { background: #d4edda; color: #155724; }
    .status-absent { background: #f8d7da; color: #721c24; }
    .status-late { background: #fff3cd; color: #856404; }
    .status-unauthorized { background: #f8d7da; color: #721c24; }

    .attendance-details {
        background: #f8f9fc;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
    }
    .attendance-details p { margin: 8px 0; color: #555; }
    .attendance-details strong { color: #333; }
@endsection

@section('content')
    {{-- Attendance Hero Banner --}}
    <div class="attendance-hero">
        <div class="hero-icon">📋</div>
        <h2>Attendance Update</h2>
        <p>{{ $data['class_name'] ?? $class_name ?? 'Class Session' }}</p>
    </div>

    {{-- Greeting --}}
    <div class="message-body">
        <p>Dear Parent/Guardian,</p>
        <p>Attendance has been recorded for <strong>{{ $data['student_name'] ?? $student_name ?? 'your child' }}</strong>.</p>
    </div>

    {{-- Status Badge --}}
    @php
        $status = $data['status'] ?? $status ?? 'present';
        $statusClass = 'status-' . strtolower($status);
        $statusLabels = [
            'present' => '✅ Present',
            'absent' => '❌ Absent',
            'late' => '⏰ Late',
            'unauthorized' => '⚠️ Unauthorized Absence',
        ];
        $statusLabel = $statusLabels[strtolower($status)] ?? ucfirst($status);
    @endphp

    <div style="text-align: center;">
        <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </div>

    {{-- Attendance Details --}}
    <div class="attendance-details">
        <p><strong>👨‍🎓 Student:</strong> {{ $data['student_name'] ?? $student_name ?? '' }}</p>
        <p><strong>🏫 Class:</strong> {{ $data['class_name'] ?? $class_name ?? '' }}</p>
        <p><strong>📅 Date:</strong> {{ isset($data['date']) ? \Carbon\Carbon::parse($data['date'])->format('d M Y') : ($date ?? now()->format('d M Y')) }}</p>
        @if(isset($data['time']) || isset($time))
            <p><strong>🕐 Time:</strong> {{ $data['time'] ?? $time }}</p>
        @endif
        @if(isset($data['notes']) || isset($notes))
            <p><strong>📝 Notes:</strong> {{ $data['notes'] ?? $notes }}</p>
        @endif
    </div>

    {{-- Absence-specific alert --}}
    @if(in_array(strtolower($status), ['absent', 'unauthorized']))
        <div class="alert-box danger">
            <strong>⚠️ Action Required</strong>
            <p style="margin: 5px 0 0 0; color: #721c24;">
                Your child was marked absent. If this is unexpected, please contact us immediately.
            </p>
        </div>
    @endif

    {{-- CTA --}}
    <div style="text-align: center;">
        <a href="{{ $data['url'] ?? $url ?? '#' }}" class="button">View Attendance Record</a>
    </div>
@endsection
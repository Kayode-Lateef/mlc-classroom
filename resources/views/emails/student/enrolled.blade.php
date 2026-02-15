@extends('emails.layouts.base')

@section('additional-styles')
    /* Student Enrolled — unique styles */
    .enrolled-hero {
        background: linear-gradient(135deg, #3386F7 0%, #E06829 100%);
        color: white;
        padding: 30px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 25px;
    }
    .enrolled-hero .hero-icon { font-size: 48px; margin-bottom: 10px; }
    .enrolled-hero h2 { margin: 0; font-size: 24px; font-weight: 700; }
    .enrolled-hero p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.95; }

    .student-info-card {
        background: #f8f9fc;
        border: 2px solid #3386F7;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        margin: 20px 0;
    }
    .student-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #3386F7 0%, #E06829 100%);
        border-radius: 50%;
        margin: 0 auto 15px auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        color: white;
    }
    .student-name { font-size: 22px; font-weight: 700; color: #333; margin-bottom: 5px; }

    .enrollment-details {
        background: #f8f9fc;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
    }
    .enrollment-details p { margin: 8px 0; color: #555; }
    .enrollment-details strong { color: #333; }
@endsection

@section('content')
    {{-- Enrolled Hero Banner --}}
    <div class="enrolled-hero">
        <div class="hero-icon">🎓</div>
        <h2>Student Enrolled Successfully</h2>
        <p>{{ $data['class_name'] ?? $class_name ?? 'New Class' }}</p>
    </div>

    {{-- Greeting --}}
    <div class="message-body">
        <p>Dear Parent/Guardian,</p>
        <p>We're pleased to confirm that your child has been enrolled in a new class.</p>
    </div>

    {{-- Student Card --}}
    <div class="student-info-card">
        <div class="student-avatar">👨‍🎓</div>
        <div class="student-name">{{ $data['student_name'] ?? $student_name ?? '' }}</div>
    </div>

    {{-- Enrollment Details --}}
    <div class="enrollment-details">
        <p><strong>🏫 Class:</strong> {{ $data['class_name'] ?? $class_name ?? '' }}</p>
        @if(isset($data['subject']) || isset($subject))
            <p><strong>📚 Subject:</strong> {{ $data['subject'] ?? $subject }}</p>
        @endif
        @if(isset($data['teacher_name']) || isset($teacher_name))
            <p><strong>👩‍🏫 Teacher:</strong> {{ $data['teacher_name'] ?? $teacher_name }}</p>
        @endif
        @if(isset($data['weekly_hours']) || isset($weekly_hours))
            <p><strong>🕐 Weekly Hours:</strong> {{ $data['weekly_hours'] ?? $weekly_hours }} hours per week</p>
        @endif
        @if(isset($data['start_date']) || isset($start_date))
            <p><strong>📅 Start Date:</strong> {{ isset($data['start_date']) ? \Carbon\Carbon::parse($data['start_date'])->format('d M Y') : $start_date }}</p>
        @endif
    </div>

    {{-- Success Alert --}}
    <div class="alert-box success">
        <strong>✅ Enrollment Confirmed</strong>
        <p style="margin: 5px 0 0 0; color: #155724;">
            Your child is now enrolled and can attend classes as scheduled.
        </p>
    </div>

    {{-- CTA --}}
    <div style="text-align: center;">
        <a href="{{ $data['url'] ?? $url ?? '#' }}" class="button">View Enrollment Details</a>
    </div>
@endsection
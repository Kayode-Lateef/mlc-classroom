@extends('emails.layouts.base')

@section('additional-styles')
    /* Homework Graded — unique styles */
    .graded-hero {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 30px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 25px;
    }
    .graded-hero .hero-icon { font-size: 48px; margin-bottom: 10px; }
    .graded-hero h2 { margin: 0; font-size: 24px; font-weight: 700; }
    .graded-hero p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.95; }

    .grade-card {
        background: #f8f9fc;
        border: 2px solid #28a745;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        margin: 20px 0;
    }
    .grade-label { font-size: 14px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
    .grade-value { font-size: 36px; font-weight: 700; color: #28a745; margin: 10px 0; }
    .grade-subtitle { font-size: 14px; color: #555; }

    .homework-info {
        background: #f8f9fc;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
    }
    .homework-info p { margin: 8px 0; color: #555; }
    .homework-info strong { color: #333; }

    .feedback-box {
        background: #e8f5e9;
        border-left: 4px solid #28a745;
        padding: 20px;
        margin: 20px 0;
        border-radius: 4px;
    }
    .feedback-box h3 { margin: 0 0 10px 0; color: #28a745; font-size: 16px; }
    .feedback-box p { margin: 0; color: #555; line-height: 1.6; }
@endsection

@section('content')
    {{-- Graded Hero Banner --}}
    <div class="graded-hero">
        <div class="hero-icon">🎓</div>
        <h2>Homework Has Been Graded</h2>
        <p>{{ $data['class_name'] ?? $class_name ?? 'Your Class' }}</p>
    </div>

    {{-- Greeting --}}
    <div class="message-body">
        <p>Dear Parent/Guardian,</p>
        <p>Homework for <strong>{{ $data['student_name'] ?? $student_name ?? 'your child' }}</strong> has been graded.</p>
    </div>

    {{-- Grade Card --}}
    @if(isset($data['grade']) || isset($grade))
        <div class="grade-card">
            <div class="grade-label">Overall Grade</div>
            <div class="grade-value">{{ $data['grade'] ?? $grade }}</div>
            <div class="grade-subtitle">{{ $data['homework_title'] ?? $homework_title ?? '' }}</div>
        </div>
    @endif

    {{-- Homework Info --}}
    <div class="homework-info">
        <p><strong>📚 Homework:</strong> {{ $data['homework_title'] ?? $homework_title ?? '' }}</p>
        <p><strong>🏫 Class:</strong> {{ $data['class_name'] ?? $class_name ?? '' }}</p>
        @if(isset($data['due_date']) || isset($due_date))
            <p><strong>📅 Due Date:</strong> {{ isset($data['due_date']) ? \Carbon\Carbon::parse($data['due_date'])->format('d M Y') : $due_date }}</p>
        @endif
        @if(isset($data['graded_at']) || isset($graded_at))
            <p><strong>✅ Graded:</strong> {{ isset($data['graded_at']) ? \Carbon\Carbon::parse($data['graded_at'])->format('d M Y') : $graded_at }}</p>
        @endif
    </div>

    {{-- Teacher Feedback --}}
    @if(isset($data['teacher_comments']) || isset($teacher_comments))
        <div class="feedback-box">
            <h3>💬 Teacher Feedback</h3>
            <p>{{ $data['teacher_comments'] ?? $teacher_comments }}</p>
        </div>
    @endif

    {{-- CTA --}}
    <div style="text-align: center;">
        <a href="{{ $data['url'] ?? $url ?? '#' }}" class="button" style="background: #28a745;">View Full Results</a>
    </div>
@endsection
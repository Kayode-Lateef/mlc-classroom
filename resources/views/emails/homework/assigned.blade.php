@extends('emails.layouts.base')

@section('additional-styles')
    /* Homework Assigned — unique styles */
    .homework-hero {
        background: linear-gradient(135deg, #3386F7 0%, #2872d9 100%);
        color: white;
        padding: 30px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 25px;
    }
    .homework-hero .hero-icon { font-size: 48px; margin-bottom: 10px; }
    .homework-hero h2 { margin: 0; font-size: 24px; font-weight: 700; }
    .homework-hero p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.95; }

    .homework-details {
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
    .detail-row:last-child { border-bottom: none; }
    .detail-label { font-weight: 600; color: #6c757d; }
    .detail-value { color: #333; font-weight: 500; }
    .due-date-badge {
        background: #E06829;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 14px;
    }
    .description-box {
        background: #fff;
        border-left: 4px solid #3386F7;
        padding: 20px;
        margin: 20px 0;
        border-radius: 4px;
    }
    .description-box h3 { margin: 0 0 10px 0; color: #3386F7; font-size: 16px; }
    .description-box p { margin: 0; color: #555; line-height: 1.6; }
@endsection

@section('content')
    {{-- Homework Hero Banner --}}
    <div class="homework-hero">
        <div class="hero-icon">📝</div>
        <h2>New Homework Assigned</h2>
        <p>{{ $data['class_name'] ?? $class_name ?? 'Your Class' }}</p>
    </div>

    {{-- Greeting --}}
    <div class="message-body">
        <p>Dear Parent/Guardian,</p>
        <p>New homework has been assigned for <strong>{{ $data['student_name'] ?? $student_name ?? 'your child' }}</strong>.</p>
    </div>

    {{-- Homework Details --}}
    <div class="homework-details">
        <div class="detail-row">
            <span class="detail-label">📚 Homework</span>
            <span class="detail-value">{{ $data['homework_title'] ?? $homework_title ?? $title ?? '' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">🏫 Class</span>
            <span class="detail-value">{{ $data['class_name'] ?? $class_name ?? '' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">📅 Due Date</span>
            <span class="due-date-badge">{{ isset($data['due_date']) ? \Carbon\Carbon::parse($data['due_date'])->format('d M Y') : ($due_date ?? '') }}</span>
        </div>
        @if(isset($data['assigned_date']) || isset($assigned_date))
        <div class="detail-row">
            <span class="detail-label">📆 Assigned</span>
            <span class="detail-value">{{ isset($data['assigned_date']) ? \Carbon\Carbon::parse($data['assigned_date'])->format('d M Y') : $assigned_date }}</span>
        </div>
        @endif
    </div>

    {{-- Description --}}
    @if(isset($data['description']) || isset($description))
        <div class="description-box">
            <h3>📖 Description</h3>
            <p>{{ $data['description'] ?? $description }}</p>
        </div>
    @endif

    {{-- CTA --}}
    <div style="text-align: center;">
        <a href="{{ $data['url'] ?? $url ?? '#' }}" class="button">View Homework Details</a>
    </div>

    <p style="color: #888; font-size: 14px; text-align: center; margin-top: 20px;">
        Please ensure the homework is submitted before the due date.
    </p>
@endsection
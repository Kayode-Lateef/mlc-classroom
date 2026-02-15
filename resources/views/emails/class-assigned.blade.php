@extends('emails.layouts.base')

@section('content')
    <div class="message-title" style="border-color: #3386F7;">New Class Assignment</div>

    <div class="message-body">
        <p>Dear {{ $teacher->name }},</p>
        <p>You have been assigned to teach a new class by {{ $assigned_by }}.</p>
    </div>

    <div class="class-card">
        <div class="title">📚 {{ $class->name }}</div>
        <div class="subtitle">{{ $student_count }} Students Enrolled</div>
        @if($class->schedule)
            <div class="subtitle" style="margin-top: 5px;">Schedule: {{ $class->schedule }}</div>
        @endif
    </div>

    <p style="color: #555;">You can now access the class, view student information, and manage class activities.</p>

    <div style="text-align: center;">
        <a href="{{ route('teacher.classes.show', $class->id) }}" class="button">View Class Details</a>
    </div>
@endsection
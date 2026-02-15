@extends('emails.layouts.base')

@section('content')
    <div class="message-title homework_reminder" style="border-color: #E06829;">Homework Due Tomorrow</div>

    <div class="message-body">
        <p>Dear Parent/Guardian,</p>
        <p>This is a friendly reminder that homework for <strong>{{ $student->full_name }}</strong> is due tomorrow.</p>
    </div>

    <div class="info-box homework" style="background-color: #f8f9fa; border-left: 4px solid #17a2b8; padding: 20px; border-radius: 8px;">
        <p style="margin: 5px 0;"><strong>Class:</strong> {{ $homework->class->name }}</p>
        <p style="margin: 5px 0;"><strong>Homework:</strong> {{ $homework->title }}</p>
        <p style="margin: 5px 0;"><strong>Due Date:</strong> {{ $due_date }}</p>

        @if($homework->description)
            <p style="margin-top: 15px;"><strong>Description:</strong></p>
            <p style="margin: 5px 0;">{{ $homework->description }}</p>
        @endif
    </div>

    <p style="color: #555;">Please ensure the homework is submitted before the due date.</p>

    <div style="text-align: center;">
        <a href="{{ route('parent.homework.show', $homework->id) }}" class="button">View Homework Details</a>
    </div>
@endsection
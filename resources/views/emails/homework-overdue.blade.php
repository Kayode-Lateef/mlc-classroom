@extends('emails.layouts.base')

@section('content')
    <div class="message-title homework_overdue" style="border-color: #dc3545;">Homework Overdue Alert</div>

    <div class="message-body">
        <p>Dear Parent/Guardian,</p>
        <p>The following homework for <strong>{{ $student->full_name }}</strong> is now <strong style="color: #dc3545;">overdue</strong> and has not been submitted.</p>
    </div>

    <div class="alert-box danger" style="border-left: 4px solid #dc3545; background-color: #fff3cd; padding: 20px; margin: 20px 0;">
        <p style="margin: 5px 0;"><strong>Class:</strong> {{ $homework->class->name }}</p>
        <p style="margin: 5px 0;"><strong>Homework:</strong> {{ $homework->title }}</p>
        <p style="margin: 5px 0;"><strong>Was Due:</strong> {{ $due_date }}</p>
    </div>

    <p style="color: #555;">Please submit the homework as soon as possible to avoid further delays in the child's learning progress.</p>

    <div style="text-align: center;">
        <a href="{{ route('parent.homework.show', $homework->id) }}" class="button" style="background: #dc3545;">Submit Homework Now</a>
    </div>

    <p style="margin-top: 30px; color: #555;">If there are any issues preventing submission, please contact the teacher immediately.</p>
@endsection
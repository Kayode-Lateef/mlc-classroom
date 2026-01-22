@extends('emails.layout')

@section('content')
<h2 style="color: #dc3545;">Homework Overdue Alert</h2>

<p>Dear Parent/Guardian,</p>

<p>The following homework for <strong>{{ $student->full_name }}</strong> is now <strong style="color: #dc3545;">overdue</strong> and has not been submitted.</p>

<div style="background-color: #fff3cd; border-left: 4px solid #dc3545; padding: 20px; margin: 20px 0;">
    <p style="margin: 5px 0;"><strong>Class:</strong> {{ $homework->class->name }}</p>
    <p style="margin: 5px 0;"><strong>Homework:</strong> {{ $homework->title }}</p>
    <p style="margin: 5px 0;"><strong>Was Due:</strong> {{ $due_date }}</p>
</div>

<p>Please submit the homework as soon as possible to avoid further delays in the child's learning progress.</p>

<a href="{{ route('parent.homework.show', $homework->id) }}" style="display: inline-block; padding: 12px 30px; background-color: #dc3545; color: #ffffff; text-decoration: none; border-radius: 5px; margin-top: 20px;">
    Submit Homework Now
</a>

<p style="margin-top: 30px;">If there are any issues preventing submission, please contact the teacher immediately.</p>
@endsection
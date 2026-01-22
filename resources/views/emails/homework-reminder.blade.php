@extends('emails.layout')

@section('content')
<h2 style="color: #e06829;">Homework Due Tomorrow</h2>

<p>Dear Parent/Guardian,</p>

<p>This is a friendly reminder that homework for <strong>{{ $student->full_name }}</strong> is due tomorrow.</p>

<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <p style="margin: 5px 0;"><strong>Class:</strong> {{ $homework->class->name }}</p>
    <p style="margin: 5px 0;"><strong>Homework:</strong> {{ $homework->title }}</p>
    <p style="margin: 5px 0;"><strong>Due Date:</strong> {{ $due_date }}</p>
    
    @if($homework->description)
    <p style="margin-top: 15px;"><strong>Description:</strong></p>
    <p style="margin: 5px 0;">{{ $homework->description }}</p>
    @endif
</div>

<p>Please ensure the homework is submitted before the due date.</p>

<a href="{{ route('parent.homework.show', $homework->id) }}" style="display: inline-block; padding: 12px 30px; background-color: #3386f7; color: #ffffff; text-decoration: none; border-radius: 5px; margin-top: 20px;">
    View Homework Details
</a>
@endsection
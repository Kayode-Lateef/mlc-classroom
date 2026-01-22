@extends('emails.layout')

@section('content')
<h2 style="color: #3386f7;">New Class Assignment</h2>

<p>Dear {{ $teacher->name }},</p>

<p>You have been assigned to teach a new class by {{ $assigned_by }}.</p>

<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <p style="margin: 5px 0;"><strong>Class Name:</strong> {{ $class->name }}</p>
    <p style="margin: 5px 0;"><strong>Students Enrolled:</strong> {{ $student_count }}</p>
    
    @if($class->schedule)
    <p style="margin: 5px 0;"><strong>Schedule:</strong> {{ $class->schedule }}</p>
    @endif
</div>

<p>You can now access the class, view student information, and manage class activities.</p>

<a href="{{ route('teacher.classes.show', $class->id) }}" style="display: inline-block; padding: 12px 30px; background-color: #3386f7; color: #ffffff; text-decoration: none; border-radius: 5px; margin-top: 20px;">
    View Class Details
</a>
@endsection
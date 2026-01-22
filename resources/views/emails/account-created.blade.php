@extends('emails.layout')

@section('content')
<h2 style="color: #3386f7;">Welcome to MLC Classroom!</h2>

<p>Dear {{ $user->name }},</p>

<p>Your account has been created by {{ $created_by }}. You can now access the MLC Classroom platform.</p>

@if(isset($password))
<div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #3386f7; margin: 20px 0;">
    <p style="margin: 0;"><strong>Your Login Credentials:</strong></p>
    <p style="margin: 5px 0;">Email: <strong>{{ $user->email }}</strong></p>
    <p style="margin: 5px 0;">Temporary Password: <strong>{{ $password }}</strong></p>
</div>

<p style="color: #e06829;"><strong>Important:</strong> Please change your password after your first login.</p>
@endif

<p>Your role: <strong>{{ ucfirst($user->role) }}</strong></p>

<a href="{{ $login_url }}" style="display: inline-block; padding: 12px 30px; background-color: #3386f7; color: #ffffff; text-decoration: none; border-radius: 5px; margin-top: 20px;">
    Login to MLC Classroom
</a>

<p style="margin-top: 30px;">If you have any questions, please contact us at {{ config('mail.from.address') }}</p>
@endsection
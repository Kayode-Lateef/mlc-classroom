@extends('emails.layouts.base')

@section('content')
    {{-- Title --}}
    <div class="message-title {{ $data['type'] ?? $type ?? 'general' }}">{{ $title }}</div>

    {{-- Body --}}
    <div class="message-body">
        {!! nl2br(e($content)) !!}
    </div>

    {{-- Type-specific cards --}}
    @include('emails.partials.student-card')
    @include('emails.partials.user-card')
    @include('emails.partials.homework-card')

    {{-- Action button --}}
    @if(isset($url) && $url && !isset($data['setup_url']))
        <div style="text-align: center;">
            <a href="{{ $url }}" class="button">View Details</a>
        </div>
    @endif

    {{-- Additional details --}}
    @include('emails.partials.additional-details')

    {{-- Alert boxes --}}
    @include('emails.partials.alert-boxes')

    {{-- Password setup link --}}
    @include('emails.partials.setup-link')
@endsection
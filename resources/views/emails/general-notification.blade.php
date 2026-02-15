{{-- M-4 FIX: Accepts both $content and $messageContent for backward compatibility --}}
@extends('emails.layouts.base')

@section('content')
    <div class="message-title {{ $data['type'] ?? $type ?? 'general' }}">{{ $title }}</div>

    <div class="message-body">
        {!! nl2br(e($content ?? $messageContent ?? '')) !!}
    </div>

    @if(isset($url) && $url)
        <div style="text-align: center;">
            <a href="{{ $url }}" class="button">View Details</a>
        </div>
    @endif

    @include('emails.partials.additional-details')
@endsection
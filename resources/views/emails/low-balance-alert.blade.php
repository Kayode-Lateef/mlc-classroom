@extends('emails.layouts.base')

@section('additional-styles')
    /* Low Balance Alert — unique styles */
    .balance-display {
        font-size: 48px;
        font-weight: bold;
        color: #dc3545;
        text-align: center;
        margin: 25px 0;
    }
    .balance-label {
        font-size: 14px;
        color: #6c757d;
        text-transform: uppercase;
        text-align: center;
        letter-spacing: 0.5px;
    }
    .threshold-info {
        background: #f8f9fc;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
    }
    .threshold-info p { margin: 8px 0; color: #555; }
    .threshold-info strong { color: #333; }
@endsection

@section('content')
    <div class="message-title" style="border-color: #dc3545;">⚠ Low SMS Credit Balance</div>

    <div class="message-body">
        <p>Hello {{ $admin_name ?? $data['admin_name'] ?? 'Administrator' }},</p>
    </div>

    <div class="alert-box danger">
        <strong>Action Required</strong>
        <p style="margin: 5px 0 0 0; color: #721c24;">
            Your SMS credit balance has fallen below the alert threshold and requires immediate attention.
        </p>
    </div>

    <div class="balance-label">Current SMS Credit Balance</div>
    <div class="balance-display">{{ $current_balance ?? $data['current_balance'] ?? '0' }}</div>

    <div class="threshold-info">
        <p><strong>Alert Threshold:</strong> {{ $threshold ?? $data['threshold'] ?? '100' }} credits</p>
        @if(isset($last_topped_up) || isset($data['last_topped_up']))
            <p><strong>Last Topped Up:</strong> {{ $last_topped_up ?? $data['last_topped_up'] }}</p>
        @endif
    </div>

    <p style="color: #555;">Please top up your SMS credits to ensure uninterrupted notification delivery to parents and staff.</p>

    <div style="text-align: center;">
        <a href="{{ $data['url'] ?? $url ?? '#' }}" class="button" style="background: #dc3545;">Top Up SMS Credits</a>
    </div>
@endsection
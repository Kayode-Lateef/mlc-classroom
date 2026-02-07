@extends('layouts.app')

@push('styles')
<style>
    .config-section {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .provider-card {
        padding: 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 10px;
    }

    .provider-card:hover {
        border-color: #3386f7;
        background-color: #f8f9fa;
    }

    .provider-card.active {
        border-color: #3386f7;
        background-color: #e7f1ff;
    }

    .balance-warning {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .balance-ok {
        background-color: #d4edda;
        border-left: 4px solid #28a745;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .test-result {
        margin-top: 15px;
        padding: 12px;
        border-radius: 6px;
        display: none;
    }

    .test-result.success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    .test-result.error {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .provider-info {
        display: none;
    }

    .provider-info.active {
        display: block;
    }

    .voodoo-balance {
        background-color: #e8f4fd;
        border: 1px solid #b6d4fe;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 15px;
    }

    .refresh-balance-btn {
        margin-top: 5px;
    }

    .balance-loading {
        display: none;
    }
</style>
@endpush

@section('content')
    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row">
                    <div class="col-lg-8 p-r-0 title-margin-right">
                        <div class="page-header">
                            <div class="page-title">
                                <h1>SMS Configuration</h1>
                            </div>
                        </div>
                        <span>Configure SMS provider settings and manage SMS delivery</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="#">Communication</a></li>
                                    <li class="active">SMS Config</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="alert alert-success fade in alert-dismissable">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class="ti-check"></i> {{ session('success') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="alert alert-danger fade in alert-dismissable">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class="ti-alert"></i> {{ session('error') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib">
                                        <i class="ti-wallet {{ $stats['is_balance_low'] ? 'color-danger border-danger' : 'color-success border-success' }}"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Credit Balance</div>
                                        <div class="stat-digit">
                                            @if($config->provider === 'voodoo' && $stats['voodoo_balance'])
                                                {{ $stats['voodoo_balance']['credits_remaining'] ?? 0 }} credits
                                            @else
                                                £{{ number_format($stats['balance'], 2) }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-email color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">SMS Today</div>
                                        <div class="stat-digit">{{ number_format($stats['today_sms']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-stats-up color-info border-info"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">This Month</div>
                                        <div class="stat-digit">{{ number_format($stats['month_sms']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-money color-danger border-danger"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Month Cost</div>
                                        <div class="stat-digit">£{{ number_format($stats['month_cost'], 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Alert -->
                    <div class="row">
                        <div class="col-lg-12">
                            @if($stats['is_balance_low'])
                            <div class="balance-warning">
                                <div style="display: flex; align-items: center;">
                                    <i class="ti-alert" style="color: #856404; margin-right: 12px;"></i>
                                    <div>
                                        <strong style="color: #856404;">Low Balance Warning</strong>
                                        <p style="margin: 5px 0 0 0; color: #856404;">
                                            Your SMS credit balance is below the threshold. Please top up to continue sending SMS.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="balance-ok">
                                <div style="display: flex; align-items: center;">
                                    <i class="ti-check" style="color: #155724; margin-right: 12px;"></i>
                                    <div>
                                        <strong style="color: #155724;">Balance OK</strong>
                                        <p style="margin: 5px 0 0 0; color: #155724;">
                                            Your SMS system is active and ready to send messages. Success rate: {{ $stats['success_rate'] }}%
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Voodoo Balance Display (if active provider) -->
                    @if($config->provider === 'voodoo' && $stats['voodoo_balance'])
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="voodoo-balance">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong style="color: #3386f7;">Voodoo SMS Balance</strong>
                                        <p style="margin: 5px 0 0 0; color: #666;">
                                            Credits: <strong>{{ $stats['voodoo_balance']['credits_remaining'] ?? 0 }}</strong> | 
                                            Balance: <strong>£{{ number_format($stats['voodoo_balance']['balance'] ?? 0, 2) }}</strong>
                                        </p>
                                    </div>
                                    <button type="button" id="refresh-voodoo-balance" class="btn btn-sm btn-info refresh-balance-btn">
                                        <i class="ti-reload"></i> Refresh Balance
                                    </button>
                                </div>
                                <div id="balance-loading" class="balance-loading">
                                    <div class="spinner-border spinner-border-sm text-info" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <span class="ms-2">Refreshing balance...</span>
                                </div>
                                <div id="balance-result" class="test-result" style="margin-top: 10px;"></div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <form action="{{ route('superadmin.sms-config.update') }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Main Configuration -->
                            <div class="col-lg-8">
                                <!-- Provider Selection -->
                                <div class="card alert">
                                    <div class="card-header mb-2">
                                        <h4><i class="ti-settings"></i> SMS Provider</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label class="required-field">Select Provider</label>
                                            <select name="provider" id="provider" required class="form-control">
                                                <option value="voodoo" {{ old('provider', $config->provider) == 'voodoo' ? 'selected' : '' }}>Voodoo SMS</option>
                                                <option value="textlocal" {{ old('provider', $config->provider) == 'textlocal' ? 'selected' : '' }}>TextLocal</option>
                                                <option value="messagebird" {{ old('provider', $config->provider) == 'messagebird' ? 'selected' : '' }}>MessageBird</option>
                                                <option value="twilio" {{ old('provider', $config->provider) == 'twilio' ? 'selected' : '' }}>Twilio</option>
                                                <option value="vonage" {{ old('provider', $config->provider) == 'vonage' ? 'selected' : '' }}>Vonage (Nexmo)</option>
                                                <option value="bulksms" {{ old('provider', $config->provider) == 'bulksms' ? 'selected' : '' }}>BulkSMS</option>
                                            </select>
                                            @error('provider')
                                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- API Credentials -->
                                <div class="card alert">
                                    <div class="card-header mb-2">
                                        <h4><i class="ti-key"></i> API Credentials</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="ti-info-alt"></i> Credentials are encrypted when saved. Never share your API keys publicly.
                                        </div>

                                        <div class="form-group">
                                            <label class="required-field" id="api-key-label">API Key / Username</label>
                                          <input 
                                                type="text" 
                                                name="api_key" 
                                                value="{{ old('api_key') }}"
                                                placeholder="{{ $config->api_key ? '••••••••  (saved - leave blank to keep current)' : 'Enter your API key, Account SID, or username' }}"
                                                {{ $config->api_key ? '' : 'required' }}
                                                class="form-control"
                                            >
                                            <small class="form-text text-muted" id="api-key-help">
                                                Required for all providers
                                            </small>
                                            @error('api_key')
                                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label id="api-secret-label">API Secret / Password</label>
                                            <input 
                                                type="password" 
                                                name="api_secret" 
                                                id="api_secret"
                                                value="{{ old('api_secret') }}"
                                                placeholder="{{ $config->api_secret ? '••••••••  (saved - leave blank to keep current)' : 'Enter your API secret, auth token, or password' }}"
                                                class="form-control"
                                            >
                                            <small class="form-text text-muted" id="api-secret-help">
                                                Required for Twilio, Vonage, BulkSMS, and Voodoo SMS.
                                            </small>
                                            @error('api_secret')
                                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="required-field">Sender ID / From Number</label>
                                            <input 
                                                type="text" 
                                                name="sender_id" 
                                                value="{{ old('sender_id', $config->sender_id) }}"
                                                placeholder="e.g., MLC CLASS (max 11 chars) or +447123456789"
                                                required
                                                class="form-control"
                                            >
                                            <small class="form-text text-muted" id="sender-id-help">
                                                <strong>UK Requirement:</strong> Must be registered sender name (max 11 characters) or verified UK phone number.
                                            </small>
                                            @error('sender_id')
                                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Balance & Limits -->
                                <div class="card alert">
                                    <div class="card-header mb-2">
                                        <h4><i class="ti-wallet"></i> Balance & Limits</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label id="credit-balance-label">Credit Balance (£)</label>
                                                    <input 
                                                        type="number" 
                                                        name="credit_balance" 
                                                        id="credit_balance"
                                                        value="{{ old('credit_balance', $config->credit_balance) }}"
                                                        step="0.01"
                                                        min="0"
                                                        class="form-control"
                                                    >
                                                    <small class="form-text text-muted" id="credit-balance-help">
                                                        Current balance in GBP
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="required-field">Low Balance Threshold (£)</label>
                                                    <input 
                                                        type="number" 
                                                        name="low_balance_threshold" 
                                                        value="{{ old('low_balance_threshold', $config->low_balance_threshold) }}"
                                                        step="0.01"
                                                        min="0"
                                                        required
                                                        class="form-control"
                                                    >
                                                    <small class="form-text text-muted">Alert when balance is below this (Recommended: £10-50)</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="required-field">Daily SMS Limit</label>
                                                    <input 
                                                        type="number" 
                                                        name="daily_limit" 
                                                        value="{{ old('daily_limit', $config->daily_limit) }}"
                                                        min="1"
                                                        required
                                                        class="form-control"
                                                    >
                                                    <small class="form-text text-muted">Remaining today: {{ $stats['daily_remaining'] }}</small>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="required-field">Monthly SMS Limit</label>
                                                    <input 
                                                        type="number" 
                                                        name="monthly_limit" 
                                                        value="{{ old('monthly_limit', $config->monthly_limit) }}"
                                                        min="1"
                                                        required
                                                        class="form-control"
                                                    >
                                                    <small class="form-text text-muted">Remaining this month: {{ $stats['monthly_remaining'] }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            {{-- FIX: Hidden input ensures is_active=0 is sent when checkbox is unchecked --}}
                                            <input type="hidden" name="is_active" value="0">
                                            <div class="form-check">
                                                <input 
                                                    type="checkbox" 
                                                    name="is_active" 
                                                    id="is_active"
                                                    value="1"
                                                    {{ old('is_active', $config->is_active) ? 'checked' : '' }}
                                                    class="form-check-input"
                                                >
                                                <label class="form-check-label" for="is_active" style="font-weight: 500;">
                                                    <strong>Activate SMS System</strong>
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">SMS will only be sent when this is checked</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="col-lg-4">
                                <!-- Test SMS -->
                                <div class="card alert">
                                    <div class="card-header mb-2">
                                        <h4><i class="ti-mobile"></i> Test SMS</h4>
                                    </div>
                                    <div class="card-body">
                                        <p style="color: #6c757d; margin-bottom: 15px;">
                                            Send a test SMS to verify your configuration works correctly.
                                        </p>

                                        <div class="form-group">
                                            <label style="font-weight: 500;">Phone Number</label>
                                            <input 
                                                type="text" 
                                                id="test_phone"
                                                placeholder="+447123456789 or 07123456789"
                                                class="form-control"
                                            >
                                        </div>

                                        <div class="form-group">
                                            <label style="font-weight: 500;">Message</label>
                                            <textarea 
                                                id="test_message"
                                                rows="3"
                                                placeholder="Test message from MLC Classroom"
                                                class="form-control"
                                            >Test SMS from MLC Classroom. Your SMS is working!</textarea>
                                            <small class="form-text text-muted">Max 160 characters</small>
                                        </div>

                                        <button type="button" id="test-sms-btn" class="btn btn-success btn-block">
                                            <i class="ti-check"></i> Send Test SMS
                                        </button>

                                        <div id="test-result" class="test-result"></div>
                                    </div>
                                </div>

                                <!-- Provider Info -->
                                <div class="card alert">
                                    <div class="card-header mb-2">
                                        <h4><i class="ti-info-alt"></i> Provider Info</h4>
                                    </div>
                                    <div class="card-body">
                                        <!-- Voodoo SMS Info -->
                                        <div id="voodoo-info" class="provider-info {{ $config->provider == 'voodoo' ? 'active' : '' }}">
                                            <h5 style="font-weight: 600; margin-bottom: 10px;">
                                                <i class="ti-world" style="color: #3386f7;"></i> Voodoo SMS
                                            </h5>
                                            <p style="margin-bottom: 10px;">Global SMS provider with excellent African and European coverage.</p>
                                            <ul style="padding-left: 20px; margin-bottom: 15px;">
                                                <li><strong>Cost:</strong> Credits based (affordable)</li>
                                                <li><strong>Setup:</strong> HTTP API (no SDK required)</li>
                                                <li><strong>Coverage:</strong> 190+ countries</li>
                                                <li><strong>Delivery reports:</strong> Yes</li>
                                                <li><strong>Two-way SMS:</strong> Supported</li>
                                            </ul>
                                            <a href="https://www.voodoosms.com/" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="ti-new-window"></i> Sign Up
                                            </a>
                                            <a href="https://www.voodoosms.com/api" target="_blank" class="btn btn-info btn-sm">
                                                <i class="ti-book"></i> API Docs
                                            </a>
                                        </div>

                                        <!-- TextLocal Info -->
                                        <div id="textlocal-info" class="provider-info {{ $config->provider == 'textlocal' ? 'active' : '' }}">
                                            <h5 style="font-weight: 600; margin-bottom: 10px;">
                                                <i class="ti-check-box" style="color: #28a745;"></i> TextLocal (Recommended)
                                            </h5>
                                            <p style="margin-bottom: 10px;">UK-based SMS provider, perfect for schools. No SDK required!</p>
                                            <ul style="padding-left: 20px; margin-bottom: 15px;">
                                                <li><strong>Cost:</strong> £0.02-0.04 per SMS (cheapest)</li>
                                                <li><strong>Setup:</strong> 5 minutes (HTTP API)</li>
                                                <li><strong>Coverage:</strong> Excellent UK delivery</li>
                                                <li><strong>No SDK required</strong></li>
                                            </ul>
                                            <a href="https://www.textlocal.com/" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="ti-new-window"></i> Sign Up
                                            </a>
                                            <a href="https://api.txtlocal.com/docs/" target="_blank" class="btn btn-info btn-sm">
                                                <i class="ti-book"></i> Docs
                                            </a>
                                        </div>

                                        <!-- MessageBird Info -->
                                        <div id="messagebird-info" class="provider-info {{ $config->provider == 'messagebird' ? 'active' : '' }}">
                                            <h5 style="font-weight: 600; margin-bottom: 10px;">MessageBird</h5>
                                            <p style="margin-bottom: 10px;">European SMS provider with global reach.</p>
                                            <ul style="padding-left: 20px; margin-bottom: 15px;">
                                                <li><strong>Cost:</strong> £0.03-0.05 per SMS</li>
                                                <li><strong>Setup:</strong> Requires SDK</li>
                                                <li><strong>Coverage:</strong> Global + UK</li>
                                                <li><strong>Delivery rate:</strong> 99%+</li>
                                            </ul>
                                            <a href="https://messagebird.com/" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="ti-new-window"></i> Sign Up
                                            </a>
                                            <a href="https://developers.messagebird.com/" target="_blank" class="btn btn-info btn-sm">
                                                <i class="ti-book"></i> Docs
                                            </a>
                                        </div>

                                        <!-- Twilio Info -->
                                        <div id="twilio-info" class="provider-info {{ $config->provider == 'twilio' ? 'active' : '' }}">
                                            <h5 style="font-weight: 600; margin-bottom: 10px;">Twilio</h5>
                                            <p style="margin-bottom: 10px;">Industry-leading SMS provider with excellent UK coverage.</p>
                                            <ul style="padding-left: 20px; margin-bottom: 15px;">
                                                <li><strong>Cost:</strong> £0.04-0.07 per SMS</li>
                                                <li><strong>Setup:</strong> Requires SDK</li>
                                                <li><strong>Delivery rate:</strong> 99.95%</li>
                                                <li><strong>Support:</strong> 24/7</li>
                                            </ul>
                                            <a href="https://www.twilio.com/" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="ti-new-window"></i> Sign Up
                                            </a>
                                            <a href="https://www.twilio.com/docs/sms" target="_blank" class="btn btn-info btn-sm">
                                                <i class="ti-book"></i> Docs
                                            </a>
                                        </div>

                                        <!-- Vonage Info -->
                                        <div id="vonage-info" class="provider-info {{ $config->provider == 'vonage' ? 'active' : '' }}">
                                            <h5 style="font-weight: 600; margin-bottom: 10px;">Vonage (Nexmo)</h5>
                                            <p style="margin-bottom: 10px;">Reliable SMS with good UK pricing.</p>
                                            <ul style="padding-left: 20px; margin-bottom: 15px;">
                                                <li><strong>Cost:</strong> £0.04-0.06 per SMS</li>
                                                <li><strong>Setup:</strong> Requires SDK</li>
                                                <li><strong>Coverage:</strong> Global + UK</li>
                                                <li><strong>Delivery reports:</strong> Real-time</li>
                                            </ul>
                                            <a href="https://www.vonage.com/" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="ti-new-window"></i> Sign Up
                                            </a>
                                            <a href="https://developer.vonage.com/" target="_blank" class="btn btn-info btn-sm">
                                                <i class="ti-book"></i> Docs
                                            </a>
                                        </div>

                                        <!-- BulkSMS Info -->
                                        <div id="bulksms-info" class="provider-info {{ $config->provider == 'bulksms' ? 'active' : '' }}">
                                            <h5 style="font-weight: 600; margin-bottom: 10px;">BulkSMS</h5>
                                            <p style="margin-bottom: 10px;">Volume SMS provider with competitive pricing.</p>
                                            <ul style="padding-left: 20px; margin-bottom: 15px;">
                                                <li><strong>Cost:</strong> £0.03-0.05 per SMS (volume discounts)</li>
                                                <li><strong>Setup:</strong> HTTP API (no SDK)</li>
                                                <li><strong>Coverage:</strong> 200+ countries</li>
                                                <li><strong>Best for:</strong> High volume</li>
                                            </ul>
                                            <a href="https://www.bulksms.com/" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="ti-new-window"></i> Sign Up
                                            </a>
                                            <a href="https://www.bulksms.com/developer/" target="_blank" class="btn btn-info btn-sm">
                                                <i class="ti-book"></i> Docs
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card alert">
                                    <div class="card-body">
                                        <div style="display: flex; justify-content: flex-end; align-items: center;">
                                            <a href="{{ route('superadmin.sms-logs.index') }}" class="btn btn-secondary" style="margin-right: 10px;">
                                                <i class="ti-list"></i> View SMS Logs
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti-check"></i> Save Configuration
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="footer">
                                    <p>MLC Classroom - SMS Configuration</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Provider selection - show/hide appropriate info and update field requirements
    $('#provider').on('change', function() {
        const provider = $(this).val();
        
        // Hide all provider info
        $('.provider-info').removeClass('active');
        
        // Show selected provider info
        $(`#${provider}-info`).addClass('active');
        
        // Update API Secret requirement based on provider
        const requiresSecret = ['twilio', 'vonage', 'bulksms', 'voodoo'].includes(provider);
        const $apiSecret = $('#api_secret');
        const $apiSecretLabel = $('#api-secret-label');
        const $apiSecretHelp = $('#api-secret-help');
        
        if (requiresSecret) {
            $apiSecret.prop('required', true);
            $apiSecretLabel.html('API Secret / Password <span style="color: #dc3545;">*</span>');
            $apiSecretHelp.text('Required for ' + (provider === 'voodoo' ? 'Voodoo SMS' : provider.charAt(0).toUpperCase() + provider.slice(1)));
        } else {
            $apiSecret.prop('required', false);
            $apiSecretLabel.text('API Secret / Password');
            $apiSecretHelp.text('Not required for ' + (provider === 'textlocal' ? 'TextLocal' : 'MessageBird'));
        }
        
        // Update API Key label based on provider
        const $apiKeyLabel = $('#api-key-label');
        const $apiKeyHelp = $('#api-key-help');
        
        if (provider === 'twilio') {
            $apiKeyLabel.html('Account SID <span style="color: #dc3545;">*</span>');
            $apiKeyHelp.text('Your Twilio Account SID (find it in Twilio Console)');
        } else if (provider === 'voodoo') {
            $apiKeyLabel.html('Username <span style="color: #dc3545;">*</span>');
            $apiKeyHelp.text('Your Voodoo SMS username (email or username)');
        } else if (provider === 'bulksms') {
            $apiKeyLabel.html('Username <span style="color: #dc3545;">*</span>');
            $apiKeyHelp.text('Your BulkSMS username');
        } else {
            $apiKeyLabel.html('API Key <span style="color: #dc3545;">*</span>');
            $apiKeyHelp.text('Your API key from ' + (provider === 'textlocal' ? 'TextLocal' : provider.charAt(0).toUpperCase() + provider.slice(1)));
        }
        
        // Update Sender ID placeholder and help
        const $senderId = $('input[name="sender_id"]');
        const $senderIdHelp = $('#sender-id-help');
        
        if (provider === 'twilio') {
            $senderId.attr('placeholder', '+447123456789 (verified Twilio number)');
            $senderIdHelp.html('<strong>Twilio:</strong> Must be a verified phone number in your Twilio account (E.164 format)');
        } else if (provider === 'voodoo') {
            $senderId.attr('placeholder', 'MLC CLASS or phone number');
            $senderIdHelp.html('<strong>Voodoo SMS:</strong> Can be a registered sender name or phone number');
        } else {
            $senderId.attr('placeholder', 'e.g., MLC CLASS (max 11 chars) or +447123456789');
            $senderIdHelp.html('<strong>UK Requirement:</strong> Must be registered sender name (max 11 characters) or verified UK phone number.');
        }
        
        // Handle credit balance field for Voodoo SMS
        const $creditBalance = $('#credit_balance');
        const $creditBalanceLabel = $('#credit-balance-label');
        const $creditBalanceHelp = $('#credit-balance-help');
        
        if (provider === 'voodoo') {
            // Disable and clear credit balance field for Voodoo
            $creditBalance.val('0');
            $creditBalance.prop('disabled', true);
            $creditBalanceLabel.text('Credit Balance (Disabled for Voodoo)');
            $creditBalanceHelp.html('Voodoo SMS uses credits, not monetary balance. Manage credits via Voodoo dashboard.');
        } else {
            // Enable credit balance field for other providers
            $creditBalance.prop('disabled', false);
            $creditBalanceLabel.text('Credit Balance (£)');
            $creditBalanceHelp.text('Current balance in GBP');
        }
    });
    
    // Trigger on page load
    $('#provider').trigger('change');

    // Test SMS
    $('#test-sms-btn').on('click', function() {
        const phone = $('#test_phone').val();
        const message = $('#test_message').val();
        const btn = $(this);
        const resultDiv = $('#test-result');

        if (!phone || !message) {
            resultDiv.removeClass('success').addClass('error')
                .html('<i class="ti-close"></i> Please enter phone number and message.')
                .show();
            return;
        }

        btn.prop('disabled', true).html('<i class="ti-reload"></i> Sending...');
        resultDiv.hide();

        $.ajax({
            url: '{{ route('superadmin.sms-config.test') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                test_phone: phone,
                test_message: message
            },
            success: function(response) {
                let details = '';
                if (response.details) {
                    const provider = response.details.provider || 'N/A';
                    const cost = response.details.cost || '0.00';
                    const balance = response.details.remaining_balance || '0.00';
                    const credits = response.details.credits_remaining;
                    
                    details = `<br><small>Provider: ${provider} | Cost: £${cost}`;
                    
                    if (credits !== undefined) {
                        details += ` | Credits: ${credits}`;
                    } else {
                        details += ` | Balance: £${balance}`;
                    }
                    
                    details += '</small>';
                }
                resultDiv.removeClass('error').addClass('success')
                    .html('<i class="ti-check"></i> ' + response.message + details)
                    .show();
                btn.prop('disabled', false).html('<i class="ti-check"></i> Send Test SMS');
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Test SMS failed. Please check your configuration.';
                resultDiv.removeClass('success').addClass('error')
                    .html('<i class="ti-close"></i> ' + message)
                    .show();
                btn.prop('disabled', false).html('<i class="ti-check"></i> Send Test SMS');
            }
        });
    });

    // Refresh Voodoo Balance
    $('#refresh-voodoo-balance').on('click', function() {
        const btn = $(this);
        const loadingDiv = $('#balance-loading');
        const resultDiv = $('#balance-result');
        
        btn.prop('disabled', true);
        loadingDiv.show();
        resultDiv.hide();
        
        $.ajax({
            url: '{{ route('superadmin.sms-config.refresh-voodoo-balance') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                loadingDiv.hide();
                if (response.success) {
                    resultDiv.removeClass('error').addClass('success')
                        .html('<i class="ti-check"></i> Balance refreshed: ' + response.formatted)
                        .show();
                    
                    // Update the balance display
                    const balanceText = `Credits: <strong>${response.credits_remaining}</strong> | Balance: <strong>£${parseFloat(response.monetary_balance).toFixed(2)}</strong>`;
                    $('.voodoo-balance p').html(balanceText);
                } else {
                    resultDiv.removeClass('success').addClass('error')
                        .html('<i class="ti-close"></i> ' + (response.message || 'Failed to refresh balance'))
                        .show();
                }
                btn.prop('disabled', false);
            },
            error: function(xhr) {
                loadingDiv.hide();
                const message = xhr.responseJSON?.message || 'Failed to refresh balance. Please try again.';
                resultDiv.removeClass('success').addClass('error')
                    .html('<i class="ti-close"></i> ' + message)
                    .show();
                btn.prop('disabled', false);
            }
        });
    });
});
</script>
@endpush
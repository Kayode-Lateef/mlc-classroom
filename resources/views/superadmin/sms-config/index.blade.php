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
        border-color: #007bff;
        background-color: #f8f9fa;
    }

    .provider-card.active {
        border-color: #007bff;
        background-color: #e7f3ff;
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
                                        <div class="stat-digit">£{{ number_format($stats['balance'], 2) }}</div>
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
                                    <div class="stat-icon dib"><i class="ti-stats-up color-purple border-purple"></i></div>
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
                                            Your SMS system is active and ready to send messages.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

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
                                                <option value="twilio" {{ old('provider', $config->provider) == 'twilio' ? 'selected' : '' }}>Twilio</option>
                                                <option value="vonage" {{ old('provider', $config->provider) == 'vonage' ? 'selected' : '' }}>Vonage (Nexmo)</option>
                                                <option value="messagebird" {{ old('provider', $config->provider) == 'messagebird' ? 'selected' : '' }}>MessageBird</option>
                                                <option value="textlocal" {{ old('provider', $config->provider) == 'textlocal' ? 'selected' : '' }}>TextLocal</option>
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
                                            <label class="required-field">API Key / Account SID</label>
                                            <input 
                                                type="text" 
                                                name="api_key" 
                                                value="{{ old('api_key') }}"
                                                placeholder="Enter your API key or Account SID"
                                                required
                                                class="form-control"
                                            >
                                            @error('api_key')
                                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="required-field">API Secret / Auth Token</label>
                                            <input 
                                                type="password" 
                                                name="api_secret" 
                                                value="{{ old('api_secret') }}"
                                                placeholder="Enter your API secret or auth token"
                                                required
                                                class="form-control"
                                            >
                                            @error('api_secret')
                                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>Sender ID / Phone Number</label>
                                            <input 
                                                type="text" 
                                                name="sender_id" 
                                                value="{{ old('sender_id', $config->sender_id) }}"
                                                placeholder="e.g., +447123456789 or YourName"
                                                class="form-control"
                                            >
                                            <small class="form-text text-muted">UK phone number or approved sender name</small>
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
                                                    <label>Credit Balance (£)</label>
                                                    <input 
                                                        type="number" 
                                                        name="credit_balance" 
                                                        value="{{ old('credit_balance', $config->credit_balance) }}"
                                                        step="0.01"
                                                        min="0"
                                                        class="form-control"
                                                    >
                                                    <small class="form-text text-muted">Current balance in GBP</small>
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
                                                    <small class="form-text text-muted">Alert when balance is below this</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Daily SMS Limit</label>
                                                    <input 
                                                        type="number" 
                                                        name="daily_limit" 
                                                        value="{{ old('daily_limit', $config->daily_limit) }}"
                                                        min="1"
                                                        class="form-control"
                                                    >
                                                    <small class="form-text text-muted">Remaining today: {{ $stats['daily_remaining'] }}</small>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Monthly SMS Limit</label>
                                                    <input 
                                                        type="number" 
                                                        name="monthly_limit" 
                                                        value="{{ old('monthly_limit', $config->monthly_limit) }}"
                                                        min="1"
                                                        class="form-control"
                                                    >
                                                    <small class="form-text text-muted">Remaining this month: {{ $stats['monthly_remaining'] }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
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
                                                placeholder="+447123456789"
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
                                            ></textarea>
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
                                        <div id="twilio-info" class="provider-info">
                                            <h5 style="font-weight: 600; margin-bottom: 10px;">Twilio</h5>
                                            <p style="margin-bottom: 10px;">Industry-leading SMS provider with excellent UK coverage.</p>
                                            <ul style="padding-left: 20px;">
                                                <li>Cost: ~£0.04 per SMS</li>
                                                <li>Delivery rate: 99.95%</li>
                                                <li>UK verified sender IDs supported</li>
                                            </ul>
                                            <a href="https://www.twilio.com/docs" target="_blank" class="btn btn-primary btn-sm mt-2">
                                                <i class="ti-new-window"></i> Documentation
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
    // Test SMS
    $('#test-sms-btn').on('click', function() {
        const phone = $('#test_phone').val();
        const message = $('#test_message').val();
        const btn = $(this);
        const resultDiv = $('#test-result');

        if (!phone || !message) {
            resultDiv.removeClass('success').addClass('error').text('Please enter phone number and message.').show();
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
                resultDiv.removeClass('error').addClass('success').html('<i class="ti-check"></i> ' + response.message).show();
                btn.prop('disabled', false).html('<i class="ti-check"></i> Send Test SMS');
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Test SMS failed. Please check your configuration.';
                resultDiv.removeClass('success').addClass('error').html('<i class="ti-close"></i> ' + message).show();
                btn.prop('disabled', false).html('<i class="ti-check"></i> Send Test SMS');
            }
        });
    });
});
</script>
@endpush
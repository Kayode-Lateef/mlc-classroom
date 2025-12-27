@extends('layouts.app')

@push('styles')
<style>
    .sms-header {
        background-color: #fff;
        padding: 25px;
        border-radius: 8px 8px 0 0;
    }

    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        flex: 0 0 180px;
        font-weight: 500;
        color: #6c757d;
    }

    .info-value {
        flex: 1;
        color: #212529;
    }

    .message-box {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
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
                                <h1>SMS Log Details</h1>
                            </div>
                        </div>
                        <span>View detailed SMS delivery information</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('superadmin.sms-logs.index') }}">SMS Logs</a></li>
                                    <li class="active">Details</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <!-- SMS Details -->
                        <div class="card alert">
                            @php
                                $statusColors = [
                                    'pending' => 'badge-secondary',
                                    'sent' => 'badge-primary',
                                    'delivered' => 'badge-success',
                                    'failed' => 'badge-danger',
                                ];
                                $statusClass = $statusColors[$smsLog->status] ?? 'badge-secondary';
                            @endphp

                            <div class="sms-header">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <h2 style="margin-bottom: 10px;">
                                            SMS to {{ $smsLog->user ? $smsLog->user->name : 'Unknown' }}
                                        </h2>
                                        <span class="badge {{ $statusClass }}" style=font-size: 0.875rem; padding: 6px 12px;">
                                            {{ ucfirst($smsLog->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <h4 style="margin-bottom: 15px;"><i class="ti-file"></i> Message Content</h4>
                                <div class="message-box">
                                    <p style="margin: 0; line-height: 1.6; white-space: pre-line;">{{ $smsLog->message_content }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Information -->
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-info-alt"></i> Delivery Information</h4>
                            </div>
                            <div class="card-body">
                                <div class="info-row">
                                    <div class="info-label">Recipient</div>
                                    <div class="info-value">
                                        <strong>{{ $smsLog->user ? $smsLog->user->name : 'Unknown' }}</strong>
                                        @if($smsLog->user)
                                        <br><small>({{ ucfirst($smsLog->user->role) }})</small>
                                        @endif
                                    </div>
                                </div>

                                <div class="info-row">
                                    <div class="info-label">Phone Number</div>
                                    <div class="info-value">{{ $smsLog->phone_number }}</div>
                                </div>

                                <div class="info-row">
                                    <div class="info-label">Message Type</div>
                                    <div class="info-value">
                                        @php
                                            $typeColors = [
                                                'absence' => 'badge-danger',
                                                'homework' => 'badge-primary',
                                                'progress' => 'badge-success',
                                                'emergency' => 'badge-warning',
                                                'general' => 'badge-secondary'
                                            ];
                                            $typeClass = $typeColors[$smsLog->message_type] ?? 'badge-secondary';
                                        @endphp
                                        <span class="badge {{ $typeClass }}">{{ ucfirst($smsLog->message_type) }}</span>
                                    </div>
                                </div>

                                <div class="info-row">
                                    <div class="info-label">Provider</div>
                                    <div class="info-value">{{ ucfirst($smsLog->provider) }}</div>
                                </div>

                                @if($smsLog->provider_message_id)
                                <div class="info-row">
                                    <div class="info-label">Provider Message ID</div>
                                    <div class="info-value"><code>{{ $smsLog->provider_message_id }}</code></div>
                                </div>
                                @endif

                                @if($smsLog->sent_at)
                                <div class="info-row">
                                    <div class="info-label">Sent At</div>
                                    <div class="info-value">{{ $smsLog->sent_at->format('d M Y, H:i:s') }}</div>
                                </div>
                                @endif

                                @if($smsLog->delivered_at)
                                <div class="info-row">
                                    <div class="info-label">Delivered At</div>
                                    <div class="info-value">{{ $smsLog->delivered_at->format('d M Y, H:i:s') }}</div>
                                </div>
                                @endif

                                @if($smsLog->cost)
                                <div class="info-row">
                                    <div class="info-label">Cost</div>
                                    <div class="info-value"><strong style="color: #007bff;">£{{ number_format($smsLog->cost, 4) }}</strong></div>
                                </div>
                                @endif

                                @if($smsLog->retry_count > 0)
                                <div class="info-row">
                                    <div class="info-label">Retry Count</div>
                                    <div class="info-value">{{ $smsLog->retry_count }}</div>
                                </div>
                                @endif

                                @if($smsLog->failure_reason)
                                <div class="info-row">
                                    <div class="info-label">Failure Reason</div>
                                    <div class="info-value">
                                        <div class="alert alert-danger" style="margin: 0; padding: 10px;">
                                            {{ $smsLog->failure_reason }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Actions -->
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-settings"></i> Actions</h4>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('superadmin.sms-logs.index') }}" class="btn btn-secondary btn-block mb-2">
                                    <i class="ti-arrow-left"></i> Back to Logs
                                </a>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="card alert">
                            <div class="card-header">
                                <h4><i class="ti-time"></i> Timeline</h4>
                            </div>
                            <div class="card-body">
                                <div style="margin-bottom: 15px;">
                                    <p style="margin: 0; font-size: 0.875rem; color: #6c757d;">Created</p>
                                    <p style="margin: 0; font-size: 0.9375rem; font-weight: 600;">{{ $smsLog->created_at->format('d M Y, H:i') }}</p>
                                    <p style="margin: 0; font-size: 0.75rem; color: #6c757d;">{{ $smsLog->created_at->diffForHumans() }}</p>
                                </div>

                                @if($smsLog->sent_at)
                                <div style="margin-bottom: 15px;">
                                    <p style="margin: 0; font-size: 0.875rem; color: #6c757d;">Sent</p>
                                    <p style="margin: 0; font-size: 0.9375rem; font-weight: 600;">{{ $smsLog->sent_at->format('d M Y, H:i') }}</p>
                                    <p style="margin: 0; font-size: 0.75rem; color: #6c757d;">{{ $smsLog->sent_at->diffForHumans() }}</p>
                                </div>
                                @endif

                                @if($smsLog->delivered_at)
                                <div>
                                    <p style="margin: 0; font-size: 0.875rem; color: #6c757d;">Delivered</p>
                                    <p style="margin: 0; font-size: 0.9375rem; font-weight: 600;">{{ $smsLog->delivered_at->format('d M Y, H:i') }}</p>
                                    <p style="margin: 0; font-size: 0.75rem; color: #6c757d;">{{ $smsLog->delivered_at->diffForHumans() }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer">
                            <p>MLC Classroom - SMS Log Details</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
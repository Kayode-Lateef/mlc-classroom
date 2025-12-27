@extends('layouts.app')

@push('styles')
<style>
    .sms-log-item {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .sms-log-item:hover {
        background-color: #f8f9fa;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .filter-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
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
                                <h1>SMS Logs</h1>
                            </div>
                        </div>
                        <span>View all SMS sent, delivery status, and costs</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="#">Communication</a></li>
                                    <li class="active">SMS Logs</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-email color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total SMS</div>
                                        <div class="stat-digit">{{ number_format($stats['total_sms']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-check color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Delivered</div>
                                        <div class="stat-digit">{{ number_format($stats['delivered']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-close color-danger border-danger"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Failed</div>
                                        <div class="stat-digit">{{ number_format($stats['failed']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-money color-purple border-purple"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Month Cost</div>
                                        <div class="stat-digit">£{{ number_format($stats['month_cost'], 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="filter-card">
                                <form method="GET" action="{{ route('superadmin.sms-logs.index') }}">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-size: 0.875rem; font-weight: 500;">Date From</label>
                                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-size: 0.875rem; font-weight: 500;">Date To</label>
                                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-size: 0.875rem; font-weight: 500;">Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="">All Status</option>
                                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-size: 0.875rem; font-weight: 500;">Type</label>
                                                <select name="message_type" class="form-control">
                                                    <option value="">All Types</option>
                                                    <option value="absence" {{ request('message_type') == 'absence' ? 'selected' : '' }}>Absence</option>
                                                    <option value="homework" {{ request('message_type') == 'homework' ? 'selected' : '' }}>Homework</option>
                                                    <option value="progress" {{ request('message_type') == 'progress' ? 'selected' : '' }}>Progress</option>
                                                    <option value="emergency" {{ request('message_type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                                    <option value="general" {{ request('message_type') == 'general' ? 'selected' : '' }}>General</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-size: 0.875rem; font-weight: 500;">Provider</label>
                                                <select name="provider" class="form-control">
                                                    <option value="">All Providers</option>
                                                    @foreach($providers as $provider)
                                                    <option value="{{ $provider }}" {{ request('provider') == $provider ? 'selected' : '' }}>
                                                        {{ ucfirst($provider) }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-size: 0.875rem; font-weight: 500;">Search</label>
                                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Phone..." class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <a href="{{ route('superadmin.sms-logs.index') }}" class="btn btn-secondary btn-sm">
                                            <i class="ti-reload"></i> Clear
                                        </a>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="ti-filter"></i> Apply
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- SMS Logs List -->
                    @if($logs->count() > 0)
                    <div class="row">
                        <div class="col-lg-12">
                            @foreach($logs as $log)
                            <div class="sms-log-item">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                            <h5 style="margin: 0; font-weight: 600;">
                                                {{ $log->user ? $log->user->name : 'Unknown' }}
                                            </h5>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'badge-secondary',
                                                    'sent' => 'badge-primary',
                                                    'delivered' => 'badge-success',
                                                    'failed' => 'badge-danger',
                                                    'undelivered' => 'badge-warning'
                                                ];
                                                $statusClass = $statusColors[$log->status] ?? 'badge-secondary';

                                                $typeColors = [
                                                    'absence' => 'badge-danger',
                                                    'homework' => 'badge-primary',
                                                    'progress' => 'badge-success',
                                                    'emergency' => 'badge-warning',
                                                    'general' => 'badge-secondary'
                                                ];
                                                $typeClass = $typeColors[$log->message_type] ?? 'badge-secondary';
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ ucfirst($log->status) }}</span>
                                            <span class="badge {{ $typeClass }}">{{ ucfirst($log->message_type) }}</span>
                                        </div>

                                        <p style="margin: 0 0 8px 0; color: #6c757d;">
                                            <i class="ti-mobile"></i> {{ $log->phone_number }}
                                            <span style="margin-left: 15px;"><i class="ti-package"></i> {{ ucfirst($log->provider) }}</span>
                                            @if($log->sent_at)
                                            <span style="margin-left: 15px;"><i class="ti-time"></i> {{ $log->sent_at->format('d M Y, H:i') }}</span>
                                            @endif
                                        </p>

                                        <p style="margin: 0; color: #495057;">
                                            {{ Str::limit($log->message_content, 100) }}
                                        </p>
                                    </div>

                                    <div style="text-align: right;">
                                        @if($log->cost)
                                        <p style="margin: 0 0 10px 0; font-weight: 600; color: #007bff;">£{{ number_format($log->cost, 4) }}</p>
                                        @endif
                                        <a href="{{ route('superadmin.sms-logs.show', $log) }}" class="btn btn-primary btn-sm">
                                            <i class="ti-eye"></i> View
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($logs->hasPages())
                    <div class="row">
                        <div class="col-lg-12">
                            {{ $logs->appends(request()->query())->links() }}
                        </div>
                    </div>
                    @endif
                    @else
                    <!-- Empty State -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert" style="text-align: center; padding: 60px 20px;">
                                <i class="ti-email" style="font-size: 4rem; color: #cbd5e0; margin-bottom: 20px;"></i>
                                <h3 style="margin-bottom: 10px;">No SMS Logs Found</h3>
                                <p class="text-muted">No SMS messages match your filters.</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - SMS Logs</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@extends('layouts.app')

@section('title', 'Activity Logs')

@push('styles')
    <style>
        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }


        .top-actions-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .action-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }

        .action-item i {
            color: #007bff;
            font-size: 2rem;
        }

        .filter-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .log-table {
            width: 100%;
        }

        .log-table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            border-bottom: 2px solid #e9ecef;
        }

        .log-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .log-table tr:hover {
            background-color: #f8f9fa;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-avatar-initial {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background-color: #e7f3ff;
            color: #007bff;
            font-size: 0.75rem;
        }

        .action-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .badge-created {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-updated {
            background-color: #cce5ff;
            color: #004085;
        }

        .badge-deleted {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-default {
            background-color: #e9ecef;
            color: #495057;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 5rem;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .time-display {
            font-size: 1rem;
        }

        .time-relative {
            font-size: 0.9rem;
            color: #6c757d;
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
                                <h1>Activity Logs</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Activity Logs</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-2 col-md-4">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-clipboard color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Logs</div>
                                        <div class="stat-digit">{{ number_format($stats['total']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-calendar color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Today</div>
                                        <div class="stat-digit">{{ number_format($stats['today']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-time color-pink border-pink"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">This Week</div>
                                        <div class="stat-digit">{{ number_format($stats['this_week']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-stats-up color-info border-info"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">This Month</div>
                                        <div class="stat-digit">{{ number_format($stats['this_month']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-user color-warning border-warning"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Active Users</div>
                                        <div class="stat-digit">{{ number_format($stats['unique_users']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                      
                    </div>
               
                    <!-- Top Actions -->
                    @if(!empty($stats['actions_breakdown']))
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="top-actions-card">
                                <h3 style="font-size: 1.6rem; font-weight: 600; margin-bottom: 15px;">
                                    <i class="ti-bar-chart"></i> Top Actions
                                </h3>
                                <div class="row">
                                    @foreach($stats['actions_breakdown'] as $action => $count)
                                    <div class="col-lg col-md-4 col-sm-6 mb-3">
                                        <div class="action-item">
                                            <div style="flex: 1;">
                                                <div style="font-size: 1rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    {{ ucwords(str_replace('_', ' ', $action)) }}
                                                </div>
                                                <div style="font-size: 1.2rem; font-weight: 600;">{{ number_format($count) }}</div>
                                            </div>
                                            <i class="ti-trending-up"></i>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Filters -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="filter-card">
                                <div class="filter-header">
                                    <h3 style="font-size: 1.6rem; font-weight: 600; margin: 0;">
                                        <i class="ti-filter"></i> Filters
                                    </h3>
                                    <a href="{{ route('superadmin.activity-logs.index') }}" class="btn btn-sm btn-secondary">
                                        <i class="ti-reload"></i> Clear All
                                    </a>
                                </div>

                                <form method="GET" action="{{ route('superadmin.activity-logs.index') }}">
                                    <div class="row">
                                        <!-- Search -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="form-group">
                                                <label>Search</label>
                                                <input 
                                                    type="text" 
                                                    name="search" 
                                                    value="{{ request('search') }}" 
                                                    placeholder="Description or action..."
                                                    class="form-control"
                                                >
                                            </div>
                                        </div>

                                        <!-- User Filter -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="form-group">
                                                <label>User</label>
                                                <select name="user_id" class="form-control">
                                                    <option value="">All Users</option>
                                                    @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                        {{ $user->name }} ({{ ucfirst($user->role) }})
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Action Filter -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="form-group">
                                                <label>Action</label>
                                                <select name="action" class="form-control">
                                                    <option value="">All Actions</option>
                                                    @foreach($actionTypes as $actionType)
                                                    <option value="{{ $actionType }}" {{ request('action') == $actionType ? 'selected' : '' }}>
                                                        {{ ucwords(str_replace('_', ' ', $actionType)) }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Model Type Filter -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="form-group">
                                                <label>Model Type</label>
                                                <select name="model_type" class="form-control">
                                                    <option value="">All Models</option>
                                                    @foreach($modelTypes as $modelType)
                                                    <option value="{{ $modelType }}" {{ request('model_type') == $modelType ? 'selected' : '' }}>
                                                        {{ class_basename($modelType) }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Date From -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="form-group">
                                                <label>Date From</label>
                                                <input 
                                                    type="date" 
                                                    name="date_from" 
                                                    value="{{ request('date_from') }}"
                                                    class="form-control"
                                                >
                                            </div>
                                        </div>

                                        <!-- Date To -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="form-group">
                                                <label>Date To</label>
                                                <input 
                                                    type="date" 
                                                    name="date_to" 
                                                    value="{{ request('date_to') }}"
                                                    class="form-control"
                                                >
                                            </div>
                                        </div>

                                        <!-- Sort By -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="form-group">
                                                <label>Sort By</label>
                                                <select name="sort_by" class="form-control">
                                                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date</option>
                                                    <option value="action" {{ request('sort_by') == 'action' ? 'selected' : '' }}>Action</option>
                                                    <option value="user_id" {{ request('sort_by') == 'user_id' ? 'selected' : '' }}>User</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Sort Order -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="form-group">
                                                <label>Order</label>
                                                <select name="sort_order" class="form-control">
                                                    <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Newest First</option>
                                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Oldest First</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Filter Buttons -->
                                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti-check"></i> Apply Filters
                                        </button>
                                        <a href="{{ route('superadmin.activity-logs.index') }}" class="btn btn-secondary">
                                            <i class="ti-reload"></i> Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Logs Table -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card alert">
                                @if($logs->count() > 0)
                                    <div class="table-responsive">
                                        <table class="log-table">
                                            <thead>
                                                <tr>
                                                    <th>Time</th>
                                                    <th>User</th>
                                                    <th>Action</th>
                                                    <th>Description</th>
                                                    <th>Model</th>
                                                    <th>IP Address</th>
                                                    <th style="text-align: right;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($logs as $log)
                                                <tr>
                                                    <td style="white-space: nowrap;">
                                                        <div class="time-display">{{ $log->created_at->format('d M Y') }}</div>
                                                        <div style="color: #6c757d;">{{ $log->created_at->format('H:i:s') }}</div>
                                                        <div class="time-relative">{{ $log->created_at->diffForHumans() }}</div>
                                                    </td>
                                                    <td style="white-space: nowrap;">
                                                        @if($log->user)
                                                        <div style="display: flex; align-items: center;">
                                                            @if($log->user->profile_photo)
                                                                <img src="{{ asset('storage/' . $log->user->profile_photo) }}" alt="{{ $log->user->name }}" class="user-avatar" style="margin-right: 8px;">
                                                            @else
                                                                <div class="user-avatar-initial" style="margin-right: 8px;">
                                                                    {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <div style="font-weight: 500;">{{ $log->user->name }}</div>
                                                                <div style="color: #6c757d;">{{ ucfirst($log->user->role) }}</div>
                                                            </div>
                                                        </div>
                                                        @else
                                                        <span style="color: #6c757d; font-style: italic;">System</span>
                                                        @endif
                                                    </td>
                                                    <td style="white-space: nowrap;">
                                                        <span class="action-badge 
                                                            {{ str_contains($log->action, 'created') ? 'badge-created' : 
                                                               (str_contains($log->action, 'updated') ? 'badge-updated' : 
                                                               (str_contains($log->action, 'deleted') ? 'badge-deleted' : 'badge-default')) }}">
                                                            {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div style="max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 1rem;">
                                                            {{ $log->description }}
                                                        </div>
                                                    </td>
                                                    <td style="white-space: nowrap; color: #6c757d;">
                                                        @if($log->model_type)
                                                        <div style="display: flex; align-items: center;">
                                                            <i class="ti-folder" style="margin-right: 5px; color: #6c757d;"></i>
                                                            <span>{{ class_basename($log->model_type) }} #{{ $log->model_id }}</span>
                                                        </div>
                                                        @else
                                                        <span style="color: #cbd5e0;">-</span>
                                                        @endif
                                                    </td>
                                                    <td style="white-space: nowrap;">
                                                        <div style="display: flex; align-items: center; color: #6c757d;">
                                                            <i class="ti-world" style="margin-right: 5px;"></i>
                                                            {{ $log->ip_address ?? 'N/A' }}
                                                        </div>
                                                    </td>
                                                    <td style="text-align: right; white-space: nowrap;">
                                                        <a href="{{ route('superadmin.activity-logs.show', $log) }}" class="btn btn-sm btn-primary">
                                                            <i class="ti-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <div style="padding: 20px; border-top: 1px solid #e9ecef;">
                                        {{ $logs->appends(request()->query())->links() }}
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <i class="ti-clipboard"></i>
                                        <h3 class="mb-3">No Activity Logs Found</h3>
                                        <p class="text-muted">Try adjusting your filters or date range.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Activity Logs</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Auto-refresh indicator (optional)
            // Could add live updates here if needed
        });
    </script>
@endpush
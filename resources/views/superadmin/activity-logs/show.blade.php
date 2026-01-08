@extends('layouts.app')

@section('title', 'Activity Log Details')

@push('styles')
    <style>
        .detail-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .action-badge-large {
            padding: 8px 20px;
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

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .info-item {
            margin-bottom: 0;
        }

        .info-label {
            color: #6c757d;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: 600;
            color: #212529;
        }

        .user-avatar-large {
            width: 96px;
            height: 96px;
            border-radius: 8px;
            object-fit: cover;
        }

        .user-avatar-initial-large {
            width: 96px;
            height: 96px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e7f3ff;
            color: #007bff;
            font-size: 2.5rem;
            font-weight: bold;
        }

        .role-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
        }

        .role-superadmin {
            background-color: #f8d7da;
            color: #721c24;
        }

        .role-admin {
            background-color: #cce5ff;
            color: #004085;
        }

        .role-teacher {
            background-color: #d4edda;
            color: #155724;
        }

        .role-parent {
            background-color: #e2d9f3;
            color: #5a2a82;
        }

        .technical-detail-box {
            display: flex;
            align-items: center;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }

        .technical-detail-box i {
            color: #6c757d;
            margin-right: 10px;
        }

        .user-agent-box {
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 6px;
            font-family: monospace;
            color: #495057;
            word-break: break-all;
        }

        .related-log-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .related-log-item:hover {
            background-color: #e9ecef;
            transform: translateX(5px);
        }

        .action-badge-small {
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
            white-space: nowrap;
        }

        .empty-state-small {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state-small i {
            color: #cbd5e0;
            margin-bottom: 15px;
        }

        .platform-info {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .platform-label {
            color: #6c757d;
        }

        .platform-value {
            font-weight: 500;
            color: #212529;
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
                                <h1>Activity Log Details</h1>
                            </div>
                        </div>
                        <span class="text-muted">View detailed information about this activity log</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.activity-logs.index') }}">Activity Logs</a></li>
                                    <li class="active">Log #{{ $activityLog->id }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Back Button -->
                    <div class="row">
                        <div class="col-lg-12">
                            <a href="{{ route('superadmin.activity-logs.index') }}" class="btn btn-secondary mb-3">
                                <i class="ti-arrow-left"></i> Back to Activity Logs
                            </a>
                        </div>
                    </div>

                    <!-- Main Log Details -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="detail-card">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                                    <div>
                                        <h2 style="font-weight: bold; margin-bottom: 10px;">
                                            {{ ucwords(str_replace('_', ' ', $activityLog->action)) }}
                                        </h2>
                                        <p class="text-muted">{{ $activityLog->description }}</p>
                                    </div>
                                    <span class="action-badge-large 
                                        {{ str_contains($activityLog->action, 'created') ? 'badge-created' : 
                                           (str_contains($activityLog->action, 'updated') ? 'badge-updated' : 
                                           (str_contains($activityLog->action, 'deleted') ? 'badge-deleted' : 'badge-default')) }}">
                                        {{ ucwords(str_replace('_', ' ', $activityLog->action)) }}
                                    </span>
                                </div>

                                <div class="info-grid">
                                    <!-- Log ID -->
                                    <div class="info-item">
                                        <div class="info-label">Log ID</div>
                                        <div class="info-value">#{{ $activityLog->id }}</div>
                                    </div>

                                    <!-- Timestamp -->
                                    <div class="info-item">
                                        <div class="info-label">Timestamp</div>
                                        <div class="info-value">{{ $activityLog->created_at->format('d F Y') }}</div>
                                        <small class="text-muted">{{ $activityLog->created_at->format('H:i:s') }} ({{ $activityLog->created_at->diffForHumans() }})</small>
                                    </div>

                                    <!-- Model Info -->
                                    <div class="info-item">
                                        <div class="info-label">Related Model</div>
                                        @if($activityLog->model_type)
                                        <div class="info-value">{{ class_basename($activityLog->model_type) }}</div>
                                        <small class="text-muted">ID: {{ $activityLog->model_id }}</small>
                                        @else
                                        <div style="color: #cbd5e0; font-style: italic;">No model associated</div>
                                        @endif
                                    </div>

                                    <!-- Action Type -->
                                    <div class="info-item">
                                        <div class="info-label">Action Type</div>
                                        <div class="info-value">{{ ucwords(str_replace('_', ' ', $activityLog->action)) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Information -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="detail-card">
                                <h3 style="font-weight: 600; margin-bottom: 20px;">
                                    <i class="ti-user"></i> User Information
                                </h3>
                                
                                @if($activityLog->user)
                                <div style="display: flex; gap: 30px;">
                                    <!-- User Avatar -->
                                    <div style="flex-shrink: 0;">
                                        @if($activityLog->user->profile_photo)
                                            <img src="{{ asset('storage/' . $activityLog->user->profile_photo) }}" alt="{{ $activityLog->user->name }}" class="user-avatar-large">
                                        @else
                                            <div class="user-avatar-initial-large">
                                                {{ strtoupper(substr($activityLog->user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- User Details -->
                                    <div style="flex: 1;">
                                        <div class="info-grid">
                                            <div class="info-item">
                                                <div class="info-label">Name</div>
                                                <div class="info-value">{{ $activityLog->user->name }}</div>
                                            </div>

                                            <div class="info-item">
                                                <div class="info-label">Email</div>
                                                <div class="info-value" style="font-weight: 400;">{{ $activityLog->user->email }}</div>
                                            </div>

                                            <div class="info-item">
                                                <div class="info-label">Role</div>
                                                <span class="role-badge 
                                                    {{ $activityLog->user->role === 'superadmin' ? 'role-superadmin' : 
                                                       ($activityLog->user->role === 'admin' ? 'role-admin' : 
                                                       ($activityLog->user->role === 'teacher' ? 'role-teacher' : 'role-parent')) }}">
                                                    {{ ucfirst($activityLog->user->role) }}
                                                </span>
                                            </div>

                                            <div class="info-item">
                                                <div class="info-label">Phone</div>
                                                <div class="info-value" style="font-weight: 400;">{{ $activityLog->user->phone ?? 'N/A' }}</div>
                                            </div>
                                        </div>

                                        <div style="margin-top: 20px;">
                                            <a href="{{ route('superadmin.users.show', $activityLog->user) }}" class="btn btn-primary btn-sm">
                                                <i class="ti-eye"></i> View User Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="empty-state-small">
                                    <i class="ti-desktop"></i>
                                    <p class="text-muted">System-generated action</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Technical Details -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="detail-card">
                                <h3 style="font-weight: 600; margin-bottom: 20px;">
                                    <i class="ti-settings"></i> Technical Details
                                </h3>
                                
                                <div class="row">
                                    <!-- IP Address -->
                                    <div class="col-lg-6">
                                        <div class="info-label mb-2">IP Address</div>
                                        <div class="technical-detail-box">
                                            <i class="ti-world"></i>
                                            <span style="font-family: monospace;">{{ $activityLog->ip_address ?? 'Not recorded' }}</span>
                                        </div>
                                    </div>

                                    <!-- Browser & Platform -->
                                    @if($userAgentInfo)
                                    <div class="col-lg-6">
                                        <div class="info-label mb-2">Browser & Platform</div>
                                        <div class="platform-info">
                                            <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                                <i class="ti-desktop" style="margin-right: 8px; color: #6c757d;"></i>
                                                <div>
                                                    <div class="platform-label">Browser</div>
                                                    <div class="platform-value">{{ $userAgentInfo['browser'] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="platform-info">
                                            <div style="display: flex; align-items: center;">
                                                <i class="ti-mobile" style="margin-right: 8px; color: #6c757d;"></i>
                                                <div>
                                                    <div class="platform-label">Platform</div>
                                                    <div class="platform-value">{{ $userAgentInfo['platform'] }} ({{ $userAgentInfo['device'] }})</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Full User Agent -->
                                @if($activityLog->user_agent)
                                <div style="margin-top: 20px;">
                                    <div class="info-label mb-2">Full User Agent</div>
                                    <div class="user-agent-box">
                                        {{ $activityLog->user_agent }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Related Logs -->
                    @if($relatedLogs->count() > 0)
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="detail-card">
                                <h3 style="font-weight: 600; margin-bottom: 20px;">
                                    <i class="ti-link"></i> Related Activity (Same {{ class_basename($activityLog->model_type) }})
                                </h3>
                                
                                @foreach($relatedLogs as $related)
                                <div class="related-log-item">
                                    <div style="flex-shrink: 0; margin-right: 15px;">
                                        <span class="action-badge-small 
                                            {{ str_contains($related->action, 'created') ? 'badge-created' : 
                                               (str_contains($related->action, 'updated') ? 'badge-updated' : 
                                               (str_contains($related->action, 'deleted') ? 'badge-deleted' : 'badge-default')) }}">
                                            {{ ucwords(str_replace('_', ' ', $related->action)) }}
                                        </span>
                                    </div>
                                    <div style="flex: 1;">
                                        <p style="margin-bottom: 5px;">{{ $related->description }}</p>
                                        <p style="font-size: 1rem; color: #6c757d; margin: 0;">
                                            {{ $related->user ? $related->user->name : 'System' }} • {{ $related->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <a href="{{ route('superadmin.activity-logs.show', $related) }}" class="btn btn-sm btn-primary">
                                        <i class="ti-eye"></i> View
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- User's Recent Activity -->
                    @if($userRecentActivity->count() > 0 && $activityLog->user)
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="detail-card">
                                <h3 style="font-weight: 600; margin-bottom: 20px;">
                                    <i class="ti-time"></i> {{ $activityLog->user->name }}'s Recent Activity
                                </h3>
                                
                                @foreach($userRecentActivity as $recent)
                                <div class="related-log-item">
                                    <div style="flex-shrink: 0; margin-right: 15px;">
                                        <span class="action-badge-small 
                                            {{ str_contains($recent->action, 'created') ? 'badge-created' : 
                                               (str_contains($recent->action, 'updated') ? 'badge-updated' : 
                                               (str_contains($recent->action, 'deleted') ? 'badge-deleted' : 'badge-default')) }}">
                                            {{ ucwords(str_replace('_', ' ', $recent->action)) }}
                                        </span>
                                    </div>
                                    <div style="flex: 1;">
                                        <p style="margin-bottom: 5px;">{{ $recent->description }}</p>
                                        <p style="font-size: 1rem; color: #6c757d; margin: 0;">{{ $recent->created_at->diffForHumans() }}</p>
                                    </div>
                                    <a href="{{ route('superadmin.activity-logs.show', $recent) }}" class="btn btn-sm btn-primary">
                                        <i class="ti-eye"></i> View
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Activity Log Details</p>
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
            // Add any custom JavaScript here if needed
        });
    </script>
@endpush
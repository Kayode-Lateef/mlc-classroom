@extends('layouts.app')

@section('title', 'Notifications')

@push('styles')
<style>
    .compose-card {
        position: sticky;
        top: 20px;
    }

    .recipient-preview {
        background-color: #e7f3ff;
        padding: 12px;
        border-radius: 6px;
        margin-top: 10px;
    }

    .channel-checkbox {
        display: flex;
        align-items: center;
        padding: 10px;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .channel-checkbox:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
    }

    .channel-checkbox input:checked ~ label {
        font-weight: 600;
    }

    /* Notification History Scrollable Container */
    .notification-history-container {
        max-height: 700px;
        overflow-y: auto;
        padding-right: 10px;
    }

    .notification-history-item {
        border-left: 4px solid #007bff;
        background: white;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .notification-history-item:hover {
        box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    }

    .notification-history-item.type-emergency {
        border-left-color: #dc3545;
    }

    .notification-history-item.type-homework {
        border-left-color: #ffc107;
    }

    .notification-history-item.type-progress_report {
        border-left-color: #28a745;
    }

    .notification-history-item.type-schedule_change {
        border-left-color: #fd7e14;
    }

    .notification-history-item.type-absence {
        border-left-color: #0dcaf0;
    }

    .notification-meta {
        color: #6c757d;
        margin-top: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notification-title {
        font-weight: 600;
        color: #212529;
        margin-bottom: 5px;
    }

    .notification-message {
        color: #495057;
        line-height: 1.5;
    }

    .notification-badge-type {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-general { background-color: #e7f3ff; color: #007bff; }
    .badge-emergency { background-color: #ffe7e7; color: #dc3545; }
    .badge-homework { background-color: #fff3cd; color: #856404; }
    .badge-progress_report { background-color: #d4edda; color: #155724; }
    .badge-schedule_change { background-color: #ffe5cc; color: #cc5200; }
    .badge-absence { background-color: #cff4fc; color: #055160; }

    .read-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #28a745;
        margin-left: 8px;
    }

    .unread-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #dc3545;
        margin-left: 8px;
        animation: pulse 2s infinite;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .empty-state i {
        opacity: 0.3;
        margin-bottom: 20px;
    }

    .required-field::after {
        content: ' *';
        color: #dc3545;
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
                                <h1>Notifications</h1>
                            </div>
                        </div>
                        <span>Send notifications and view notification history</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="#">Communication</a></li>
                                    <li class="active">Notifications</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            

                <!-- Statistics Cards -->
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="stat-widget-one">
                                <div class="stat-icon dib"><i class="ti-check color-success border-success"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">Sent Today</div>
                                    <div class="stat-digit">{{ $stats['today_notifications'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="stat-widget-one">
                                <div class="stat-icon dib"><i class="ti-calendar color-primary border-primary"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">This Week</div>
                                    <div class="stat-digit">{{ $stats['week_notifications'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="stat-widget-one">
                                <div class="stat-icon dib"><i class="ti-stats-up color-info border-info"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">This Month</div>
                                    <div class="stat-digit">{{ $stats['month_notifications'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="stat-widget-one">
                                <div class="stat-icon dib"><i class="ti-bell color-danger border-danger"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">Unread</div>
                                    <div class="stat-digit">{{ $stats['unread_notifications'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Send Notification Form -->
                    <div class="col-lg-7">
                        <div class="card alert compose-card">
                            <div class="card-header mb-2">
                                <h4><i class="ti-announcement"></i> Send Notification</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('superadmin.notifications.send') }}" method="POST" id="notification-form">
                                    @csrf

                                    <!-- Recipients -->
                                    <div class="form-group">
                                        <label class="required-field">Send To</label>
                                        <select name="recipient_type" id="recipient_type" required class="form-control @error('recipient_type') is-invalid @enderror">
                                            <option value="">Select recipients...</option>
                                            <option value="all_parents" {{ old('recipient_type') == 'all_parents' ? 'selected' : '' }}>All Parents</option>
                                            <option value="all_teachers" {{ old('recipient_type') == 'all_teachers' ? 'selected' : '' }}>All Teachers</option>
                                            <option value="class" {{ old('recipient_type') == 'class' ? 'selected' : '' }}>Specific Class Parents</option>
                                        </select>
                                        @error('recipient_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Class Selection -->
                                    <div class="form-group" id="class-selection" style="display: {{ old('recipient_type') == 'class' ? 'block' : 'none' }};">
                                        <label class="required-field">Select Class</label>
                                        <select name="class_id" class="form-control @error('class_id') is-invalid @enderror">
                                            <option value="">Choose class...</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }}
                                                    @if($class->teacher)
                                                        (Teacher: {{ $class->teacher->name }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">
                                            <i class="ti-info-alt"></i> Will send to all parents of students enroled in this class
                                        </small>
                                        @error('class_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Type -->
                                    <div class="form-group">
                                        <label class="required-field">Notification Type</label>
                                        <select name="notification_type" required class="form-control @error('notification_type') is-invalid @enderror">
                                            <option value="general" {{ old('notification_type') == 'general' ? 'selected' : '' }}>General</option>
                                            <option value="emergency" {{ old('notification_type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                            <option value="homework" {{ old('notification_type') == 'homework' ? 'selected' : '' }}>Homework</option>
                                            <option value="progress_report" {{ old('notification_type') == 'progress_report' ? 'selected' : '' }}>Progress Report</option>
                                            <option value="schedule_change" {{ old('notification_type') == 'schedule_change' ? 'selected' : '' }}>Schedule Change</option>
                                            <option value="absence" {{ old('notification_type') == 'absence' ? 'selected' : '' }}>Absence</option>
                                        </select>
                                        @error('notification_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Channels -->
                                    <div class="form-group">
                                        <label class="required-field">Channels</label>
                                        <div class="channel-checkbox">
                                            <input type="checkbox" name="channels[]" value="email" id="channel_email" {{ old('channels') && in_array('email', old('channels')) ? 'checked' : 'checked' }}>
                                            <label for="channel_email" style="margin: 0 0 0 10px; cursor: pointer; flex: 1;">
                                                <i class="ti-email"></i> Email
                                            </label>
                                        </div>
                                        <div class="channel-checkbox">
                                            <input type="checkbox" name="channels[]" value="sms" id="channel_sms" {{ old('channels') && in_array('sms', old('channels')) ? 'checked' : '' }}>
                                            <label for="channel_sms" style="margin: 0 0 0 10px; cursor: pointer; flex: 1;">
                                                <i class="ti-mobile"></i> SMS
                                            </label>
                                        </div>
                                        <div class="channel-checkbox">
                                            <input type="checkbox" name="channels[]" value="in_app" id="channel_app" {{ old('channels') && in_array('in_app', old('channels')) ? 'checked' : 'checked' }}>
                                            <label for="channel_app" style="margin: 0 0 0 10px; cursor: pointer; flex: 1;">
                                                <i class="ti-bell"></i> In-App
                                            </label>
                                        </div>
                                        @error('channels')
                                            <div class="text-danger" style="font-size: 0.875rem; margin-top: 5px;">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Title -->
                                    <div class="form-group">
                                        <label class="required-field">Title</label>
                                        <input type="text" name="title" required maxlength="255" value="{{ old('title') }}" placeholder="Notification title" class="form-control @error('title') is-invalid @enderror">
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Message -->
                                    <div class="form-group">
                                        <label class="required-field">Message</label>
                                        <textarea name="message" rows="5" required maxlength="1000" placeholder="Your message..." class="form-control @error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                                        <small class="form-text text-muted">Max 1000 characters</small>
                                        @error('message')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-block" id="send-btn">
                                        <i class="ti-check"></i> Send Notification
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Notification History -->
                    <div class="col-lg-5">
                        <div class="card alert">
                            <div class="card-header mb-2">
                                <h4><i class="ti-time"></i> Recent Notifications (Last 50)</h4>
                            </div>
                            <div class="card-body">
                                @if($recentNotifications->count() > 0)
                                    <div class="notification-history-container">
                                        @foreach($recentNotifications as $notification)
                                            @php
                                                $data = $notification->data;
                                                $type = $data['type'] ?? 'general';
                                                $title = $data['title'] ?? 'Notification';
                                                $message = $data['message'] ?? '';
                                                $sentBy = $data['sent_by'] ?? 'System';
                                                $sentAt = $data['sent_at'] ?? $notification->created_at->format('d M Y, H:i');
                                                $isRead = $notification->read_at !== null;
                                            @endphp
                                            
                                            <div class="notification-history-item type-{{ $type }}">
                                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                                    <span class="notification-badge-type badge-{{ $type }}">
                                                        {{ str_replace('_', ' ', $type) }}
                                                    </span>
                                                    <span style="font-size: 12px; color: #6c757d;">
                                                        <i class="ti-time"></i> {{ $notification->created_at->diffForHumans() }}
                                                        @if($isRead)
                                                            <span class="read-indicator" title="Read"></span>
                                                        @else
                                                            <span class="unread-indicator" title="Unread"></span>
                                                        @endif
                                                    </span>
                                                </div>
                                                
                                                <div class="notification-title">{{ $title }}</div>
                                                <div class="notification-message">{{ $message }}</div>
                                                
                                                <div class="notification-meta">
                                                    <span><i class="ti-user"></i> Sent by: {{ $sentBy }}</span>
                                                    <span><i class="ti-calendar"></i> {{ $sentAt }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <i class="ti-bell"></i>
                                        <p style="font-size: 1rem; margin-top: 15px;">No notifications sent yet</p>
                                        <p style="font-size: 0.875rem; color: #868e96;">Send your first notification using the form on the left</p>
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
                            <p>MLC Classroom - Notifications</p>
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
    // Show/hide class selection based on recipient type
    $('#recipient_type').on('change', function() {
        if ($(this).val() === 'class') {
            $('#class-selection').slideDown();
        } else {
            $('#class-selection').slideUp();
        }
    });

    // Form submission feedback
    $('#notification-form').on('submit', function() {
        var btn = $('#send-btn');
        btn.prop('disabled', true);
        btn.html('<i class="ti-reload"></i> Sending...');
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissable').fadeOut('slow');
    }, 5000);
});
</script>
@endpush
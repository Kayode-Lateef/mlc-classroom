@extends('layouts.app')

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

    .channel-checkbox input:checked + label {
        font-weight: 600;
    }

    .history-item {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
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

                <div class="row">
                    <!-- Send Notification Form -->
                    <div class="col-lg-5">
                        <div class="card alert compose-card">
                            <div class="card-header mb-2">
                                <h4><i class="ti-announcement"></i> Send Notification</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('superadmin.notifications.send') }}" method="POST">
                                    @csrf

                                    <!-- Recipients -->
                                    <div class="form-group">
                                        <label class="required-field">Send To</label>
                                        <select name="recipient_type" id="recipient_type" required class="form-control">
                                            <option value="">Select recipients...</option>
                                            <option value="all_parents">All Parents</option>
                                            <option value="all_teachers">All Teachers</option>
                                            <option value="class">Specific Class Parents</option>
                                            <option value="individual">Individual User</option>
                                        </select>
                                    </div>

                                    <!-- Class Selection -->
                                    <div class="form-group" id="class-selection" style="display: none;">
                                        <label>Select Class</label>
                                        <select name="class_id" class="form-control">
                                            <option value="">Choose class...</option>
                                        </select>
                                    </div>

                                    <!-- Type -->
                                    <div class="form-group">
                                        <label class="required-field">Notification Type</label>
                                        <select name="notification_type" required class="form-control">
                                            <option value="general">General</option>
                                            <option value="emergency">Emergency</option>
                                            <option value="homework">Homework</option>
                                            <option value="progress_report">Progress Report</option>
                                            <option value="schedule_change">Schedule Change</option>
                                        </select>
                                    </div>

                                    <!-- Channels -->
                                    <div class="form-group">
                                        <label>Channels</label>
                                        <div class="channel-checkbox">
                                            <input type="checkbox" name="channels[]" value="email" id="channel_email" checked>
                                            <label for="channel_email" style="margin: 0 0 0 10px; cursor: pointer; flex: 1;">
                                                <i class="ti-email"></i> Email
                                            </label>
                                        </div>
                                        <div class="channel-checkbox">
                                            <input type="checkbox" name="channels[]" value="sms" id="channel_sms">
                                            <label for="channel_sms" style="margin: 0 0 0 10px; cursor: pointer; flex: 1;">
                                                <i class="ti-mobile"></i> SMS
                                            </label>
                                        </div>
                                        <div class="channel-checkbox">
                                            <input type="checkbox" name="channels[]" value="in_app" id="channel_app" checked>
                                            <label for="channel_app" style="margin: 0 0 0 10px; cursor: pointer; flex: 1;">
                                                <i class="ti-bell"></i> In-App
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Title -->
                                    <div class="form-group">
                                        <label class="required-field">Title</label>
                                        <input type="text" name="title" required maxlength="255" placeholder="Notification title" class="form-control">
                                    </div>

                                    <!-- Message -->
                                    <div class="form-group">
                                        <label class="required-field">Message</label>
                                        <textarea name="message" rows="5" required maxlength="1000" placeholder="Your message..." class="form-control"></textarea>
                                        <small class="form-text text-muted">Max 1000 characters</small>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="ti-check"></i> Send Notification
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Notification History -->
                    <div class="col-lg-7">
                        <div class="card alert">
                            <div class="card-header mb-2">
                                <h4><i class="ti-time"></i> Recent Notifications</h4>
                            </div>
                            <div class="card-body">
                                <p style="text-align: center; padding: 40px 20px; color: #6c757d;">
                                    <i class="ti-bell" style="font-size: 3rem; opacity: 0.3;"></i><br>
                                    Notification history will appear here
                                </p>
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
    $('#recipient_type').on('change', function() {
        if ($(this).val() === 'class') {
            $('#class-selection').show();
        } else {
            $('#class-selection').hide();
        }
    });
});
</script>
@endpush
@extends('layouts.app')

@section('title', 'System Settings')

@push('styles')
    <style>
        .settings-header {
            background: linear-gradient(135deg, #3386f7 0%, #2c75d6 100%);
            color: white;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .settings-header h1 {
            color: white;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .settings-header p {
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            font-size: 1.0625rem;
        }

        .settings-nav {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }

        .settings-nav .nav-pills .nav-link {
            color: #495057;
            padding: 12px 20px;
            margin-bottom: 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .settings-nav .nav-pills .nav-link i {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }

        .settings-nav .nav-pills .nav-link:hover {
            background-color: #f8f9fa;
            color: #3386f7;
        }

        .settings-nav .nav-pills .nav-link.active {
            background-color: #3386f7;
            color: white;
        }

        .settings-content {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .setting-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .setting-card:hover {
            border-color: #3386f7;
            box-shadow: 0 4px 8px rgba(51, 134, 247, 0.1);
        }

        .setting-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .setting-card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .setting-card-description {
            font-size: 0.9375rem;
            color: #7f8c8d;
            margin: 0;
        }

        .notification-table {
            width: 100%;
            margin-top: 20px;
        }

        .notification-table th {
            background-color: #f8f9fa;
            padding: 12px;
            font-weight: 600;
            font-size: 0.9375rem;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }

        .notification-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .notification-table tr:hover {
            background-color: #f8f9fa;
        }

        .notification-label {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            color: #2c3e50;
        }

        .notification-label i {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }

        .notification-label .badge {
            font-size: 0.75rem;
            padding: 3px 8px;
        }

        .custom-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .custom-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .switch-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 26px;
        }

        .switch-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .switch-slider {
            background-color: #3386f7;
        }

        input:checked + .switch-slider:before {
            transform: translateX(24px);
        }

        .info-alert {
            background-color: #e8f4fd;
            border-left: 4px solid #3386f7;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .info-alert i {
            color: #3386f7;
            font-size: 1.5rem;
            margin-right: 12px;
        }

        .warning-alert {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .warning-alert i {
            color: #ffc107;
            font-size: 1.5rem;
            margin-right: 12px;
        }

        .form-actions {
            padding-top: 25px;
            border-top: 2px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-badge.enabled {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.disabled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .channel-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
        }

        .channel-icon.email {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .channel-icon.sms {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }

        .channel-icon.app {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        @media (max-width: 768px) {
            .settings-nav {
                margin-bottom: 20px;
            }

            .notification-table {
                display: block;
                overflow-x: auto;
            }

            .setting-card-header {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="settings-header">
                            <h1><i class="ti-settings"></i> System Settings</h1>
                            <p>Configure system-wide settings and notification preferences</p>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Success Message -->
                    @if(session('success'))
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="alert alert-success alert-dismissible fade show">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <i class="ti-check"></i> {{ session('success') }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Error Message -->
                    @if(session('error'))
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="alert alert-danger alert-dismissible fade show">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <i class="ti-alert"></i> {{ session('error') }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Settings Layout -->
                    <div class="row">
                        <!-- Settings Navigation -->
                        <div class="col-lg-3 col-md-4">
                            <div class="settings-nav">
                                <ul class="nav nav-pills flex-column" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="pill" href="#notifications">
                                            <i class="ti-bell"></i>
                                            <span>Notifications</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="pill" href="#system">
                                            <i class="ti-settings"></i>
                                            <span>System Info</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('profile.edit') }}">
                                            <i class="ti-user"></i>
                                            <span>Profile Settings</span>
                                            <i class="ti-angle-right ml-auto"></i>
                                        </a>
                                    </li>
                                </ul>

                                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                                    <div class="info-alert" style="padding: 12px;">
                                        <i class="ti-info-alt" style="font-size: 1.25rem; margin-right: 8px;"></i>
                                        <small style="font-size: 0.8125rem;">
                                            Profile and password settings are managed separately via Profile Settings.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Settings Content -->
                        <div class="col-lg-9 col-md-8">
                            <div class="settings-content">
                                <div class="tab-content">
                                    <!-- Notifications Tab -->
                                    <div class="tab-pane fade show active" id="notifications">
                                        <h2 class="section-title">
                                            <i class="ti-bell"></i> Notification Preferences
                                        </h2>

                                        <div class="info-alert">
                                            <i class="ti-info-alt"></i>
                                            <div>
                                                <strong style="font-size: 0.9375rem;">Manage Your Notification Channels</strong>
                                                <p style="margin: 5px 0 0 0; font-size: 0.875rem;">
                                                    Choose how you want to receive notifications for different events. You can enable or disable Email, SMS, and In-App notifications for each notification type.
                                                </p>
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('admin.settings.update') }}">
                                            @csrf
                                            <input type="hidden" name="section" value="notifications">

                                            <!-- Notification Channels Legend -->
                                            <div class="setting-card" style="background-color: #f8f9fa;">
                                                <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 20px;">
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        <div class="channel-icon email">
                                                            <i class="ti-email"></i>
                                                        </div>
                                                        <div>
                                                            <div style="font-weight: 600; font-size: 0.9375rem;">Email</div>
                                                            <small style="color: #7f8c8d;">Email Notifications</small>
                                                        </div>
                                                    </div>
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        <div class="channel-icon sms">
                                                            <i class="ti-mobile"></i>
                                                        </div>
                                                        <div>
                                                            <div style="font-weight: 600; font-size: 0.9375rem;">SMS</div>
                                                            <small style="color: #7f8c8d;">Text Messages</small>
                                                        </div>
                                                    </div>
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        <div class="channel-icon app">
                                                            <i class="ti-bell"></i>
                                                        </div>
                                                        <div>
                                                            <div style="font-weight: 600; font-size: 0.9375rem;">In-App</div>
                                                            <small style="color: #7f8c8d;">Dashboard Alerts</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Notification Types Table -->
                                            <div class="setting-card">
                                                <table class="notification-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Notification Type</th>
                                                            <th style="text-align: center; width: 100px;">
                                                                <i class="ti-email"></i> Email
                                                            </th>
                                                            <th style="text-align: center; width: 100px;">
                                                                <i class="ti-mobile"></i> SMS
                                                            </th>
                                                            <th style="text-align: center; width: 100px;">
                                                                <i class="ti-bell"></i> In-App
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Absence Notifications -->
                                                        <tr>
                                                            <td>
                                                                <div class="notification-label">
                                                                    <i class="ti-close" style="color: #dc3545;"></i>
                                                                    <div>
                                                                        <strong>Student Absence</strong>
                                                                        <div style="font-size: 0.8125rem; color: #7f8c8d;">
                                                                            When students are marked absent
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[absence][email_enabled]" 
                                                                           {{ $notificationSettings['absence']->email_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[absence][sms_enabled]" 
                                                                           {{ $notificationSettings['absence']->sms_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[absence][in_app_enabled]" 
                                                                           {{ $notificationSettings['absence']->in_app_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                        </tr>

                                                        <!-- Homework Assigned -->
                                                        <tr>
                                                            <td>
                                                                <div class="notification-label">
                                                                    <i class="ti-book" style="color: #3386f7;"></i>
                                                                    <div>
                                                                        <strong>Homework Assigned</strong>
                                                                        <div style="font-size: 0.8125rem; color: #7f8c8d;">
                                                                            When new homework is assigned
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[homework_assigned][email_enabled]" 
                                                                           {{ $notificationSettings['homework_assigned']->email_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[homework_assigned][sms_enabled]" 
                                                                           {{ $notificationSettings['homework_assigned']->sms_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[homework_assigned][in_app_enabled]" 
                                                                           {{ $notificationSettings['homework_assigned']->in_app_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                        </tr>

                                                        <!-- Homework Graded -->
                                                        <tr>
                                                            <td>
                                                                <div class="notification-label">
                                                                    <i class="ti-check-box" style="color: #28a745;"></i>
                                                                    <div>
                                                                        <strong>Homework Graded</strong>
                                                                        <div style="font-size: 0.8125rem; color: #7f8c8d;">
                                                                            When homework is graded
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[homework_graded][email_enabled]" 
                                                                           {{ $notificationSettings['homework_graded']->email_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[homework_graded][sms_enabled]" 
                                                                           {{ $notificationSettings['homework_graded']->sms_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[homework_graded][in_app_enabled]" 
                                                                           {{ $notificationSettings['homework_graded']->in_app_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                        </tr>

                                                        <!-- Progress Report -->
                                                        <tr>
                                                            <td>
                                                                <div class="notification-label">
                                                                    <i class="ti-clipboard" style="color: #17a2b8;"></i>
                                                                    <div>
                                                                        <strong>Progress Report</strong>
                                                                        <div style="font-size: 0.8125rem; color: #7f8c8d;">
                                                                            When progress sheets are published
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[progress_report][email_enabled]" 
                                                                           {{ $notificationSettings['progress_report']->email_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[progress_report][sms_enabled]" 
                                                                           {{ $notificationSettings['progress_report']->sms_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[progress_report][in_app_enabled]" 
                                                                           {{ $notificationSettings['progress_report']->in_app_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                        </tr>

                                                        <!-- Schedule Change -->
                                                        <tr>
                                                            <td>
                                                                <div class="notification-label">
                                                                    <i class="ti-calendar" style="color: #ffc107;"></i>
                                                                    <div>
                                                                        <strong>Schedule Change</strong>
                                                                        <div style="font-size: 0.8125rem; color: #7f8c8d;">
                                                                            When class schedules are modified
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[schedule_change][email_enabled]" 
                                                                           {{ $notificationSettings['schedule_change']->email_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[schedule_change][sms_enabled]" 
                                                                           {{ $notificationSettings['schedule_change']->sms_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[schedule_change][in_app_enabled]" 
                                                                           {{ $notificationSettings['schedule_change']->in_app_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                        </tr>

                                                        <!-- Emergency -->
                                                        <tr>
                                                            <td>
                                                                <div class="notification-label">
                                                                    <i class="ti-alert" style="color: #e91e63;"></i>
                                                                    <div>
                                                                        <strong>Emergency Alerts</strong>
                                                                        <span class="badge badge-danger ml-2">Critical</span>
                                                                        <div style="font-size: 0.8125rem; color: #7f8c8d;">
                                                                            Urgent system or safety alerts
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[emergency][email_enabled]" 
                                                                           {{ $notificationSettings['emergency']->email_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[emergency][sms_enabled]" 
                                                                           {{ $notificationSettings['emergency']->sms_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <label class="custom-switch">
                                                                    <input type="checkbox" 
                                                                           name="notifications[emergency][in_app_enabled]" 
                                                                           {{ $notificationSettings['emergency']->in_app_enabled ? 'checked' : '' }}>
                                                                    <span class="switch-slider"></span>
                                                                </label>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- SMS Provider Info -->
                                            <div class="warning-alert">
                                                <i class="ti-info-alt"></i>
                                                <div>
                                                    <strong style="font-size: 0.9375rem;">SMS Configuration</strong>
                                                    <p style="margin: 5px 0 0 0; font-size: 0.875rem;">
                                                        SMS notifications are sent via Twilio. Make sure your Twilio credentials are properly configured in the <code>.env</code> file. SMS charges apply based on your Twilio plan.
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Form Actions -->
                                            <div class="form-actions">
                                                <button type="reset" class="btn btn-secondary btn-lg">
                                                    <i class="ti-reload"></i> Reset Changes
                                                </button>
                                                <button type="submit" class="btn btn-primary btn-lg" style="background-color: #3386f7; border-color: #3386f7;">
                                                    <i class="ti-check"></i> Save Notification Preferences
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- System Info Tab -->
                                    <div class="tab-pane fade" id="system">
                                        <h2 class="section-title">
                                            <i class="ti-settings"></i> System Information
                                        </h2>

                                        <div class="setting-card">
                                            <div class="setting-card-header">
                                                <div>
                                                    <h3 class="setting-card-title">Application Details</h3>
                                                    <p class="setting-card-description">System configuration and version information</p>
                                                </div>
                                            </div>

                                            <table class="table table-borderless">
                                                <tr>
                                                    <td style="width: 200px; font-weight: 600;">Application Name:</td>
                                                    <td>{{ config('app.name', 'MLC Classroom') }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600;">Environment:</td>
                                                    <td>
                                                        <span class="badge {{ config('app.env') === 'production' ? 'badge-success' : 'badge-warning' }}">
                                                            {{ strtoupper(config('app.env')) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600;">Laravel Version:</td>
                                                    <td>{{ app()->version() }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600;">PHP Version:</td>
                                                    <td>{{ phpversion() }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600;">Timezone:</td>
                                                    <td>{{ config('app.timezone') }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600;">Your Email:</td>
                                                    <td>{{ $admin->email }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600;">Your Role:</td>
                                                    <td>
                                                        <span class="badge badge-primary">{{ strtoupper($admin->role) }}</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                        <div class="info-alert">
                                            <i class="ti-info-alt"></i>
                                            <div>
                                                <strong style="font-size: 0.9375rem;">Note</strong>
                                                <p style="margin: 5px 0 0 0; font-size: 0.875rem;">
                                                    System configuration values are managed through the <code>.env</code> file and cannot be modified directly from this interface.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - System Settings</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
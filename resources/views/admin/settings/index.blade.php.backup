@extends('layouts.app')

@section('title', 'System Settings')

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

        .stat-widget-one .stat-value {
            font-weight: bold;
        }

        .tab-content {
            padding: 30px;
        }

        .setting-section {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .setting-section-title {
            font-weight: 600;
            margin-bottom: 15px;
        }

        .info-box {
            background-color: #e7f3ff;
            border-radius: 6px;
            padding: 15px;
            display: flex;
            align-items: flex-start;
        }

        .info-box i {
            color: #007bff;
            margin-right: 12px;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .form-actions {
            padding: 20px 30px;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .status-enabled {
            color: #28a745;
            font-weight: 600;
        }

        .status-disabled {
            color: #dc3545;
            font-weight: 600;
        }

        .toggle-label {
            display: flex;
            align-items: flex-start;
        }

        .toggle-label input[type="checkbox"] {
            margin-top: 6px;
        }

        .toggle-description {
            margin-left: 10px;
        }

        .notification-table {
            width: 100%;
            margin-top: 15px;
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
                                <h1>System Settings</h1>
                            </div>
                        </div>
                        <span>Configure notification preferences and view system information</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li class="active">System Settings</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-email color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Email Notifications</div>
                                        <div class="stat-value {{ $notificationSettings['absence']->email_enabled ? 'status-enabled' : 'status-disabled' }}">
                                            {{ $notificationSettings['absence']->email_enabled ? 'Enabled' : 'Disabled' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-mobile color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">SMS Notifications</div>
                                        <div class="stat-value {{ $notificationSettings['absence']->sms_enabled ? 'status-enabled' : 'status-disabled' }}">
                                            {{ $notificationSettings['absence']->sms_enabled ? 'Enabled' : 'Disabled' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-bell color-pink border-pink"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">In-App Notifications</div>
                                        <div class="stat-value {{ $notificationSettings['absence']->in_app_enabled ? 'status-enabled' : 'status-disabled' }}">
                                            {{ $notificationSettings['absence']->in_app_enabled ? 'Enabled' : 'Disabled' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-user color-warning border-warning"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Your Role</div>
                                        <div class="stat-value">{{ ucfirst($admin->role) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="row mt-3">
                            <div class="col-lg-12">
                                <div class="alert alert-success fade in alert-dismissable">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class="ti-check"></i> {{ session('success') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Error Message -->
                    @if(session('error'))
                        <div class="row mt-3">
                            <div class="col-lg-12">
                                <div class="alert alert-danger fade in alert-dismissable">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class="ti-alert"></i> {{ session('error') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Settings Form -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="custom-tab">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li role="presentation" class="active">
                                                <a href="#notifications-tab" aria-controls="notifications-tab" role="tab" data-toggle="tab">
                                                    <i class="ti-bell"></i> Notifications
                                                </a>
                                            </li>
                                            <li role="presentation">
                                                <a href="#system-tab" aria-controls="system-tab" role="tab" data-toggle="tab">
                                                    <i class="ti-settings"></i> System Info
                                                </a>
                                            </li>
                                        </ul>

                                        <div class="tab-content">
                                            <!-- Notifications Tab -->
                                            <div role="tabpanel" class="tab-pane active" id="notifications-tab">
                                                <h3 style="font-weight: 600; margin-bottom: 25px;">Notification Preferences</h3>

                                                <div class="info-box" style="margin-bottom: 25px;">
                                                    <i class="ti-info-alt"></i>
                                                    <div>
                                                        <p style="font-size: 1rem; font-weight: 500; margin-bottom: 5px;">Manage Your Notification Channels</p>
                                                        <p style="font-size: 0.9rem; margin: 0;">Choose how you want to receive notifications for different events. You can enable or disable Email, SMS, and In-App notifications for each notification type.</p>
                                                    </div>
                                                </div>

                                                <form method="POST" action="{{ route('admin.settings.update') }}">
                                                    @csrf
                                                    <input type="hidden" name="section" value="notifications">

                                                    <!-- Channel Legend -->
                                                    <div class="setting-section" style="background-color: #f8f9fa;">
                                                        <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 20px;">
                                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                                <div class="channel-icon email">
                                                                    <i class="ti-email"></i>
                                                                </div>
                                                                <div>
                                                                    <div style="font-weight: 600;">Email</div>
                                                                    <small style="color: #6c757d;">Email Notifications</small>
                                                                </div>
                                                            </div>
                                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                                <div class="channel-icon sms">
                                                                    <i class="ti-mobile"></i>
                                                                </div>
                                                                <div>
                                                                    <div style="font-weight: 600;">SMS</div>
                                                                    <small style="color: #6c757d;">Text Messages</small>
                                                                </div>
                                                            </div>
                                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                                <div class="channel-icon app">
                                                                    <i class="ti-bell"></i>
                                                                </div>
                                                                <div>
                                                                    <div style="font-weight: 600;">In-App</div>
                                                                    <small style="color: #6c757d;">Dashboard Alerts</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Notification Types Table -->
                                                    <div class="setting-section">
                                                        <h4 class="setting-section-title">Notification Types</h4>
                                                        <table class="notification-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Notification Type</th>
                                                                    <th style="text-align: center; width: 100px;"><i class="ti-email"></i> Email</th>
                                                                    <th style="text-align: center; width: 100px;"><i class="ti-mobile"></i> SMS</th>
                                                                    <th style="text-align: center; width: 100px;"><i class="ti-bell"></i> In-App</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- Student Absence -->
                                                                <tr>
                                                                    <td>
                                                                        <div class="notification-label">
                                                                            <i class="ti-close" style="color: #dc3545;"></i>
                                                                            <div>
                                                                                <strong>Student Absence</strong>
                                                                                <div style="font-size: 0.8rem; color: #6c757d;">When students are marked absent</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[absence][email_enabled]" {{ $notificationSettings['absence']->email_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[absence][sms_enabled]" {{ $notificationSettings['absence']->sms_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[absence][in_app_enabled]" {{ $notificationSettings['absence']->in_app_enabled ? 'checked' : '' }}>
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
                                                                                <div style="font-size: 0.8rem; color: #6c757d;">When new homework is assigned</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[homework_assigned][email_enabled]" {{ $notificationSettings['homework_assigned']->email_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[homework_assigned][sms_enabled]" {{ $notificationSettings['homework_assigned']->sms_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[homework_assigned][in_app_enabled]" {{ $notificationSettings['homework_assigned']->in_app_enabled ? 'checked' : '' }}>
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
                                                                                <div style="font-size: 0.8rem; color: #6c757d;">When homework is graded</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[homework_graded][email_enabled]" {{ $notificationSettings['homework_graded']->email_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[homework_graded][sms_enabled]" {{ $notificationSettings['homework_graded']->sms_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[homework_graded][in_app_enabled]" {{ $notificationSettings['homework_graded']->in_app_enabled ? 'checked' : '' }}>
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
                                                                                <div style="font-size: 0.8rem; color: #6c757d;">When progress sheets are published</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[progress_report][email_enabled]" {{ $notificationSettings['progress_report']->email_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[progress_report][sms_enabled]" {{ $notificationSettings['progress_report']->sms_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[progress_report][in_app_enabled]" {{ $notificationSettings['progress_report']->in_app_enabled ? 'checked' : '' }}>
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
                                                                                <div style="font-size: 0.8rem; color: #6c757d;">When class schedules are modified</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[schedule_change][email_enabled]" {{ $notificationSettings['schedule_change']->email_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[schedule_change][sms_enabled]" {{ $notificationSettings['schedule_change']->sms_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[schedule_change][in_app_enabled]" {{ $notificationSettings['schedule_change']->in_app_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                </tr>

                                                                <!-- Emergency Alerts -->
                                                                <tr>
                                                                    <td>
                                                                        <div class="notification-label">
                                                                            <i class="ti-alert" style="color: #e91e63;"></i>
                                                                            <div>
                                                                                <strong>Emergency Alerts</strong>
                                                                                <span class="badge badge-danger" style="margin-left: 8px;">Critical</span>
                                                                                <div style="font-size: 0.8rem; color: #6c757d;">Urgent system or safety alerts</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[emergency][email_enabled]" {{ $notificationSettings['emergency']->email_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[emergency][sms_enabled]" {{ $notificationSettings['emergency']->sms_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <label class="custom-switch">
                                                                            <input type="checkbox" name="notifications[emergency][in_app_enabled]" {{ $notificationSettings['emergency']->in_app_enabled ? 'checked' : '' }}>
                                                                            <span class="switch-slider"></span>
                                                                        </label>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <!-- SMS Provider Info -->
                                                    <div class="setting-section" style="background-color: #fff3cd; border-color: #ffc107;">
                                                        <div style="display: flex; align-items: flex-start;">
                                                            <i class="ti-info-alt" style="color: #856404; margin-right: 12px; font-size: 1.5rem;"></i>
                                                            <div>
                                                                <p style="font-size: 1rem; font-weight: 500; margin-bottom: 5px; color: #856404;">SMS Configuration</p>
                                                                <p style="font-size: 0.9rem; margin: 0; color: #856404;">SMS notifications are sent via Twilio. Make sure your Twilio credentials are properly configured in the <code>.env</code> file. SMS charges apply based on your Twilio plan.</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Form Actions -->
                                                    <div class="form-actions">
                                                        <button type="reset" class="btn btn-secondary">
                                                            <i class="ti-reload"></i> Reset Changes
                                                        </button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="ti-check"></i> Save Notification Preferences
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- System Info Tab -->
                                            <div role="tabpanel" class="tab-pane" id="system-tab">
                                                <h3 style="font-weight: 600; margin-bottom: 25px;">System Information</h3>

                                                <div class="setting-section">
                                                    <h4 class="setting-section-title">Application Details</h4>
                                                    <p style="font-size: 0.9rem; color: #6c757d; margin-bottom: 20px;">System configuration and version information</p>

                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <table class="table table-borderless">
                                                                <tr>
                                                                    <td style="width: 180px; font-weight: 600;">Application Name:</td>
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
                                                            </table>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <table class="table table-borderless">
                                                                <tr>
                                                                    <td style="width: 180px; font-weight: 600;">Timezone:</td>
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
                                                    </div>
                                                </div>

                                                <div class="info-box">
                                                    <i class="ti-info-alt"></i>
                                                    <div>
                                                        <p style="font-size: 1rem; font-weight: 500; margin-bottom: 5px;">Note</p>
                                                        <p style="font-size: 0.9rem; margin: 0;">System configuration values are managed through the <code>.env</code> file and cannot be modified directly from this interface. Profile and password settings can be managed via <a href="{{ route('profile.edit') }}">Profile Settings</a>.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
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
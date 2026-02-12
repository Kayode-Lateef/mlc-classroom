@extends('layouts.app')

@section('title', 'Settings')

@push('styles')
    <style>
        /* ============================================ */
        /* ADMIN SETTINGS STYLES                        */
        /* Colours: #3386f7 (blue) and #e06829 (orange) */
        /* ============================================ */

        .channel-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .channel-badge.blue   { background: rgba(51,134,247,0.1);  color: #3386f7; }
        .channel-badge.orange { background: rgba(224,104,41,0.1);  color: #e06829; }
        .channel-badge.green  { background: rgba(40,167,69,0.1);   color: #28a745; }

        .setting-section {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px 25px;
            margin-bottom: 20px;
        }

        .setting-section-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #2d3748;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3386f7;
        }

        .notification-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .notification-table thead th {
            background: #f1f5f9;
            padding: 12px 15px;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 2px solid #e2e8f0;
        }

        .notification-table tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: background 0.15s;
        }

        .notification-table tbody tr:hover {
            background: rgba(51,134,247,0.03);
        }

        .notification-table td {
            padding: 14px 15px;
            vertical-align: middle;
        }

        .notification-label {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notification-label i {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #f1f5f9;
        }

        .notification-label strong {
            display: block;
            color: #2d3748;
        }

        .notification-label small {
            color: #6c757d;
        }

        /* Custom Switch */
        .custom-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .custom-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .switch-slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #cbd5e0;
            border-radius: 24px;
            transition: 0.3s;
        }

        .switch-slider::before {
            content: "";
            position: absolute;
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background: #fff;
            border-radius: 50%;
            transition: 0.3s;
        }

        .custom-switch input:checked + .switch-slider {
            background-color: #3386f7;
        }

        .custom-switch input:checked + .switch-slider::before {
            transform: translateX(20px);
        }

        .btn-save-settings {
            background: linear-gradient(135deg, #3386f7 0%, #2a6fd6 100%);
            border: none;
            color: #fff;
            padding: 12px 35px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-save-settings:hover {
            background: linear-gradient(135deg, #2a6fd6 0%, #1e5bb5 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(51,134,247,0.3);
        }
    </style>
@endpush

@section('content')
    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">

                {{-- Page Header --}}
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

                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti-check"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti-alert"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif


                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-email color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Email</div>
                                        <div class="stat-value">
                                            {{ $channelStats['email_count'] }}/{{ $channelStats['total'] }}
                                        </div>
                                        <div style="font-size: 1rem; color: #6c757d; margin-top: 8px;">types with email enabled</div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-mobile color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">SMS</div>
                                        <div class="stat-value">
                                           {{ $channelStats['sms_count'] }}/{{ $channelStats['total'] }}
                                        </div>
                                        <div style="font-size: 1rem; color: #6c757d; margin-top: 8px;">types with SMS enabled</div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-bell color-pink border-pink"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">In-App</div>
                                        <div class="stat-value">
                                                {{ $channelStats['in_app_count'] }}/{{ $channelStats['total'] }}
                                        </div>
                                        <div style="font-size: 1rem; color: #6c757d; margin-top: 8px;">types with in-app enabled</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-user color-warning border-warning"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Signed in as</div>
                                        <div class="stat-value">{{ $admin->name }}</div>
                                        <div style="font-size: 1rem; color: #6c757d;">{{ $admin->email }}</div>

                                    </div>
                                </div>
                            </div>
                        </div>
                </div>

                {{-- Notification Settings Form --}}
                <div class="card" style="border-radius: 8px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 20px;">
                            <div>
                                <h3 style="font-weight: 600; margin: 0;">Notification Preferences</h3>
                                <p style="color: #6c757d; margin: 5px 0 0;">
                                    Enable or disable Email, SMS, and In-App notifications for each type.
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.settings.update') }}">
                            @csrf
                            <input type="hidden" name="section" value="notifications">

                            <div class="setting-section" style="padding: 0; background: transparent; border: none;">
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
                                        @foreach(\App\Models\NotificationSetting::TYPES as $type)
                                            <tr>
                                                <td>
                                                    <div class="notification-label">
                                                        <i class="{{ \App\Models\NotificationSetting::TYPE_ICONS[$type] ?? 'ti-bell' }}"
                                                           style="color: {{ \App\Models\NotificationSetting::TYPE_COLOURS[$type] ?? '#6c757d' }};">
                                                        </i>
                                                        <div>
                                                            <strong>{{ \App\Models\NotificationSetting::getLabel($type) }}</strong>
                                                            @if($type === 'emergency')
                                                                <span class="badge badge-danger" style="margin-left: 4px; font-size: 1rem;">Critical</span>
                                                            @endif
                                                            <small>{{ \App\Models\NotificationSetting::getDescription($type) }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="text-align: center;">
                                                    <label class="custom-switch">
                                                        <input type="checkbox"
                                                            name="notifications[{{ $type }}][email_enabled]"
                                                            {{ $notificationSettings[$type]->email_enabled ? 'checked' : '' }}>
                                                        <span class="switch-slider"></span>
                                                    </label>
                                                </td>
                                                <td style="text-align: center;">
                                                    <label class="custom-switch">
                                                        <input type="checkbox"
                                                            name="notifications[{{ $type }}][sms_enabled]"
                                                            {{ $notificationSettings[$type]->sms_enabled ? 'checked' : '' }}>
                                                        <span class="switch-slider"></span>
                                                    </label>
                                                </td>
                                                <td style="text-align: center;">
                                                    <label class="custom-switch">
                                                        <input type="checkbox"
                                                            name="notifications[{{ $type }}][in_app_enabled]"
                                                            {{ $notificationSettings[$type]->in_app_enabled ? 'checked' : '' }}>
                                                        <span class="switch-slider"></span>
                                                    </label>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- System Info (read-only for admins) --}}
                            <div class="setting-section" style="margin-top: 25px;">
                                <h4 class="setting-section-title">System Information</h4>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div style="color: #6c757d;">School Name</div>
                                        <div style="font-weight: 600;">{{ $systemInfo['school_name'] }}</div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div style="color: #6c757d;">Email System</div>
                                        <div style="font-weight: 600; color: {{ $systemInfo['email_enabled'] ? '#28a745' : '#dc3545' }};">
                                            {{ $systemInfo['email_enabled'] ? 'Enabled' : 'Disabled' }}
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div style="color: #6c757d;">SMS System</div>
                                        <div style="font-weight: 600; color: {{ $systemInfo['sms_enabled'] ? '#28a745' : '#dc3545' }};">
                                            {{ $systemInfo['sms_enabled'] ? 'Enabled' : 'Disabled' }}
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted" style="display: block; margin-top: 10px;">
                                    <i class="ti-info-alt"></i> System-wide notification settings are managed by the SuperAdmin.
                                </small>
                            </div>

                            {{-- Save Button --}}
                            <div class="d-flex justify-content-end" style="margin-top: 20px;">
                                <button type="submit" class="btn btn-save-settings">
                                    <i class="ti-save"></i> Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
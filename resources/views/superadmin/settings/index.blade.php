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
            font-size: 24px;
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

        .logo-preview {
            max-height: 80px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            padding: 5px;
            background-color: #fff;
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

        .required-field::after {
            content: " *";
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
                                <h1>System Settings</h1>
                            </div>
                        </div>
                        <span>Configure and manage system-wide settings</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
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
                                    <div class="stat-icon dib"><i class="ti-settings color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Settings</div>
                                        <div class="stat-digit">{{ $stats['total_settings'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-email color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Email Status</div>
                                        <div class="stat-value {{ $stats['email_enabled'] ? 'status-enabled' : 'status-disabled' }}">
                                            {{ $stats['email_enabled'] ? 'Enabled' : 'Disabled' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-comment-alt color-pink border-pink"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">SMS Status</div>
                                        <div class="stat-value {{ $stats['sms_enabled'] ? 'status-enabled' : 'status-disabled' }}">
                                            {{ $stats['sms_enabled'] ? 'Enabled' : 'Disabled' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-time color-warning border-warning"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Last Updated</div>
                                        <div class="stat-digit">{{ $stats['last_updated'] ? $stats['last_updated']->diffForHumans() : 'Never' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                      
                    </div>

                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="row mt-3">
                            <div class="col-lg-12">
                                <div class="alert alert-success alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <i class="ti-check"></i> {{ session('success') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="row mt-3">
                            <div class="col-lg-12">
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <i class="ti-alert"></i> {{ session('error') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Settings Form -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <form method="POST" action="{{ route('superadmin.settings.update') }}" enctype="multipart/form-data">
                                @csrf
                                
                                <div class="card alert">                                         
                                    <div class="card-body">
                                        <div class="custom-tab">
                                            <ul class="nav nav-tabs" role="tablist">
                                                <li role="presentation" class="active"><a href="#school-tab" aria-controls="school-tab" role="tab" data-toggle="tab"><i class="ti-home"></i> School Information</a></li>
                                                <li role="presentation"><a href="#system-tab" aria-controls="system-tab" role="tab" data-toggle="tab"><i class="ti-settings"></i> System Settings</a></li>
                                                <li role="presentation"><a href="#notifications-tab" aria-controls="notifications-tab" role="tab" data-toggle="tab"><i class="ti-bell"></i> Notifications</a></li>
                                                <li role="presentation"><a href="#academic-tab" aria-controls="academic-tab" role="tab" data-toggle="tab"><i class="ti-book"></i> Academic Settings</a></li>
                                            </ul>
                                            <div class="tab-content">
                                                <!-- School Information Tab -->
                                                <div role="tabpanel" class="tab-pane active" id="school-tab">
                                                        <h3 style="font-weight: 600; margin-bottom: 25px;">School Information</h3>                                                       
                                                        <div class="row">
                                                            <!-- School Name -->
                                                            <div class="col-lg-12">
                                                                <div class="form-group">
                                                                    <label class="required-field">School Name</label>
                                                                    <input 
                                                                        type="text" 
                                                                        name="school_name" 
                                                                        value="{{ old('school_name', $settings->get('school')->firstWhere('key', 'school_name')?->value) }}"
                                                                        required
                                                                        class="form-control @error('school_name') is-invalid @enderror"
                                                                    >
                                                                    @error('school_name')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <!-- School Email -->
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label class="required-field">School Email</label>
                                                                    <input 
                                                                        type="email" 
                                                                        name="school_email" 
                                                                        value="{{ old('school_email', $settings->get('school')->firstWhere('key', 'school_email')?->value) }}"
                                                                        required
                                                                        class="form-control @error('school_email') is-invalid @enderror"
                                                                    >
                                                                    @error('school_email')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <!-- School Phone -->
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label class="required-field">School Phone</label>
                                                                    <input 
                                                                        type="text" 
                                                                        name="school_phone" 
                                                                        value="{{ old('school_phone', $settings->get('school')->firstWhere('key', 'school_phone')?->value) }}"
                                                                        placeholder="+44..."
                                                                        required
                                                                        class="form-control @error('school_phone') is-invalid @enderror"
                                                                    >
                                                                    @error('school_phone')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <!-- School Address -->
                                                            <div class="col-lg-12">
                                                                <div class="form-group">
                                                                    <label class="required-field">School Address</label>
                                                                    <textarea 
                                                                        name="school_address" 
                                                                        rows="3"
                                                                        required
                                                                        class="form-control @error('school_address') is-invalid @enderror"
                                                                    >{{ old('school_address', $settings->get('school')->firstWhere('key', 'school_address')?->value) }}</textarea>
                                                                    @error('school_address')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <!-- School Logo -->
                                                            <div class="col-lg-12">
                                                                <div class="form-group">
                                                                    <label>School Logo</label>
                                                                    @php
                                                                        $currentLogo = $settings->get('school')->firstWhere('key', 'school_logo')?->value;
                                                                    @endphp
                                                                    @if($currentLogo)
                                                                    <div style="margin-bottom: 15px;">
                                                                        <img src="{{ asset('storage/' . $currentLogo) }}" alt="Current Logo" class="logo-preview">
                                                                        <p style="font-size: 0.875rem; color: #6c757d; margin-top: 5px;">Current logo</p>
                                                                    </div>
                                                                    @endif
                                                                    <input 
                                                                        type="file" 
                                                                        name="school_logo" 
                                                                        accept="image/*"
                                                                        class="form-control-file @error('school_logo') is-invalid @enderror"
                                                                    >
                                                                    <small class="form-text text-muted">Max size: 2MB. Formats: JPG, PNG, GIF</small>
                                                                    @error('school_logo')
                                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                </div>
                                                <!-- System Settings Tab -->
                                                <div role="tabpanel" class="tab-pane" id="system-tab">
                                                        <h3 style="font-weight: 600; margin-bottom: 25px;">System Settings</h3>                                                       
                                                        <div class="row">
                                                            <!-- Max Class Capacity -->
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label class="required-field">Maximum Class Capacity</label>
                                                                    <input 
                                                                        type="number" 
                                                                        name="max_class_capacity" 
                                                                        value="{{ old('max_class_capacity', $settings->get('system')->firstWhere('key', 'max_class_capacity')?->value ?? 20) }}"
                                                                        min="1"
                                                                        max="100"
                                                                        required
                                                                        class="form-control @error('max_class_capacity') is-invalid @enderror"
                                                                    >
                                                                    <small class="form-text text-muted">Default maximum students per class</small>
                                                                    @error('max_class_capacity')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <!-- Term Start Date -->
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label class="required-field">Current Term Start Date</label>
                                                                    <input 
                                                                        type="date" 
                                                                        name="term_start_date" 
                                                                        value="{{ old('term_start_date', $settings->get('system')->firstWhere('key', 'term_start_date')?->value) }}"
                                                                        required
                                                                        class="form-control @error('term_start_date') is-invalid @enderror"
                                                                    >
                                                                    @error('term_start_date')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <!-- Term End Date -->
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label class="required-field">Current Term End Date</label>
                                                                    <input 
                                                                        type="date" 
                                                                        name="term_end_date" 
                                                                        value="{{ old('term_end_date', $settings->get('system')->firstWhere('key', 'term_end_date')?->value) }}"
                                                                        required
                                                                        class="form-control @error('term_end_date') is-invalid @enderror"
                                                                    >
                                                                    @error('term_end_date')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <!-- Timezone -->
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label>Timezone</label>
                                                                    <select name="timezone" class="form-control">
                                                                        <option value="Europe/London" {{ old('timezone', $settings->get('system')->firstWhere('key', 'timezone')?->value ?? 'Europe/London') === 'Europe/London' ? 'selected' : '' }}>
                                                                            Europe/London (GMT/BST)
                                                                        </option>
                                                                        <option value="UTC" {{ old('timezone', $settings->get('system')->firstWhere('key', 'timezone')?->value) === 'UTC' ? 'selected' : '' }}>
                                                                            UTC
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <!-- Date Format -->
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label>Date Format</label>
                                                                    <select name="date_format" class="form-control">
                                                                        <option value="d/m/Y" {{ old('date_format', $settings->get('system')->firstWhere('key', 'date_format')?->value ?? 'd/m/Y') === 'd/m/Y' ? 'selected' : '' }}>
                                                                            DD/MM/YYYY ({{ date('d/m/Y') }})
                                                                        </option>
                                                                        <option value="m/d/Y" {{ old('date_format', $settings->get('system')->firstWhere('key', 'date_format')?->value) === 'm/d/Y' ? 'selected' : '' }}>
                                                                            MM/DD/YYYY ({{ date('m/d/Y') }})
                                                                        </option>
                                                                        <option value="Y-m-d" {{ old('date_format', $settings->get('system')->firstWhere('key', 'date_format')?->value) === 'Y-m-d' ? 'selected' : '' }}>
                                                                            YYYY-MM-DD ({{ date('Y-m-d') }})
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <!-- Time Format -->
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label>Time Format</label>
                                                                    <select name="time_format" class="form-control">
                                                                        <option value="H:i" {{ old('time_format', $settings->get('system')->firstWhere('key', 'time_format')?->value ?? 'H:i') === 'H:i' ? 'selected' : '' }}>
                                                                            24 Hour ({{ date('H:i') }})
                                                                        </option>
                                                                        <option value="h:i A" {{ old('time_format', $settings->get('system')->firstWhere('key', 'time_format')?->value) === 'h:i A' ? 'selected' : '' }}>
                                                                            12 Hour ({{ date('h:i A') }})
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <!-- Maintenance Mode -->
                                                            <div class="col-lg-12">
                                                                <div class="form-group">
                                                                    <label class="toggle-label">
                                                                        <input 
                                                                            type="checkbox" 
                                                                            name="maintenance_mode" 
                                                                            value="1"
                                                                            id="maintenance_mode"
                                                                            {{ old('maintenance_mode', $settings->get('system')->firstWhere('key', 'maintenance_mode')?->value) ? 'checked' : '' }}
                                                                        >
                                                                        <div class="toggle-description">
                                                                            <span style="font-weight: 500;">Enable Maintenance Mode</span>
                                                                            <p style="font-size: 1rem; color: #6c757d; margin: 5px 0 0 0;">When enabled, only administrators can access the system</p>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            </div>

                                                            <!-- Maintenance Message -->
                                                            <div class="col-lg-12">
                                                                <div class="form-group">
                                                                    <label>Maintenance Message</label>
                                                                    <textarea 
                                                                        name="maintenance_message" 
                                                                        rows="3"
                                                                        placeholder="System is undergoing maintenance. Please check back later."
                                                                        class="form-control"
                                                                    >{{ old('maintenance_message', $settings->get('system')->firstWhere('key', 'maintenance_message')?->value) }}</textarea>
                                                                    <small class="form-text text-muted">Message shown to users when maintenance mode is enabled</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    
                                                </div>
                                                <!-- Notifications Tab -->
                                                <div role="tabpanel" class="tab-pane" id="notifications-tab">
                                                        <h3 style="font-weight: 600; margin-bottom: 25px;">Notification Settings</h3>
                                                        <!-- Email Notifications -->
                                                        <div class="setting-section">
                                                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                                                                <div>
                                                                    <h4 class="setting-section-title">Email Notifications</h4>
                                                                    <p style="font-size: 1.2rem; color: #6c757d;">Configure email notification settings</p>
                                                                </div>
                                                                <label class="toggle-label">
                                                                    <input 
                                                                        type="checkbox" 
                                                                        name="email_enabled" 
                                                                        value="1"
                                                                        {{ old('email_enabled', $settings->get('notifications')->firstWhere('key', 'email_enabled')?->value) ? 'checked' : '' }}
                                                                    >
                                                                    <span style="margin-left: 8px; font-weight: 500;">Enable Email</span>
                                                                </label>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <div class="form-group">
                                                                        <label class="required-field">Admin Notification Email</label>
                                                                        <input 
                                                                            type="email" 
                                                                            name="admin_notification_email" 
                                                                            value="{{ old('admin_notification_email', $settings->get('notifications')->firstWhere('key', 'admin_notification_email')?->value) }}"
                                                                            required
                                                                            class="form-control @error('admin_notification_email') is-invalid @enderror"
                                                                        >
                                                                        <small class="form-text text-muted">Email for receiving system notifications</small>
                                                                        @error('admin_notification_email')
                                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- SMS Notifications -->
                                                        <div class="setting-section">
                                                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                                                                <div>
                                                                    <h4 class="setting-section-title">SMS Notifications</h4>
                                                                    <p style="font-size: 1.2rem; color: #6c757d;">Configure SMS notification settings (Twilio)</p>
                                                                </div>
                                                                <label class="toggle-label">
                                                                    <input 
                                                                        type="checkbox" 
                                                                        name="sms_enabled" 
                                                                        value="1"
                                                                        {{ old('sms_enabled', $settings->get('notifications')->firstWhere('key', 'sms_enabled')?->value) ? 'checked' : '' }}
                                                                    >
                                                                    <span style="margin-left: 8px; font-weight: 500;">Enable SMS</span>
                                                                </label>
                                                            </div>

                                                            <div class="info-box">
                                                                <i class="ti-info-alt"></i>
                                                                <div>
                                                                    <p style="font-size: 1.2rem; font-weight: 500; margin-bottom: 5px;">SMS Provider Configuration</p>
                                                                    <p style="font-size: 1.2rem; margin: 0;">SMS credentials are configured in your .env file. Provider: Twilio</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    
                                                </div>
                                                <!-- Academic Settings Tab -->
                                                <div role="tabpanel" class="tab-pane" id="academic-tab">
                                                        <h3 style="font-weight: 600; margin-bottom: 25px;">Academic Settings</h3>                                                        
                                                        <!-- Attendance Settings -->
                                                        <div class="setting-section">
                                                            <h4 class="setting-section-title">Attendance Settings</h4>
                                                            
                                                            <label class="toggle-label">
                                                                <input 
                                                                    type="checkbox" 
                                                                    name="attendance_required" 
                                                                    value="1"
                                                                    {{ old('attendance_required', $settings->get('academic')->firstWhere('key', 'attendance_required')?->value) ? 'checked' : '' }}
                                                                >
                                                                <div class="toggle-description">
                                                                    <span style="font-weight: 500;">Require Attendance Marking</span>
                                                                    <p style="font-size: 1.2rem; color: #6c757d; margin: 5px 0 0 0;">Teachers must mark attendance for each class session</p>
                                                                </div>
                                                            </label>
                                                        </div>

                                                        <!-- Homework Settings -->
                                                        <div class="setting-section">
                                                            <h4 class="setting-section-title">Homework Settings</h4>
                                                            
                                                            <div style="margin-bottom: 20px;">
                                                                <label class="toggle-label">
                                                                    <input 
                                                                        type="checkbox" 
                                                                        name="late_homework_penalty" 
                                                                        value="1"
                                                                        {{ old('late_homework_penalty', $settings->get('academic')->firstWhere('key', 'late_homework_penalty')?->value) ? 'checked' : '' }}
                                                                    >
                                                                    <div class="toggle-description">
                                                                        <span style="font-weight: 500;">Apply Late Homework Penalty</span>
                                                                        <p style="font-size: 1.2rem; color: #6c757d; margin: 5px 0 0 0;">Mark homework as "late" if submitted after due date</p>
                                                                    </div>
                                                                </label>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-lg-4">
                                                                    <div class="form-group">
                                                                        <label>Default Homework Due Days</label>
                                                                        <input 
                                                                            type="number" 
                                                                            name="homework_due_days" 
                                                                            value="{{ old('homework_due_days', $settings->get('academic')->firstWhere('key', 'homework_due_days')?->value ?? 7) }}"
                                                                            min="1"
                                                                            max="30"
                                                                            class="form-control"
                                                                        >
                                                                        <small class="form-text text-muted">Default number of days until homework is due</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Progress Report Settings -->
                                                        <div class="setting-section">
                                                            <h4 class="setting-section-title">Progress Report Settings</h4>
                                                            
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <div class="form-group">
                                                                        <label>Progress Report Frequency</label>
                                                                        <select name="progress_report_frequency" class="form-control">
                                                                            <option value="weekly" {{ old('progress_report_frequency', $settings->get('academic')->firstWhere('key', 'progress_report_frequency')?->value) === 'weekly' ? 'selected' : '' }}>
                                                                                Weekly
                                                                            </option>
                                                                            <option value="biweekly" {{ old('progress_report_frequency', $settings->get('academic')->firstWhere('key', 'progress_report_frequency')?->value) === 'biweekly' ? 'selected' : '' }}>
                                                                                Bi-weekly
                                                                            </option>
                                                                            <option value="monthly" {{ old('progress_report_frequency', $settings->get('academic')->firstWhere('key', 'progress_report_frequency')?->value ?? 'monthly') === 'monthly' ? 'selected' : '' }}>
                                                                                Monthly
                                                                            </option>
                                                                            <option value="quarterly" {{ old('progress_report_frequency', $settings->get('academic')->firstWhere('key', 'progress_report_frequency')?->value) === 'quarterly' ? 'selected' : '' }}>
                                                                                Quarterly
                                                                            </option>
                                                                        </select>
                                                                        <small class="form-text text-muted">How often progress reports should be generated</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    
                                                </div>
                                            </div>

                                            <!-- Form Actions -->
                                            <div class="form-actions">
                                                <button type="reset" class="btn btn-secondary">
                                                    <i class="ti-reload"></i> Reset Changes
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ti-check"></i> Save Settings
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>                            
                            </form>
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

@push('scripts')
    <script>
        $(document).ready(function() {

            // Maintenance mode confirmation
            $('#maintenance_mode').on('change', function() {
                if (this.checked) {
                    if (!confirm('Are you sure you want to enable maintenance mode? Only administrators will be able to access the system.')) {
                        this.checked = false;
                    }
                }
            });
        });
    </script>
@endpush
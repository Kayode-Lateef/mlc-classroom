@extends('layouts.app')

@section('title', 'System Settings')

@push('styles')
    <style>
        /* ============================================ */
        /* MLC SETTINGS STYLES                          */
        /* Colours: #3386f7 (blue) and #e06829 (orange) */
        /* ============================================ */

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

        .nav-tabs .nav-link {
            font-weight: 500;
            color: #4a5568;
            padding: 12px 20px;
            border: none;
            border-bottom: 3px solid transparent;
            font-size: 0.95rem;
        }

        .nav-tabs .nav-link:hover {
            color: #3386f7;
            border-bottom-color: rgba(51,134,247,0.3);
        }

        .nav-tabs .nav-link.active {
            color: #3386f7;
            border-bottom-color: #3386f7;
            background: transparent;
        }

        .nav-tabs .nav-link i {
            margin-right: 6px;
        }

        .toggle-label {
            display: flex;
            align-items: flex-start;
            cursor: pointer;
        }

        .toggle-label input[type="checkbox"] {
            margin-top: 6px;
            width: 18px;
            height: 18px;
            accent-color: #3386f7;
        }

        .toggle-description {
            margin-left: 10px;
        }

        .toggle-description span {
            font-weight: 500;
            font-size: 0.95rem;
        }

        .toggle-description p {
            font-size: 0.85rem;
            color: #6c757d;
            margin: 3px 0 0;
        }

        .required-field::after {
            content: " *";
            color: #dc3545;
        }

        .logo-preview {
            max-width: 200px;
            max-height: 80px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 5px;
        }

        .info-box {
            background: rgba(51,134,247,0.06);
            border: 1px solid rgba(51,134,247,0.15);
            border-radius: 8px;
            padding: 15px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .info-box i {
            color: #3386f7;
            font-size: 1.1rem;
            margin-top: 2px;
        }

        .info-box p {
            font-size: 0.9rem;
            margin: 0;
            color: #4a5568;
        }

        .btn-save-settings {
            background: linear-gradient(135deg, #3386f7 0%, #2a6fd6 100%);
            border: none;
            color: #fff;
            padding: 12px 35px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .btn-save-settings:hover {
            background: linear-gradient(135deg, #2a6fd6 0%, #1e5bb5 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(51,134,247,0.3);
        }

        .income-preview {
            background: rgba(224,104,41,0.06);
            border: 1px solid rgba(224,104,41,0.15);
            border-radius: 8px;
            padding: 15px;
        }

        .income-preview h5 {
            color: #e06829;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-control, .form-control-file {
            font-size: 0.9rem;
        }

        .form-group label {
            font-weight: 500;
            font-size: 0.9rem;
            color: #2d3748;
        }

        small.form-text {
            font-size: 0.8rem;
        }
    </style>
@endpush

{{-- ================================================================ --}}
{{-- BOOLEAN CHECKBOX FIX                                             --}}
{{--                                                                  --}}
{{-- The database stores booleans as strings: 'true' / 'false'        --}}
{{-- PHP treats the string 'false' as TRUTHY (non-empty string).      --}}
{{-- This isChecked() helper uses filter_var() which correctly maps:  --}}
{{--   'true'  → true    'false' → false                             --}}
{{--   '1'     → true    '0'     → false                             --}}
{{--   1       → true    0       → false                             --}}
{{--                                                                  --}}
{{-- It also respects old() input after validation failures.          --}}
{{-- ================================================================ --}}
@php
    function isChecked($key, $settingsMap) {
        // old() returns null if the field wasn't in the previous request,
        // but for checkboxes we need to check if old input EXISTS at all.
        // If the form was submitted (old('_token') exists), use old() value.
        // Otherwise, use the database value from settingsMap.
        if (old('_token') !== null) {
            // Form was submitted — old() is available
            // For checkboxes: old($key) will be '1' if checked, null if unchecked
            return old($key) !== null && old($key) !== '' && old($key) !== '0';
        }

        // No form submission — use database value
        $value = $settingsMap[$key] ?? false;
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
@endphp

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

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="ti-alert"></i> Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif


                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-settings color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Settings</div>
                                        <div class="stat-value">{{ $stats['total_settings'] }}</div>
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
                                        <div class="stat-value">{{ $stats['last_updated'] ? $stats['last_updated']->diffForHumans() : 'Never' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib">
                                        <i class="ti-email {{ $stats['email_enabled'] ? 'color-success border-success' : 'grey' }}"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Email Notifications</div>
                                        <div class="stat-value" style="font-weight: 600; color: {{ $stats['email_enabled'] ? '#28a745' : '#dc3545' }};">
                                            {{ $stats['email_enabled'] ? 'Enabled' : 'Disabled' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-comment-alt {{ $stats['sms_enabled'] ? 'color-pink border-pink' : 'grey' }}"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">SMS Notifications</div>
                                        <div class="stat-value  style="font-weight: 600; color: {{ $stats['sms_enabled'] ? '#28a745' : '#dc3545' }};">
                                            {{ $stats['sms_enabled'] ? 'Enabled' : 'Disabled' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                      
                    </div>

                {{-- Settings Form --}}
                <form method="POST" action="{{ route('superadmin.settings.update') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="card" style="border-radius: 8px;">
                        <div class="card-body" style="padding: 0;">

                            {{-- Tabs --}}
                            <ul class="nav nav-tabs" role="tablist" style="padding: 0 25px;">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#school-tab" role="tab">
                                        <i class="ti-home"></i> School Info
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#system-tab" role="tab">
                                        <i class="ti-settings"></i> System
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#notifications-tab" role="tab">
                                        <i class="ti-bell"></i> Notifications
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#academic-tab" role="tab">
                                        <i class="ti-book"></i> Academic
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content" style="padding: 25px;">

                                {{-- ========================================== --}}
                                {{-- SCHOOL INFORMATION TAB                      --}}
                                {{-- ========================================== --}}
                                <div class="tab-pane active" id="school-tab" role="tabpanel">
                                    <h3 style="font-weight: 600; margin-bottom: 25px;">School Information</h3>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label class="required-field">School Name</label>
                                                <input type="text" name="school_name"
                                                    value="{{ old('school_name', $settingsMap['school_name'] ?? '') }}"
                                                    required
                                                    class="form-control @error('school_name') is-invalid @enderror">
                                                @error('school_name')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="required-field">School Email</label>
                                                <input type="email" name="school_email"
                                                    value="{{ old('school_email', $settingsMap['school_email'] ?? '') }}"
                                                    required
                                                    class="form-control @error('school_email') is-invalid @enderror">
                                                @error('school_email')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="required-field">School Phone</label>
                                                <input type="text" name="school_phone"
                                                    value="{{ old('school_phone', $settingsMap['school_phone'] ?? '') }}"
                                                    required placeholder="+44 20 1234 5678"
                                                    class="form-control @error('school_phone') is-invalid @enderror">
                                                <small class="form-text text-muted">UK format: +44... or 0...</small>
                                                @error('school_phone')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label class="required-field">School Address</label>
                                                <textarea name="school_address" rows="3" required
                                                    class="form-control @error('school_address') is-invalid @enderror">{{ old('school_address', $settingsMap['school_address'] ?? '') }}</textarea>
                                                @error('school_address')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>School Logo</label>
                                                @if(!empty($settingsMap['school_logo']))
                                                    <div style="margin-bottom: 15px;">
                                                        <img src="{{ asset('storage/' . $settingsMap['school_logo']) }}" alt="Current Logo" class="logo-preview">
                                                        <p style="font-size: 0.8rem; color: #6c757d; margin-top: 5px;">Current logo</p>
                                                    </div>
                                                @endif
                                                <input type="file" name="school_logo" accept="image/*"
                                                    class="form-control-file @error('school_logo') is-invalid @enderror">
                                                <small class="form-text text-muted">Max size: 2MB. Formats: JPG, PNG, GIF</small>
                                                @error('school_logo')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ========================================== --}}
                                {{-- SYSTEM SETTINGS TAB                        --}}
                                {{-- ========================================== --}}
                                <div class="tab-pane" id="system-tab" role="tabpanel">
                                    <h3 style="font-weight: 600; margin-bottom: 25px;">System Settings</h3>

                                    <div class="setting-section">
                                        <h4 class="setting-section-title">Capacity & Term Dates</h4>
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label class="required-field">Maximum Class Capacity</label>
                                                    <input type="number" name="max_class_capacity"
                                                        value="{{ old('max_class_capacity', $settingsMap['max_class_capacity'] ?? 20) }}"
                                                        min="1" max="100" required
                                                        class="form-control @error('max_class_capacity') is-invalid @enderror">
                                                    @error('max_class_capacity')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label class="required-field">Term Start Date</label>
                                                    <input type="date" name="term_start_date"
                                                        value="{{ old('term_start_date', $settingsMap['term_start_date'] ?? '') }}"
                                                        required
                                                        class="form-control @error('term_start_date') is-invalid @enderror">
                                                    @error('term_start_date')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label class="required-field">Term End Date</label>
                                                    <input type="date" name="term_end_date"
                                                        value="{{ old('term_end_date', $settingsMap['term_end_date'] ?? '') }}"
                                                        required
                                                        class="form-control @error('term_end_date') is-invalid @enderror">
                                                    @error('term_end_date')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="setting-section">
                                        <h4 class="setting-section-title">Maintenance Mode</h4>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group" style="margin-bottom: 15px;">
                                                    <label class="toggle-label">
                                                        <input type="checkbox" name="maintenance_mode" value="1"
                                                            {{ old('maintenance_mode', $settingsMap['maintenance_mode'] ?? false) ? 'checked' : '' }}>
                                                        <div class="toggle-description">
                                                            <span>Enable Maintenance Mode</span>
                                                            <p>When enabled, only administrators can access the system</p>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label>Maintenance Message</label>
                                                    <textarea name="maintenance_message" rows="3" class="form-control"
                                                        placeholder="System is undergoing maintenance. Please check back later.">{{ old('maintenance_message', $settingsMap['maintenance_message'] ?? '') }}</textarea>
                                                    <small class="form-text text-muted">Message shown to users when maintenance mode is enabled</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ========================================== --}}
                                {{-- NOTIFICATIONS TAB                           --}}
                                {{-- ========================================== --}}
                                <div class="tab-pane" id="notifications-tab" role="tabpanel">
                                    <h3 style="font-weight: 600; margin-bottom: 25px;">Notification Settings</h3>

                                    <div class="setting-section">
                                        <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 15px;">
                                            <div>
                                                <h4 class="setting-section-title" style="margin-bottom: 5px;">Email Notifications</h4>
                                                <p style="font-size: 0.85rem; color: #6c757d;">Configure email notification settings</p>
                                            </div>
                                            <label class="toggle-label">
                                                <input type="checkbox" name="email_enabled" value="1"
                                                    {{ old('email_enabled', $settingsMap['email_enabled'] ?? false) ? 'checked' : '' }}>
                                                <span style="margin-left: 8px; font-weight: 500;">Enable Email</span>
                                            </label>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label class="required-field">Admin Notification Email</label>
                                                    <input type="email" name="admin_notification_email"
                                                        value="{{ old('admin_notification_email', $settingsMap['admin_notification_email'] ?? '') }}"
                                                        required
                                                        class="form-control @error('admin_notification_email') is-invalid @enderror">
                                                    <small class="form-text text-muted">Email for receiving system notifications</small>
                                                    @error('admin_notification_email')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="setting-section">
                                        <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 15px;">
                                            <div>
                                                <h4 class="setting-section-title" style="margin-bottom: 5px;">SMS Notifications</h4>
                                                <p style="font-size: 0.85rem; color: #6c757d;">Configure SMS notification settings</p>
                                            </div>
                                            <label class="toggle-label">
                                                <input type="checkbox" name="sms_enabled" value="1"
                                                    {{ old('sms_enabled', $settingsMap['sms_enabled'] ?? false) ? 'checked' : '' }}>
                                                <span style="margin-left: 8px; font-weight: 500;">Enable SMS</span>
                                            </label>
                                        </div>

                                        <div class="info-box">
                                            <i class="ti-info-alt"></i>
                                            <div>
                                                <p style="font-weight: 500; margin-bottom: 5px;">SMS Provider Configuration</p>
                                                <p>SMS credentials are configured in the <a href="{{ route('superadmin.sms-config.index') }}">SMS Configuration</a> page.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ========================================== --}}
                                {{-- ACADEMIC SETTINGS TAB                       --}}
                                {{-- ========================================== --}}
                                <div class="tab-pane" id="academic-tab" role="tabpanel">
                                    <h3 style="font-weight: 600; margin-bottom: 25px;">Academic Settings</h3>

                                    {{-- Hourly Rate --}}
                                    <div class="setting-section">
                                        <h4 class="setting-section-title">Income & Billing Settings</h4>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="hourly_rate" class="required-field">
                                                        <i class="ti-money"></i> Hourly Teaching Rate
                                                    </label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">&pound;</span>
                                                        </div>
                                                        <input type="number" id="hourly_rate" name="hourly_rate"
                                                            value="{{ old('hourly_rate', $settingsMap['hourly_rate'] ?? 50.00) }}"
                                                            min="0" max="1000" step="0.01" required
                                                            class="form-control @error('hourly_rate') is-invalid @enderror">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">/hour</span>
                                                        </div>
                                                        @error('hourly_rate')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        <i class="ti-info-alt"></i> Used for calculating income projections on the dashboard
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="income-preview" style="margin-top: 32px;">
                                                    <h5><i class="ti-bar-chart"></i> Income Preview</h5>
                                                    <p style="margin: 0 0 8px; font-size: 0.85rem;">Based on current hourly rate:</p>
                                                    <ul style="margin: 0; padding-left: 18px; font-size: 0.85rem;">
                                                        <li><strong>1 hour/week</strong> = &pound;<span id="preview-1hr">0.00</span>/month</li>
                                                        <li><strong>2 hours/week</strong> = &pound;<span id="preview-2hr">0.00</span>/month</li>
                                                        <li><strong>5 hours/week</strong> = &pound;<span id="preview-5hr">0.00</span>/month</li>
                                                    </ul>
                                                    <small class="text-muted"><i class="ti-info-alt"></i> Monthly = rate &times; hours &times; 4.33 weeks</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Attendance Settings --}}
                                    <div class="setting-section">
                                        <h4 class="setting-section-title">Attendance Settings</h4>
                                        <label class="toggle-label">
                                            <input type="checkbox" name="attendance_required" value="1"
                                                {{ old('attendance_required', $settingsMap['attendance_required'] ?? false) ? 'checked' : '' }}>
                                            <div class="toggle-description">
                                                <span>Require Attendance Marking</span>
                                                <p>When enabled, teachers must mark attendance for each class session</p>
                                            </div>
                                        </label>
                                    </div>

                                    {{-- Homework Settings --}}
                                    <div class="setting-section">
                                        <h4 class="setting-section-title">Homework Settings</h4>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <label class="toggle-label" style="margin-bottom: 20px;">
                                                    <input type="checkbox" name="late_homework_penalty" value="1"
                                                        {{ old('late_homework_penalty', $settingsMap['late_homework_penalty'] ?? false) ? 'checked' : '' }}>
                                                    <div class="toggle-description">
                                                        <span>Apply Late Homework Penalty</span>
                                                        <p>Flag homework submitted after the due date</p>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label>Default Homework Due Days</label>
                                                    <input type="number" name="homework_due_days"
                                                        value="{{ old('homework_due_days', $settingsMap['homework_due_days'] ?? 7) }}"
                                                        min="1" max="30" class="form-control">
                                                    <small class="form-text text-muted">Default number of days until homework is due</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Progress Report Settings --}}
                                    <div class="setting-section">
                                        <h4 class="setting-section-title">Progress Report Settings</h4>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label>Progress Report Frequency</label>
                                                    <select name="progress_report_frequency" class="form-control">
                                                        @php $currentFreq = old('progress_report_frequency', $settingsMap['progress_report_frequency'] ?? 'monthly'); @endphp
                                                        <option value="weekly" {{ $currentFreq === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                        <option value="biweekly" {{ $currentFreq === 'biweekly' ? 'selected' : '' }}>Bi-weekly</option>
                                                        <option value="monthly" {{ $currentFreq === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                        <option value="termly" {{ $currentFreq === 'termly' ? 'selected' : '' }}>Termly</option>
                                                    </select>
                                                    <small class="form-text text-muted">How often progress reports should be generated</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>{{-- end tab-content --}}

                            {{-- Save Button --}}
                            <div style="padding: 20px 25px; border-top: 1px solid #e9ecef; background: #f8f9fa; border-radius: 0 0 8px 8px;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted"><i class="ti-info-alt"></i> Changes take effect immediately across the platform.</small>
                                    <button type="submit" class="btn btn-save-settings">
                                        <i class="ti-save"></i> Save All Settings
                                    </button>
                                </div>
                            </div>

                        </div>{{-- end card-body --}}
                    </div>{{-- end card --}}
                </form>

            </div>{{-- end container-fluid --}}
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // ============================================
    // Live Income Preview Calculator
    // ============================================
    (function() {
        const rateInput = document.getElementById('hourly_rate');
        const preview1 = document.getElementById('preview-1hr');
        const preview2 = document.getElementById('preview-2hr');
        const preview5 = document.getElementById('preview-5hr');

        function updatePreview() {
            const rate = parseFloat(rateInput.value) || 0;
            preview1.textContent = (rate * 1 * 4.33).toFixed(2);
            preview2.textContent = (rate * 2 * 4.33).toFixed(2);
            preview5.textContent = (rate * 5 * 4.33).toFixed(2);
        }

        rateInput.addEventListener('input', updatePreview);
        // Run on page load
        updatePreview();
    })();

    // ============================================
    // Preserve active tab on page reload
    // ============================================
    (function() {
        const activeTab = localStorage.getItem('settingsActiveTab');
        if (activeTab) {
            const tab = document.querySelector('a[href="' + activeTab + '"]');
            if (tab) tab.click();
        }

        document.querySelectorAll('.nav-tabs .nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                localStorage.setItem('settingsActiveTab', this.getAttribute('href'));
            });
        });
    })();

    // ============================================
    // Unsaved changes warning
    // ============================================
    (function() {
        let formChanged = false;
        const form = document.querySelector('form');

        form.addEventListener('change', function() { formChanged = true; });
        form.addEventListener('input', function() { formChanged = true; });
        form.addEventListener('submit', function() { formChanged = false; });

        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    })();
</script>
@endpush
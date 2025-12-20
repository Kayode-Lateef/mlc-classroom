@extends('layouts.app')

@section('title', 'Edit Permission')

@push('styles')
    <style>
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .required-field::after {
            content: " *";
            color: #dc3545;
        }

        .warning-box {
            background-color: #fff3cd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .warning-box i {
            color: #ffc107;
            font-size: 1.2rem;
        }

        .role-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .role-item:hover {
            background-color: #f8f9fa;
        }

        .role-item input[type="checkbox"] {
            margin-right: 15px;
        }

        .role-info {
            flex: 1;
        }

        .role-name {
            font-weight: 500;
            color: #212529;
            text-transform: capitalize;
        }

        .role-meta {
            display: flex;
            align-items: center;
            font-size: 1rem;
            color: #6c757d;
            margin-top: 5px;
        }

        .role-meta i {
            margin-right: 5px;
        }

        .system-role-badge {
            background-color: #e7f3ff;
            border: 1px solid #0066cc;
            color: #0066cc;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            display: inline-block;
            margin-top: 5px;
        }

        .sidebar-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            position: sticky;
            top: 20px;
        }

        .info-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .info-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .info-label {
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: #212529;
        }

        .info-value-large {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
        }

        .current-role-item {
            display: flex;
            align-items: center;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .current-role-item i {
            color: #007bff;
            margin-right: 8px;
        }

        .sidebar-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .sidebar-title {
            font-weight: 600;
            color: #212529;
            margin-bottom: 12px;
        }

        code {
            background-color: #f8f9fa;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
            color: #0066cc;
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
                                <h1>Edit Permission</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.permissions.index') }}">Permissions</a></li>
                                    <li><a href="{{ route('superadmin.permissions.show', $permission) }}">{{ ucwords($permission->name) }}</a></li>
                                    <li class="active">Edit</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <div class="row">
                        <!-- Main Form Column -->
                        <div class="col-md-8">
                            <div class="card alert">
                                <form method="POST" action="{{ route('superadmin.permissions.update', $permission) }}">
                                        @csrf
                                        @method('PUT')

                                        <!-- Permission Name -->
                                        <div class="form-group">
                                            <label for="name" class="required-field">Permission Name</label>
                                            <input 
                                                type="text" 
                                                name="name" 
                                                id="name" 
                                                value="{{ old('name', $permission->name) }}" 
                                                required
                                                class="form-control @error('name') is-invalid @enderror"
                                            >
                                            <small class="form-text text-muted">
                                                <i class="ti-info-alt"></i> Use format: <code>action module</code>
                                            </small>
                                            @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Assign to Roles -->
                                        <div class="form-group">
                                            <label class="font-weight-bold mb-3">
                                                <i class="ti-shield"></i> Assign to Roles (Optional)
                                            </label>
                                            
                                            @if($roles->count() > 0)
                                                <div>
                                                    @foreach($roles as $role)
                                                    <label class="role-item">
                                                        <input 
                                                            type="checkbox" 
                                                            name="roles[]" 
                                                            value="{{ $role->id }}"
                                                            {{ in_array($role->id, old('roles', $permissionRoles)) ? 'checked' : '' }}
                                                        >
                                                        <div class="role-info">
                                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                                <span class="role-name">{{ str_replace('_', ' ', $role->name) }}</span>
                                                                <div class="role-meta">
                                                                    <i class="ti-user"></i>
                                                                    {{ $role->users->count() }} users
                                                                </div>
                                                            </div>
                                                            @if(in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent']))
                                                                <span class="system-role-badge">System Role</span>
                                                            @endif
                                                        </div>
                                                    </label>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="alert alert-warning">
                                                    <i class="ti-alert"></i> No roles available.
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Warning Box -->
                                        <div class="warning-box">
                                            <div class="d-flex">
                                                <i class="ti-alert mr-3"></i>
                                                <div>
                                                    <strong>Important</strong>
                                                    <p class="mb-0 mt-2">
                                                        Changing this permission will affect all users who have roles with this permission assigned. 
                                                        Make sure you understand the implications before saving.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('superadmin.permissions.show', $permission) }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti-check"></i> Update Permission
                                            </button>
                                        </div>
                                    </form>
                            </div>
                        </div>

                        <!-- Sidebar Column -->
                        <div class="col-lg-4">
                            <div class="card">                              
                                <div class="card-header pr">
                                    <h4><i class="ti-info-alt"></i> Current Information</h4>                                                                     
                                </div>
                                <div class="sidebar-card">
                                   
                                    <div class="info-item">
                                        <div class="info-label">Permission Name</div>
                                        <div class="info-value">{{ ucwords($permission->name) }}</div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">Guard</div>
                                        <div class="info-value">{{ $permission->guard_name }}</div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">Assigned Roles</div>
                                        <div class="info-value-large">{{ $permission->roles->count() }}</div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">Created</div>
                                        <div class="info-value">{{ $permission->created_at->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $permission->created_at->diffForHumans() }}</small>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">Last Updated</div>
                                        <div class="info-value">{{ $permission->updated_at->format('d M Y') }}</div>
                                    </div>

                                    <!-- Current Roles Section -->
                                    @if($permission->roles->count() > 0)
                                    <div class="sidebar-section">
                                        <div class="sidebar-title">Currently Assigned To:</div>
                                        <div>
                                            @foreach($permission->roles as $role)
                                            <div class="current-role-item">
                                                <i class="ti-check"></i>
                                                <span style="font-size: 1rem; font-weight: 500; text-transform: capitalize;">
                                                    {{ str_replace('_', ' ', $role->name) }}
                                                </span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Impact Notice -->
                                    <div class="sidebar-section">
                                        <div class="sidebar-title">
                                            <i class="ti-help-alt"></i> Impact Notice
                                        </div>
                                        <p style="font-size: 1rem; color: #6c757d; margin: 0;">
                                            This permission affects 
                                            <strong>{{ $permission->roles->sum(function($role) { return $role->users->count(); }) }} users</strong> 
                                            across {{ $permission->roles->count() }} role(s).
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Edit Permission</p>
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
            // Track original selections for change detection
            const originalRoles = [];
            $('input[name="roles[]"]:checked').each(function() {
                originalRoles.push($(this).val());
            });

            // Click anywhere on role item to toggle checkbox
            $('.role-item').on('click', function(e) {
                if (e.target.tagName !== 'INPUT') {
                    const checkbox = $(this).find('input[type="checkbox"]');
                    checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
                }
            });

            // Track changes
            let hasChanges = false;
            $('input[name="roles[]"]').on('change', function() {
                hasChanges = true;
                updateChangeIndicator();
            });

            $('#name').on('input', function() {
                hasChanges = true;
            });

            // Update change indicator
            function updateChangeIndicator() {
                const currentRoles = [];
                $('input[name="roles[]"]:checked').each(function() {
                    currentRoles.push($(this).val());
                });

                // Check if roles have changed
                const rolesChanged = JSON.stringify(originalRoles.sort()) !== JSON.stringify(currentRoles.sort());
                
                if (rolesChanged) {
                    if (!$('#changes-indicator').length) {
                        $('.warning-box').before(
                            '<div id="changes-indicator" class="alert alert-info">' +
                            '<i class="ti-info-alt"></i> You have unsaved changes to role assignments.' +
                            '</div>'
                        );
                    }
                } else {
                    $('#changes-indicator').remove();
                }
            }

            // Warn about unsaved changes
            $(window).on('beforeunload', function() {
                if (hasChanges) {
                    return 'You have unsaved changes. Are you sure you want to leave?';
                }
            });

            $('form').on('submit', function() {
                hasChanges = false; // Don't show warning on form submit
            });

            // Show affected users count when selecting roles
            $('input[name="roles[]"]').on('change', function() {
                updateAffectedUsers();
            });

            function updateAffectedUsers() {
                let totalUsers = 0;
                let totalRoles = 0;

                $('input[name="roles[]"]:checked').each(function() {
                    const roleItem = $(this).closest('.role-item');
                    const userCount = parseInt(roleItem.find('.role-meta').text().match(/\d+/)[0]);
                    totalUsers += userCount;
                    totalRoles++;
                });

                if (totalRoles > 0) {
                    if (!$('#affected-users').length) {
                        $('.warning-box').after(
                            '<div id="affected-users" class="alert alert-info">' +
                            '<i class="ti-user"></i> ' +
                            '<strong>Impact:</strong> <span id="affected-count"></span>' +
                            '</div>'
                        );
                    }
                    
                    $('#affected-count').text(
                        `This permission will affect ${totalUsers} user(s) across ${totalRoles} role(s).`
                    );
                } else {
                    $('#affected-users').remove();
                }
            }

            // Initial affected users count
            updateAffectedUsers();

            // Form validation
            $('form').on('submit', function(e) {
                const permissionName = $('#name').val().trim();
                
                if (!permissionName) {
                    e.preventDefault();
                    alert('Please enter a permission name.');
                    $('#name').focus();
                    return false;
                }

                // Confirm if removing all roles
                const selectedRoles = $('input[name="roles[]"]:checked').length;
                if (originalRoles.length > 0 && selectedRoles === 0) {
                    const confirm = window.confirm(
                        'Warning: You are removing this permission from all roles.\n\n' +
                        'This means no users will have this permission anymore.\n\n' +
                        'Are you sure you want to continue?'
                    );
                    
                    if (!confirm) {
                        e.preventDefault();
                        return false;
                    }
                }
            });

            // Auto-format permission name
            $('#name').on('input', function() {
                let value = $(this).val();
                value = value.toLowerCase();
                $(this).val(value);
            });
        });
    </script>
@endpush
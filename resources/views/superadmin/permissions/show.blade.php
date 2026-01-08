@extends('layouts.app')

@section('title', ucwords($permission->name))

@push('styles')
    <style>
        .info-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
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

        .info-value-large {
            font-weight: bold;
            color: #007bff;
        }

        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-number {
            font-weight: bold;
            margin: 10px 0 5px;
        }

        .stat-label {
            color: #6c757d;
        }

        .role-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s ease;
        }

        .role-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .role-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .role-name {
            font-weight: 600;
            color: #212529;
            text-transform: capitalize;
        }

        .role-badge {
            background-color: #e7f3ff;
            border: 1px solid #0066cc;
            color: #0066cc;
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: 600;
            display: inline-block;
            margin-top: 5px;
        }

        .role-stats {
            display: flex;
            justify-content: space-between;
            color: #6c757d;
            margin-bottom: 12px;
        }

        .role-stats i {
            margin-right: 5px;
        }

        .check-icon {
            width: 24px;
            height: 24px;
            background-color: #d4edda;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #28a745;
        }

        .nav-tabs {
            border-bottom: 2px solid #e9ecef;
        }

        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6c757d;
            font-weight: 500;
            padding: 15px 20px;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            border-bottom-color: #dee2e6;
            color: #495057;
        }

        .nav-tabs .nav-link.active {
            border-bottom-color: #007bff;
            color: #007bff;
            background-color: transparent;
        }

        .tab-content {
            padding: 20px 0;
        }

        .user-card {
            display: flex;
            align-items: center;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
        }

        .user-avatar-initial {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background-color: #e7f3ff;
            color: #007bff;
            margin-right: 12px;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-weight: 500;
            color: #212529;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-email {
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .role-section {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .role-section-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .role-section-title {
            font-weight: 600;
            color: #212529;
            text-transform: capitalize;
        }

        .role-section-body {
            padding: 15px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            color: #cbd5e0;
            margin-bottom: 20px;
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
                                <h1>{{ ucwords($permission->name) }}</h1>
                            </div>
                        </div>
                        <span>{{ $permission->description }}</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.permissions.index') }}">Permissions</a></li>
                                    <li class="active">{{ ucwords($permission->name) }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px; margin-bottom: 10px;">
                                <a href="{{ route('superadmin.permissions.edit', $permission) }}" class="btn btn-primary">
                                    <i class="ti-pencil-alt"></i> Edit Permission
                                </a>
                                <form action="{{ route('superadmin.permissions.destroy', $permission) }}" method="POST" 
                                    style="display: inline-block;"
                                    id="deletePermissionForm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="ti-trash"></i> Delete Permission
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Permission Info Card -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="info-card">
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-label">Permission Name</div>
                                        <div class="info-value">{{ ucwords($permission->name) }}</div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">Module</div>
                                        <div class="info-value" style="text-transform: capitalize;">
                                            {{ explode(' ', $permission->name)[1] ?? 'General' }}
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">Guard</div>
                                        <div class="info-value">{{ $permission->guard_name }}</div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">Assigned Roles</div>
                                        <div class="info-value">{{ $rolesWithPermission->count() }}</div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">Created</div>
                                        <div class="info-value">{{ $permission->created_at->format('d F Y') }}</div>
                                        <small class="text-muted">{{ $permission->created_at->diffForHumans() }}</small>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">Last Updated</div>
                                        <div class="info-value">{{ $permission->updated_at->format('d F Y') }}</div>
                                    </div>

                                    <div class="info-item" style="grid-column: span 2;">
                                        <div class="info-label">Total Users Affected</div>
                                        <div class="info-value-large">
                                            {{ $rolesWithPermission->sum(function($role) { return $role->users->count(); }) }}
                                        </div>
                                        <small class="text-muted">Users with roles that have this permission</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-shield color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Roles with Permission</div>
                                        <div class="stat-digit">{{ $rolesWithPermission->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-user color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Users Affected</div>
                                        <div class="stat-digit">{{ $rolesWithPermission->sum(function($role) { return $role->users->count(); }) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-time color-pink border-pink"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Age</div>
                                        <div class="stat-digit">{{ $permission->created_at->diffForHumans(null, true) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                      
                    </div>
        
                    <!-- Tabs -->
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="custom-tab">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li role="presentation" class="active"><a href="#roles-tab" aria-controls="roles-tab" role="tab" data-toggle="tab"> <i class="ti-shield"></i> Assigned Roles ({{ $rolesWithPermission->count() }})</a></li>
                                            <li role="presentation"><a href="#users-tab" aria-controls="users-tab" role="tab" data-toggle="tab"><i class="ti-user"></i> Affected Users</a></li>
                                        </ul>
                                        <div class="tab-content">
                                            <!-- Roles Tab -->
                                            <div role="tabpanel" class="tab-pane active" id="roles-tab">
                                                    @if($rolesWithPermission->count() > 0)
                                                        <div class="row">
                                                            @foreach($rolesWithPermission as $role)
                                                            <div class="col-lg-4 col-md-6 mb-3">
                                                                <div class="role-card">
                                                                    <div class="role-card-header">
                                                                        <div>
                                                                            <div class="role-name">{{ str_replace('_', ' ', $role->name) }}</div>
                                                                            @if(in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent']))
                                                                                <span class="role-badge">System Role</span>
                                                                            @endif
                                                                        </div>
                                                                        <div class="check-icon">
                                                                            <i class="ti-check"></i>
                                                                        </div>
                                                                    </div>

                                                                    <div class="role-stats">
                                                                        <div>
                                                                            <i class="ti-user"></i>
                                                                            {{ $role->users->count() }} users
                                                                        </div>
                                                                        <div>
                                                                            <i class="ti-key"></i>
                                                                            {{ $role->permissions->count() }} permissions
                                                                        </div>
                                                                    </div>

                                                                    <a href="{{ route('superadmin.roles.show', $role) }}" class="btn btn-primary btn-sm btn-block">
                                                                        <i class="ti-eye"></i> View Role
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="empty-state">
                                                            <i class="ti-shield"></i>
                                                            <h3 class="mb-3">Not Assigned to Any Roles</h3>
                                                            <p class="text-muted mb-4">This permission is not currently assigned to any roles.</p>
                                                            <a href="{{ route('superadmin.permissions.edit', $permission) }}" class="btn btn-primary">
                                                                <i class="ti-plus"></i> Assign to Roles
                                                            </a>
                                                        </div>
                                                    @endif
                                            </div>
                                            <div role="tabpanel" class="tab-pane" id="users-tab">
                                                @if($rolesWithPermission->count() > 0)
                                                    @foreach($rolesWithPermission as $role)
                                                        @if($role->users->count() > 0)
                                                        <div class="role-section">
                                                            <div class="role-section-header">
                                                                <div class="role-section-title">
                                                                    {{ str_replace('_', ' ', $role->name) }}
                                                                    <span style="font-weight: 400; color: #6c757d;">
                                                                        ({{ $role->users->count() }} users)
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="role-section-body">
                                                                <div class="row">
                                                                    @foreach($role->users as $user)
                                                                    <div class="col-lg-4 col-md-6 mb-3">
                                                                        <div class="user-card">
                                                                            @if($user->profile_photo)
                                                                                <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="user-avatar">
                                                                            @else
                                                                                <div class="user-avatar-initial">
                                                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                                                </div>
                                                                            @endif
                                                                            <div class="user-info">
                                                                                <div class="user-name">{{ $user->name }}</div>
                                                                                <div class="user-email">{{ $user->email }}</div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-user"></i>
                                                        <h3 class="mb-3">No Users Affected</h3>
                                                        <p class="text-muted">This permission is not assigned to any roles yet.</p>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /# column -->
                    </div>


                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Permission Details</p>
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
            // Handle role deletion with SweetAlert
            $('#deletePermissionForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default submission
                
                const form = this;
                const permissionName = "{{ $permission->name }}";
                swal({
                    title: "Are you sure?",
                    text: "You want to delete the permission '" + permissionName + "'? This action cannot be undone!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel!",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function(isConfirm){
                    if (isConfirm) {
                        form.submit(); // Submit the form
                    }
                });
                
                return false;
            });
        });
    </script>
@endpush
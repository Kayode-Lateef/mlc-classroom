@extends('layouts.app')

@section('title', ucwords(str_replace('_', ' ', $role->name)))

@push('styles')
    <style>
        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            color: #6c757d;
            margin-bottom: 5px;
        }

        .info-value {
            /* font-size: 1rem; */
            color: #212529;
            font-weight: 500;
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
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0 5px;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
        }

        #permissions-container {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .permission-module {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            background-color: #fff;
            
        }

        .permission-module-title {
            /* font-size: 1.1rem; */
            font-weight: 600;
            color: #212529;
            text-transform: capitalize;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }

        .permission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
        }

        .permission-badge {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
            /* font-size: 1rem; */
            color: #155724;
        }

        .permission-badge i {
            margin-right: 8px;
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

        .user-table {
            width: 100%;
        }

        .user-table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            border-bottom: 2px solid #e9ecef;
        }

        .user-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-avatar-initial {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background-color: #e7f3ff;
            color: #007bff;
        }

        .activity-item {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #007bff;
            margin-bottom: 10px;
        }

        .activity-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e7f3ff;
            color: #007bff;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .system-role-badge {
            background-color: #e7f3ff;
            border: 1px solid #0066cc;
            color: #0066cc;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-top: 5px;
        }

        .protected-notice {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 15px;
            display: inline-flex;
            align-items: center;
            color: #6c757d;
        }

        .protected-notice i {
            margin-right: 8px;
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
                                <h1>{{ ucwords(str_replace('_', ' ', $role->name)) }}</h1>
                            </div>
                        </div>
                        <span>{{ $role->description }}</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.roles.index') }}">Roles</a></li>
                                    <li class="active">{{ ucwords(str_replace('_', ' ', $role->name)) }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                                @if(!in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent']))
                                    <a href="{{ route('superadmin.roles.edit', $role) }}" class="btn btn-primary">
                                        <i class="ti-pencil-alt"></i> Edit Role
                                    </a>
                                    <form action="{{ route('superadmin.roles.destroy', $role) }}" 
                                        method="POST" 
                                        style="display: inline-block;"
                                        id="deleteRoleForm">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="ti-trash"></i> Delete Role
                                        </button>
                                    </form>
                                @else
                                    <div class="protected-notice">
                                        <i class="ti-lock"></i>
                                        <span>System Role (Protected)</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Role Information Card -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-item">
                                                <div class="info-label">Role Name</div>
                                                <div class="info-value">
                                                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                                    @if(in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent']))
                                                        <span class="system-role-badge">System Role</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="info-item">
                                                <div class="info-label">Assigned Permissions</div>
                                                <div class="info-value">{{ $role->permissions->count() }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="info-item">
                                                <div class="info-label">Users with this Role</div>
                                                <div class="info-value">{{ $role->users->count() }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="info-item">
                                                <div class="info-label">Created</div>
                                                <div class="info-value">
                                                    {{ $role->created_at->format('d F Y') }}
                                                    <small class="text-muted d-block">{{ $role->created_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="info-item">
                                                <div class="info-label">Last Updated</div>
                                                <div class="info-value">{{ $role->updated_at->format('d F Y') }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="info-item">
                                                <div class="info-label">Guard</div>
                                                <div class="info-value">{{ $role->guard_name }}</div>
                                            </div>
                                        </div>
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
                                    <div class="stat-icon dib"><i class="ti-key color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Permissions</div>
                                        <div class="stat-digit">{{ $role->permissions->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-user color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Assigned Users</div>
                                        <div class="stat-digit">{{ $role->users->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-folder color-pink border-pink"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Permission Modules</div>
                                        <div class="stat-digit">{{ $groupedPermissions->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                      
                    </div>

                    {{-- Tabs --}}
                        <div class="row">

                        <!-- /# column -->
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="custom-tab">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li role="presentation" class="active"><a href="#permissions-tab" aria-controls="permissions-tab" role="tab" data-toggle="tab">Permissions ({{ $role->permissions->count() }})</a></li>
                                            <li role="presentation"><a href="#users-tab" aria-controls="users-tab" role="tab" data-toggle="tab">Users ({{ $role->users->count() }})</a></li>
                                            <li role="presentation"><a href="#activity-tab" aria-controls="activity-tab" role="tab" data-toggle="tab">Recent Activity</a></li>
                                        </ul>
                                        <div class="tab-content">
                                            <!-- Permissions Tab -->
                                            <div role="tabpanel" class="tab-pane active" id="permissions-tab">                                                
                                                @if($groupedPermissions->count() > 0)
                                                    <div id="permissions-container">
                                                        @foreach($groupedPermissions as $module => $permissions)
                                                        <div class="permission-module">
                                                            <h5 class="permission-module-title">
                                                                <i class="ti-folder"></i> {{ str_replace('_', ' ', $module) }}
                                                            </h5>
                                                            <div class="permission-grid">
                                                                @foreach($permissions as $permission)
                                                                <div class="permission-badge">
                                                                    <i class="ti-check"></i>
                                                                    <span>{{ ucwords(str_replace(['.', '_'], ' ', $permission->name)) }}</span>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-key"></i>
                                                        <h4>No Permissions Assigned</h4>
                                                        <p>This role doesn't have any permissions yet.</p>
                                                        @if(!in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent']))
                                                        <a href="{{ route('superadmin.roles.edit', $role) }}" class="btn btn-primary mt-activity-tab">
                                                            <i class="ti-plus"></i> Assign Permissions
                                                        </a>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <!-- Users Tab -->
                                            <div role="tabpanel" class="tab-pane" id="users-tab">
                                                @if($role->users->count() > 0)
                                                    <div class="table-responsive">
                                                        <table class="user-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>User</th>
                                                                    <th>Email</th>
                                                                    <th>Phone</th>
                                                                    <th>Created</th>
                                                                    <th style="text-align: right;">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($role->users as $user)
                                                                <tr>
                                                                    <td>
                                                                        <div style="display: flex; align-items: center; gap: 10px;">
                                                                            @if($user->profile_photo)
                                                                                <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="user-avatar">
                                                                            @else
                                                                                <div class="user-avatar-initial">
                                                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                                                </div>
                                                                            @endif
                                                                            <span style="font-weight: 500;">{{ $user->name }}</span>
                                                                        </div>
                                                                    </td>
                                                                    <td>{{ $user->email }}</td>
                                                                    <td>{{ $user->phone ?? '-' }}</td>
                                                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                                                    <td style="text-align: right;">
                                                                        <a href="{{ route('superadmin.users.show', $user) }}" class="btn btn-sm btn-info">
                                                                            <i class="ti-eye"></i> View
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-user"></i>
                                                        <h4>No Users Assigned</h4>
                                                        <p>No users have been assigned to this role yet.</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <!-- Activity Tab -->
                                            <div role="tabpanel" class="tab-pane" id="activity-tab">
                                                @if($recentActivity->count() > 0)
                                                    @foreach($recentActivity as $activity)
                                                    <div class="activity-item">
                                                        <div style="display: flex; align-items: flex-start; gap: 15px;">
                                                            <div class="activity-icon">
                                                                <i class="ti-info-alt"></i>
                                                            </div>
                                                            <div style="flex: 1;">
                                                                <p style="margin-bottom: 5px; color: #212529;">{{ $activity->description }}</p>
                                                                <small class="text-muted">
                                                                    {{ $activity->user ? $activity->user->name : 'System' }} • 
                                                                    <i class="ti-time"></i> {{ $activity->created_at->diffForHumans() }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-time"></i>
                                                        <h4>No Recent Activity</h4>
                                                        <p>No activity recorded for this role yet.</p>
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
                                <p>MLC Classroom - Role Details</p>
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
            $('#deleteRoleForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default submission
                
                const form = this;
                const roleName = "{{ $role->name }}";
                
                swal({
                    title: "Are you sure?",
                    text: "You want to delete the role '" + roleName + "'? This action cannot be undone!",
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
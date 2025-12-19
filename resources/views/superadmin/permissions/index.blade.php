@extends('layouts.app')

@section('title', 'Permissions Management')

@push('styles')
    <style>
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

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-top: 10px;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 5px;
        }

        .info-banner {
            background-color: #e7f3ff;
            border: 1px solid #0066cc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-banner i {
            color: #0066cc;
            font-size: 1.2rem;
        }

        .module-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .module-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: white;
        }

        .module-title {
            font-size: 1.25rem;
            font-weight: 600;
            text-transform: capitalize;
            margin: 0;
        }

        .module-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(255,255,255,0.3);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .module-stats {
            background-color: rgba(255,255,255,0.3);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
        }

        .permission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            padding: 20px;
        }

        .permission-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            background: linear-gradient(to bottom right, #fff, #f8f9fa);
            transition: all 0.3s ease;
        }

        .permission-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .permission-name {
            font-size: 1rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 8px;
        }

        .permission-roles {
            font-size: 0.8rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .permission-roles i {
            margin-right: 5px;
        }

        .permission-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge-assigned {
            background-color: #d4edda;
            color: #28a745;
        }

        .badge-unassigned {
            background-color: #f8f9fa;
            color: #6c757d;
        }

        .permission-actions {
            display: flex;
            gap: 8px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 5rem;
            color: #cbd5e0;
            margin-bottom: 20px;
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
                                <h1>Permissions Management</h1>
                                <p class="text-muted">Manage system permissions and their assignments</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Permissions</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Create Button -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                                <a href="{{ route('superadmin.permissions.create') }}" class="btn btn-primary">
                                    <i class="ti-plus"></i> Create New Permission
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="stat-card">
                                <div style="display: flex; align-items: center;">
                                    <div class="stat-icon bg-primary text-white">
                                        <i class="ti-key"></i>
                                    </div>
                                    <div style="margin-left: 15px; flex: 1;">
                                        <div class="stat-label">Total Permissions</div>
                                        <div class="stat-value text-primary">{{ $permissions->flatten()->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="stat-card">
                                <div style="display: flex; align-items: center;">
                                    <div class="stat-icon bg-success text-white">
                                        <i class="ti-folder"></i>
                                    </div>
                                    <div style="margin-left: 15px; flex: 1;">
                                        <div class="stat-label">Permission Modules</div>
                                        <div class="stat-value text-success">{{ $permissions->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="stat-card">
                                <div style="display: flex; align-items: center;">
                                    <div class="stat-icon bg-info text-white">
                                        <i class="ti-shield"></i>
                                    </div>
                                    <div style="margin-left: 15px; flex: 1;">
                                        <div class="stat-label">Total Roles</div>
                                        <div class="stat-value text-info">{{ \Spatie\Permission\Models\Role::count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="stat-card">
                                <div style="display: flex; align-items: center;">
                                    <div class="stat-icon bg-warning text-white">
                                        <i class="ti-check-box"></i>
                                    </div>
                                    <div style="margin-left: 15px; flex: 1;">
                                        <div class="stat-label">Assigned</div>
                                        <div class="stat-value text-warning">
                                            {{ $permissions->flatten()->filter(function($p) { return $p->roles_count > 0; })->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Banner -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="info-banner">
                                <div class="d-flex">
                                    <i class="ti-info-alt mr-3"></i>
                                    <div>
                                        <strong>Permission Naming Convention</strong>
                                        <p class="mb-0 mt-2">
                                            Permissions follow the format: <code>action module</code> (e.g., "create users", "view reports"). 
                                            The second word determines the module grouping.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
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

                    <!-- Permissions by Module -->
                    @if($permissions->count() > 0)
                        <div class="row mt-4">
                            <div class="col-lg-12">
                                @foreach($permissions->sortKeys() as $module => $modulePermissions)
                                <div class="module-card">
                                    <!-- Module Header -->
                                    <div class="module-header">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div style="display: flex; align-items: center;">
                                                <div class="module-icon">
                                                    <i class="ti-folder"></i>
                                                </div>
                                                <div>
                                                    <h3 class="module-title">{{ str_replace('_', ' ', $module) }} Module</h3>
                                                    <small style="opacity: 0.9;">{{ $modulePermissions->count() }} permissions</small>
                                                </div>
                                            </div>
                                            <span class="module-stats">
                                                {{ $modulePermissions->sum('roles_count') }} role assignments
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Permissions Grid -->
                                    <div class="permission-grid">
                                        @foreach($modulePermissions->sortBy('name') as $permission)
                                        <div class="permission-card">
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                                <div style="flex: 1;">
                                                    <div class="permission-name">{{ ucwords($permission->name) }}</div>
                                                    <div class="permission-roles">
                                                        <i class="ti-shield"></i>
                                                        <span>{{ $permission->roles_count }} role(s)</span>
                                                    </div>
                                                </div>
                                                <div class="permission-badge {{ $permission->roles_count > 0 ? 'badge-assigned' : 'badge-unassigned' }}">
                                                    @if($permission->roles_count > 0)
                                                        <i class="ti-check"></i>
                                                    @else
                                                        <i class="ti-close"></i>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="permission-actions">
                                                <a href="{{ route('superadmin.permissions.show', $permission) }}" 
                                                   class="btn btn-primary btn-sm" style="flex: 1;">
                                                    <i class="ti-eye"></i> View Details
                                                </a>
                                                <a href="{{ route('superadmin.permissions.edit', $permission) }}" 
                                                   class="btn btn-success btn-sm" 
                                                   title="Edit">
                                                    <i class="ti-pencil-alt"></i>
                                                </a>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="empty-state">
                                    <i class="ti-key"></i>
                                    <h3 class="mb-3">No Permissions Found</h3>
                                    <p class="text-muted mb-4">Get started by creating your first permission.</p>
                                    <a href="{{ route('superadmin.permissions.create') }}" class="btn btn-primary">
                                        <i class="ti-plus"></i> Create Permission
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Permissions Management</p>
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
            // Add hover effects
            $('.permission-card').hover(
                function() {
                    $(this).addClass('shadow');
                },
                function() {
                    $(this).removeClass('shadow');
                }
            );

            // Stat card animations
            $('.stat-card').hover(
                function() {
                    $(this).addClass('shadow-lg');
                },
                function() {
                    $(this).removeClass('shadow-lg');
                }
            );
        });
    </script>
@endpush
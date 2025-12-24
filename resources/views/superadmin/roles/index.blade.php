@extends('layouts.app')

@section('title', 'Roles Management')

@push('styles')
    <style>
        /* Role card styles */
        .role-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .role-card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: white;
        }

        .role-card-header.system-role {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .role-card-body {
            padding: 20px;
        }

        .system-role-badge {
            background-color: rgba(255,255,255,0.3);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .stat-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 0.875rem;
            color: #6c757d;
        }

        .stat-item i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .stat-item.permissions i {
            color: #28a745;
        }

        .stat-item.users i {
            color: #007bff;
        }

        .stat-item.date i {
            color: #6c757d;
        }

        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .stat-card-content {
            margin-left: 20px;
        }

        .stat-card-value {
            font-size: 2rem;
            font-weight: bold;
            margin: 5px 0;
        }

        .stat-card-label {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .search-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            padding-top: 15px;
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
                                <h1>Roles & Permissions Management</h1>
                            </div>
                        </div>
                        <span>Manage and view all roles and their associated permissions</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Roles</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Add Role Button -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-right mt-3">
                                <a href="{{ route('superadmin.roles.create') }}" class="btn btn-primary">
                                    <i class="ti-plus"></i> Create New Role
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-shield color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Roles</div>
                                        <div class="stat-digit">{{ $stats['total_roles'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-key color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Permissions</div>
                                        <div class="stat-digit">{{ $stats['total_permissions'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-user color-pink border-pink"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Users</div>
                                        <div class="stat-digit">{{ $stats['total_users'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                      
                    </div>

                    <!-- Search Box -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="search-card">
                                <form method="GET" action="{{ route('superadmin.roles.index') }}">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group mb-0">
                                                <input 
                                                    type="text" 
                                                    name="search" 
                                                    value="{{ request('search') }}" 
                                                    placeholder="Search roles by name..." 
                                                    class="form-control"
                                                >
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary flex-fill">
                                                    <i class="ti-search"></i> Search
                                                </button>
                                                <a href="{{ route('superadmin.roles.index') }}" class="btn btn-secondary flex-fill">
                                                    <i class="ti-reload"></i> Reset
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
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

                    @if(session('warning'))
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="alert alert-warning alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <i class="ti-info"></i> {{ session('warning') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Roles Grid -->
                    @if($roles->count() > 0)
                    <div class="row">
                        @foreach($roles as $role)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="panel lobipanel-basic panel-primary">
                                <div class="panel-heading">
                                    <div class="panel-title {{ in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent']) ? 'system-role' : '' }}">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <h4 style="margin: 0; color: white;">
                                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                            </h4>
                                            @if(in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent']))
                                                <span class="system-role-badge">System Role</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                     <!-- Role Card Body -->
                                    <div class="role-card-body">
                                        <!-- Statistics -->
                                        <div class="stat-item permissions">
                                            <i class="ti-key"></i>
                                            <span><strong>{{ $role->permissions_count }}</strong> Permissions</span>
                                        </div>

                                        <div class="stat-item users">
                                            <i class="ti-user"></i>
                                            <span><strong>{{ $role->users_count }}</strong> Users</span>
                                        </div>

                                        <div class="stat-item date">
                                            <i class="ti-time"></i>
                                            <span>Created {{ $role->created_at->diffForHumans() }}</span>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="action-buttons">
                                            <a href="{{ route('superadmin.roles.show', $role) }}" class="btn btn-primary btn-sm flex-fill">
                                                <i class="ti-eye"></i> View Details
                                            </a>
                                            @if(!in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent']))
                                            <a href="{{ route('superadmin.roles.edit', $role) }}" class="btn btn-success btn-sm">
                                                <i class="ti-pencil-alt"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-lg-4 col-md-6 mb-4">
                            <div class="role-card">
                                <!-- Role Card Header -->
                                <div class="role-card-header {{ in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent']) ? 'system-role' : '' }}">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <h4 style="margin: 0; color: white;">
                                            {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                        </h4>
                                        @if(in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent']))
                                            <span class="system-role-badge">System Role</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Role Card Body -->
                                <div class="role-card-body">
                                    <!-- Statistics -->
                                    <div class="stat-item permissions">
                                        <i class="ti-key"></i>
                                        <span><strong>{{ $role->permissions_count }}</strong> Permissions</span>
                                    </div>

                                    <div class="stat-item users">
                                        <i class="ti-user"></i>
                                        <span><strong>{{ $role->users_count }}</strong> Users</span>
                                    </div>

                                    <div class="stat-item date">
                                        <i class="ti-time"></i>
                                        <span>Created {{ $role->created_at->diffForHumans() }}</span>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="action-buttons">
                                        <a href="{{ route('superadmin.roles.show', $role) }}" class="btn btn-primary btn-sm flex-fill">
                                            <i class="ti-eye"></i> View Details
                                        </a>
                                        @if(!in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent']))
                                        <a href="{{ route('superadmin.roles.edit', $role) }}" class="btn btn-success btn-sm">
                                            <i class="ti-pencil-alt"></i>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mt-4">
                                {{ $roles->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- Empty State -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="empty-state">
                                <i class="ti-shield"></i>
                                <h3 class="mb-3">No Roles Found</h3>
                                <p class="text-muted mb-4">Get started by creating your first custom role.</p>
                                <a href="{{ route('superadmin.roles.create') }}" class="btn btn-primary">
                                    <i class="ti-plus"></i> Create Role
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Roles Management</p>
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
            // Add any custom JavaScript here if needed
            
            // Smooth scroll for role cards
            $('.role-card').hover(
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
@extends('layouts.app')

@section('title', 'User Management')

@push('styles')
    <style>
        /* Custom styles for user management */
        .filter-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            font-size: 2rem;
            margin: 10px 0;
        }
        
        .stat-card p {
            margin: 0;
            color: #6c757d;
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
            font-size: 1rem;
        }
        
        .role-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        
        .status-verified {
            color: #28a745;
        }
        
        .status-unverified {
            color: #ffc107;
        }
        
        .action-buttons a, .action-buttons button {
            margin: 0 2px;
        }
        
        .search-box {
            max-width: 300px;
        }

        .related-info {
            color: #6c757d;
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
                                <h1>User Management</h1>
                            </div>
                        </div>
                        <span>Manage and view all user accounts</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="active">User Management</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                 
                    
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-primary">
                                        <i class="ti-user"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ $stats['total'] }}</div>
                                        <div class="stat-text">Total Users</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-danger">
                                        <i class="ti-crown"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ $stats['superadmins'] }}</div>
                                        <div class="stat-text">Super Admins</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-success">
                                        <i class="ti-id-badge"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ $stats['admins'] }}</div>
                                        <div class="stat-text">Admins</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-info">
                                        <i class="ti-briefcase"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ $stats['teachers'] }}</div>
                                        <div class="stat-text">Teachers</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-warning">
                                        <i class="ti-user"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ $stats['parents'] }}</div>
                                        <div class="stat-text">Parents</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-lg-12">
                            <div class="filter-card">
                                <form method="GET" 
                                    action="{{ route('superadmin.users.index') }}" 
                                    id="userFilterForm">
                                    <div class="row">
                                        <!-- Search Field -->
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Search</label>
                                                <input type="text" 
                                                    name="search" 
                                                    class="form-control" 
                                                    placeholder="Name, Email, or Phone" 
                                                    value="{{ request('search') }}">
                                            </div>
                                        </div>

                                        <!-- Role Filter -->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Filter by Role</label>
                                                <select name="role" class="form-control">
                                                    <option value="">All Roles</option>
                                                    <option value="superadmin" {{ request('role') == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                                    <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                                                    <option value="parent" {{ request('role') == 'parent' ? 'selected' : '' }}>Parent</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Email Status Filter -->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Email Status</label>
                                                <select name="verified" class="form-control">
                                                    <option value="">All</option>
                                                    <option value="1" {{ request('verified') == '1' ? 'selected' : '' }}>Verified</option>
                                                    <option value="0" {{ request('verified') == '0' ? 'selected' : '' }}>Unverified</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Add after Email Status filter -->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Account Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="">All Statuses</option>
                                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                    <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Banned</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Sort By -->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Sort By</label>
                                                <select name="sort_by" class="form-control">
                                                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date Created</option>
                                                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                                                    <option value="email" {{ request('sort_by') == 'email' ? 'selected' : '' }}>Email</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Sort Order -->
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>Order</label>
                                                <select name="sort_order" class="form-control">
                                                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Desc</option>
                                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Filter & Reset Buttons -->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div style="display: flex; gap: 5px;">
                                                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                                                        <i class="ti-search"></i> Filter
                                                    </button>
                                                    <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary" title="Clear Filters">
                                                        <i class="ti-reload"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4>All Users ({{ $users->total() }})</h4>
                                    <div class="card-header-right-icon">
                                        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary btn-sm">
                                            <i class="ti-plus"></i> Add New User
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">

                                    @if($users->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>User</th>
                                                    <th>Role</th>
                                                    <th>Email Status</th>
                                                    <th>Phone</th>
                                                    <th>Created</th>
                                                    <th>Related</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($users as $user)
                                                    <tr>
                                                        <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                                        <td>
                                                            <div style="display: flex; align-items: center;">
                                                                @if($user->profile_photo)
                                                                    <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="user-avatar" style="margin-right: 12px;">
                                                                @else
                                                                    <div class="user-avatar-initial 
                                                                        {{ $user->role === 'superadmin' ? 'bg-danger text-white' : 
                                                                        ($user->role === 'admin' ? 'bg-success text-white' : 
                                                                        ($user->role === 'teacher' ? 'bg-info text-white' : 'bg-warning text-white')) }}" 
                                                                        style="margin-right: 12px;">
                                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                                    </div>
                                                                @endif
                                                                <div style="flex: 1;">
                                                                    <div>
                                                                        <strong>{{ $user->name }}</strong>
                                                                        @if($user->id === auth()->id())
                                                                            <span class="badge badge-info badge-sm" style="margin-left: 5px;">You</span>
                                                                        @endif
                                                                    </div>
                                                                    <small class="text-muted" style="display: block; margin-top: 2px;">{{ $user->email }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($user->role === 'superadmin')
                                                                <span class="badge badge-danger role-badge">Super Admin</span>
                                                            @elseif($user->role === 'admin')
                                                                <span class="badge badge-success role-badge">Admin</span>
                                                            @elseif($user->role === 'teacher')
                                                                <span class="badge badge-info role-badge">Teacher</span>
                                                            @elseif($user->role === 'parent')
                                                                <span class="badge badge-warning role-badge">Parent</span>
                                                            @else
                                                                <span class="badge badge-secondary role-badge">{{ ucfirst($user->role) }}</span>
                                                            @endif
                                                        </td>
                                                        <!-- Replace email verification badge with status badge -->
                                                        <td>
                                                            @if($user->status === 'active')
                                                                <span class="badge badge-success">
                                                                    <i class="ti-check"></i> Active
                                                                </span>
                                                            @elseif($user->status === 'suspended')
                                                                <span class="badge badge-warning">
                                                                    <i class="ti-lock"></i> Suspended
                                                                </span>
                                                            @elseif($user->status === 'inactive')
                                                                <span class="badge badge-secondary">
                                                                    <i class="ti-time"></i> Inactive
                                                                </span>
                                                            @elseif($user->status === 'banned')
                                                                <span class="badge badge-danger">
                                                                    <i class="ti-na"></i> Banned
                                                                </span>
                                                            @endif
                                                            
                                                            <!-- Email verification (separate) -->
                                                            @if($user->email_verified_at)
                                                                <span class="badge badge-info badge-sm ml-1">
                                                                    <i class="ti-email"></i> Verified
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $user->phone ?? '-' }}</td>
                                                        <td>{{ $user->created_at->format('d M Y') }}</td>
                                                        <td>
                                                            <span class="related-info">
                                                                @if($user->role === 'teacher' && method_exists($user, 'teachingClasses'))
                                                                    {{ $user->teachingClasses()->count() }} classes
                                                                @elseif($user->role === 'parent' && method_exists($user, 'children'))
                                                                    {{ $user->children()->count() }} children
                                                                @else
                                                                    -
                                                                @endif
                                                            </span>
                                                        </td>
                                                        <td class="action-buttons">
                                                            <a href="{{ route('superadmin.users.show', $user) }}" class="btn btn-sm btn-info" title="View">
                                                                <i class="ti-eye"></i>
                                                            </a>
                                                            
                                                            @if($user->id !== auth()->id())
                                                                <a href="{{ route('superadmin.users.edit', $user) }}" class="btn btn-sm btn-success" title="Edit">
                                                                    <i class="ti-pencil-alt"></i>
                                                                </a>
                                                                
                                                               @if($user->id !== auth()->id())
                                                                    <!-- Suspend/Activate Form -->
                                                                    <form action="{{ route('superadmin.users.toggleStatus', $user) }}" 
                                                                        method="POST" 
                                                                        style="display: inline-block;"
                                                                        id="suspendForm_{{ $user->id }}">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        
                                                                        <button type="button" 
                                                                                class="btn btn-sm btn-warning" 
                                                                                title="{{ $user->status === 'active' ? 'Suspend' : 'Activate' }}"
                                                                                onclick="handleSuspend({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->status === 'active' ? 'true' : 'false' }})">
                                                                            <i class="ti-lock"></i>
                                                                        </button>
                                                                    </form>
                                                                @else
                                                                    <span class="badge badge-secondary badge-sm">Current User</span>
                                                                @endif
                                                                
                                                                <!-- DELETE BUTTON -->
                                                                <form action="{{ route('superadmin.users.destroy', $user) }}" 
                                                                    method="POST" 
                                                                    style="display: inline-block;"
                                                                    id="deleteForm_{{ $user->id }}"
                                                                    onsubmit="return handleDelete(event, '{{ addslashes($user->name) }}');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                        <i class="ti-trash"></i>
                                                                    </button>
                                                                </form>

                                                            @else
                                                                <span class="badge badge-secondary badge-sm">Current User</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <div class="mt-4">
                                        {{ $users->appends(request()->query())->links() }}
                                    </div>
                                    @else
                                    <!-- Empty State -->
                                    <div class="text-center py-5">
                                        <i class="ti-user" style="font-size: 4rem; color: #cbd5e0;"></i>
                                        <h3 class="mt-3 mb-2">No users found</h3>
                                        <p class="text-muted mb-4">Get started by adding your first user.</p>
                                        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">
                                            <i class="ti-plus"></i> Add User
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - User Management | Last Updated: <span id="date-time"></span></p>
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
function handleDelete(event, userName) {
    event.preventDefault(); // Stop the form from submitting immediately
    
    var form = event.target; // Get the form element
    
    swal({
        title: "Are you sure?",
        text: "You want to delete " + userName + "? This action cannot be undone!",
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
            form.submit(); // Submit the form immediately
        }
    });
    
    return false;
}

function handleSuspend(userId, userName, isActive) {
    var action = isActive ? 'suspend' : 'activate';
    
    var form = document.getElementById('suspendForm_' + userId);
    
    if (!form) {
        swal("Error!", "Form not found. Please refresh the page.", "error");
        return false;
    }
    
    swal({
        title: "Are you sure?",
        text: "You want to " + action + " " + userName + "?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: isActive ? "#DD6B55" : "#28a745",
        confirmButtonText: "Yes, " + action + "!",
        cancelButtonText: "No, cancel!",
        closeOnConfirm: true,
        closeOnCancel: true
    },
    function(isConfirm){
        if (isConfirm) {
            form.submit();
        }
    });
    
    return false;
}

// jQuery initialization
$(document).ready(function() {
    console.log('User management initialized');
    
    $('#userFilterForm').off('submit').on('submit', function() {
        return true;
    });
    
    $('#userFilterForm input[name="search"]').on('keypress', function(e) {
        if (e.which === 13) {
            $('#userFilterForm').submit();
        }
    });
});
</script>
@endpush
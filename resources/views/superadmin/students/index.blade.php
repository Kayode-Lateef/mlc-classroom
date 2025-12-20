@extends('layouts.app')

@section('title', 'Students')

@push('styles')
    <style>
        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 3px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .filter-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .student-avatar-initial {
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

        .related-info {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .action-buttons a,
        .action-buttons button {
            margin: 0 2px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 5rem;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .action-buttons a,
        .action-buttons button {
            margin: 0 2px;
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
                                <h1>Students</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Students</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Add Button -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                                <a href="{{ route('superadmin.students.create') }}" class="btn btn-primary">
                                    <i class="ti-plus"></i> Add Student
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card">
                                <div style="display: flex; align-items: center;">
                                    <div class="stat-icon bg-primary text-white">
                                        <i class="ti-user"></i>
                                    </div>
                                    <div style="margin-left: 10px; flex: 1;">
                                        <div class="stat-label">Total</div>
                                        <div class="stat-value text-primary">{{ $stats['total'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card">
                                <div style="display: flex; align-items: center;">
                                    <div class="stat-icon bg-success text-white">
                                        <i class="ti-check"></i>
                                    </div>
                                    <div style="margin-left: 10px; flex: 1;">
                                        <div class="stat-label">Active</div>
                                        <div class="stat-value text-success">{{ $stats['active'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card">
                                <div style="display: flex; align-items: center;">
                                    <div class="stat-icon bg-secondary text-white">
                                        <i class="ti-lock"></i>
                                    </div>
                                    <div style="margin-left: 10px; flex: 1;">
                                        <div class="stat-label">Inactive</div>
                                        <div class="stat-value text-secondary">{{ $stats['inactive'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card">
                                <div style="display: flex; align-items: center;">
                                    <div class="stat-icon bg-info text-white">
                                        <i class="ti-medall"></i>
                                    </div>
                                    <div style="margin-left: 10px; flex: 1;">
                                        <div class="stat-label">Graduated</div>
                                        <div class="stat-value text-info">{{ $stats['graduated'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="filter-card">
                                <form method="GET" action="{{ route('superadmin.students.index') }}">
                                    <div class="row">
                                        <!-- Search -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="form-group">
                                                <label>Search</label>
                                                <input 
                                                    type="text" 
                                                    name="search" 
                                                    value="{{ request('search') }}" 
                                                    placeholder="Name or email..."
                                                    class="form-control"
                                                >
                                            </div>
                                        </div>

                                        <!-- Status Filter -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="">All Statuses</option>
                                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                    <option value="graduated" {{ request('status') === 'graduated' ? 'selected' : '' }}>Graduated</option>
                                                    <option value="withdrawn" {{ request('status') === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Parent Filter -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="form-group">
                                                <label>Parent</label>
                                                <select name="parent_id" class="form-control">
                                                    <option value="">All Parents</option>
                                                    @foreach($parents as $parent)
                                                    <option value="{{ $parent->id }}" {{ request('parent_id') == $parent->id ? 'selected' : '' }}>
                                                        {{ $parent->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div style="display: flex; gap: 10px;">
                                                    <button type="submit" class="btn btn-primary flex-fill">
                                                        <i class="ti-search"></i> Filter
                                                    </button>
                                                    <a href="{{ route('superadmin.students.index') }}" class="btn btn-secondary">
                                                        <i class="ti-reload"></i> Reset
                                                    </a>
                                                </div>
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

                    <!-- Students Table -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4>All Students ({{ $students->total() }})</h4>
                                    <div class="card-header-right-icon">
                                        <a href="{{ route('superadmin.students.create') }}" class="btn btn-primary btn-sm">
                                            <i class="ti-plus"></i> Add New Student
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($students->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Student</th>
                                                    <th>Parent</th>
                                                    <th>Date of Birth</th>
                                                    <th>Enrollment</th>
                                                    <th>Status</th>
                                                    <th>Classes</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($students as $student)
                                                    <tr>
                                                        <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                                                        <td>
                                                            <div style="display: flex; align-items: center;">
                                                                @if($student->profile_photo)
                                                                    <img src="{{ asset('storage/' . $student->profile_photo) }}" alt="{{ $student->full_name }}" class="student-avatar" style="margin-right: 12px;">
                                                                @else
                                                                    <div class="student-avatar-initial bg-primary text-white" style="margin-right: 12px;">
                                                                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                                                    </div>
                                                                @endif
                                                                <div style="flex: 1;">
                                                                    <div>
                                                                        <strong>{{ $student->full_name }}</strong>
                                                                    </div>
                                                                    <small class="text-muted" style="display: block; margin-top: 2px;">ID: {{ $student->id }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <strong>{{ $student->parent->name }}</strong>
                                                            </div>
                                                            <small class="text-muted" style="display: block; margin-top: 2px;">{{ $student->parent->email }}</small>
                                                        </td>
                                                        <td>
                                                            {{ $student->date_of_birth->format('d M Y') }}
                                                            <small class="text-muted" style="display: block; margin-top: 2px;">Age: {{ $student->date_of_birth->age }}</small>
                                                        </td>
                                                        <td>{{ $student->enrollment_date->format('d M Y') }}</td>
                                                        <td>
                                                            @if($student->status === 'active')
                                                                <span class="badge badge-success role-badge">Active</span>
                                                            @elseif($student->status === 'inactive')
                                                                <span class="badge badge-secondary role-badge">Inactive</span>
                                                            @elseif($student->status === 'graduated')
                                                                <span class="badge badge-info role-badge">Graduated</span>
                                                            @else
                                                                <span class="badge badge-danger role-badge">Withdrawn</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="related-info">
                                                                {{ $student->classes->count() }} classes
                                                            </span>
                                                        </td>
                                                        <td class="action-buttons">
                                                            <a href="{{ route('superadmin.students.show', $student) }}" class="btn btn-sm btn-info" title="View">
                                                                <i class="ti-eye"></i>
                                                            </a>
                                                            
                                                            <a href="{{ route('superadmin.students.edit', $student) }}" class="btn btn-sm btn-success" title="Edit">
                                                                <i class="ti-pencil-alt"></i>
                                                            </a>
                                                            
                                                            <form action="{{ route('superadmin.students.destroy', $student) }}" 
                                                                method="POST" 
                                                                style="display: inline-block;"
                                                                onsubmit="return confirm('Are you sure you want to delete {{ $student->full_name }}? This action cannot be undone.');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                    <i class="ti-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <div class="mt-4">
                                        {{ $students->appends(request()->query())->links() }}
                                    </div>
                                    @else
                                    <!-- Empty State -->
                                    <div class="text-center py-5">
                                        <i class="ti-book" style="font-size: 4rem; color: #cbd5e0;"></i>
                                        <h3 class="mt-3 mb-2">No students found</h3>
                                        <p class="text-muted mb-4">Get started by adding your first student.</p>
                                        <a href="{{ route('superadmin.students.create') }}" class="btn btn-primary">
                                            <i class="ti-plus"></i> Add Student
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
                                <p>MLC Classroom - Students Management</p>
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
        });
    </script>
@endpush
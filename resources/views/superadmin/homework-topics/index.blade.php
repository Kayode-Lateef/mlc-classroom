@extends('layouts.app')

@section('title', 'Homework Topics Management')

@push('styles')
    <style>

        .topics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #3386f7;
        }

        .stat-card.orange {
            border-left-color: #e06829;
        }

        .stat-card.green {
            border-left-color: #28a745;
        }

        .stat-card.red {
            border-left-color: #dc3545;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .topics-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .topics-table .card-header {
            background: linear-gradient(135deg, #3386f7 0%, #e06829 100%);
            color: white;
            padding: 20px;
            border: none;
        }

        .topics-table .card-header h2 {
            margin: 0;
            color: white;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .topics-table .table {
            margin-bottom: 0;
        }

        .topics-table .table th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
            padding: 15px;
        }

        .topics-table .table td {
            padding: 15px;
            vertical-align: middle;
            font-size: 0.9375rem;
        }

        .topics-table .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge-subject {
            padding: 6px 12px;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 4px;
        }

        .badge-active {
            background-color: #28a745;
            color: white;
        }

        .badge-inactive {
            background-color: #6c757d;
            color: white;
        }


        .action-buttons a,
        .action-buttons button {
            margin: 0 2px;
        }

        .action-buttons a,
        .action-buttons button {
            margin: 0 2px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            color: #dee2e6;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: #495057;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #6c757d;
            margin-bottom: 20px;
        }

    .filter-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

        @media (max-width: 768px) {
            .topics-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .action-buttons {
                flex-direction: column;
                width: 100%;
            }

            .action-buttons .btn {
                width: 100%;
            }
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
                                <h1><i class="ti-bookmark-alt"></i> Homework Topics</h1>
                            </div>
                        </div>
                        <span>Manage homework topics for assignment categorization</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Homework Topics</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti-check"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti-alert"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="ti-info-alt"></i> {{ session('warning') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif


                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-book color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-digit">{{ $topics->total() }}</div>
                                        <div class="stat-text">Total Topics</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-check color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-digit">{{ $topics->where('is_active', true)->count() }}</div>
                                        <div class="stat-text">Active Topics</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-alert color-danger border-warning"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-digit">{{ $topics->where('is_active', false)->count() }}</div>
                                        <div class="stat-text">Inactive Topics</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-book color-warning border-danger"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-digit">{{ $topics->pluck('subject')->unique()->count() }}</div>
                                        <div class="stat-text">Subjects</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- Header with Create Button -->
                <div class="topics-header mt-4">
                    <div>
                        <h3 style="margin: 0; color: #2c3e50;">
                            <i class="ti-list"></i> All Topics
                        </h3>
                    </div>
                    <div>
                        <a href="{{ route('superadmin.homework-topics.create') }}" class="btn btn-primary">
                            <i class="ti-plus"></i> Create New Topic
                        </a>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="filter-card">
                            <form method="GET" action="{{ route('superadmin.homework-topics.index') }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="subject"><i class="ti-filter"></i> Filter by Subject</label>
                                            <select name="subject" id="subject" class="form-control">
                                                <option value="">All Subjects</option>
                                                @foreach($topics->pluck('subject')->unique()->filter() as $subject)
                                                    <option value="{{ $subject }}" {{ request('subject') == $subject ? 'selected' : '' }}>
                                                        {{ $subject }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="status"><i class="ti-check-box"></i> Status</label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="">All Status</option>
                                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    {{-- <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="search"><i class="ti-search"></i> Search</label>
                                            <input type="text" name="search" id="search" class="form-control" 
                                                placeholder="Search topics..." value="{{ request('search') }}">
                                        </div>
                                    </div> --}}

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{-- <label style="font-weight: 500;">Search</label> --}}
                                            <label for="search"><i class="ti-search"></i> Search</label>
                                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search topics.." class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti-filter"></i> Apply Filters
                                    </button>
                                    <a href="{{ route('superadmin.homework-topics.index') }}" class="btn btn-secondary">
                                        <i class="ti-reload"></i> Clear Filters
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>



                    <!-- Topics Table -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4><i class="ti-bookmark-alt"></i> Topics List</h4>
                                </div>

                                <div class="card-body">
                                    @if($topics->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Topic Name</th>
                                                    <th>Subject</th>
                                                    <th>Description</th>
                                                    <th>Status</th>
                                                    <th>Usage</th>
                                                    <th class="text-center">Actions</th>
                                                </tr>

                                            </thead>
                                            <tbody>
                                                    @foreach($topics as $topic)
                                                    <tr>
                                                        {{-- <td>{{ $loop->iteration + ($topics->currentPage() - 1) * $topics->perPage() }}</td> --}}
                                                        <td>{{ ($topics->currentPage() - 1) * $topics->perPage() + $loop->iteration }}</td>
                                                        <td>
                                                            <strong>{{ $topic->name }}</strong>
                                                        </td>
                                                        <td>
                                                            @if($topic->subject)
                                                                <span class="badge badge-subject" style="background: #3386f7; color: white;">
                                                                    {{ $topic->subject }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($topic->description)
                                                                {{ Str::limit($topic->description, 60) }}
                                                            @else
                                                                <span class="text-muted">No description</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($topic->is_active)
                                                                <span class="badge badge-active">
                                                                    <i class="ti-check"></i> Active
                                                                </span>
                                                            @else
                                                                <span class="badge badge-inactive">
                                                                    <i class="ti-close"></i> Inactive
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-info">
                                                                {{ $topic->homeworkAssignments->count() }} assignments
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="action-buttons">
                                                                <a href="{{ route('superadmin.homework-topics.edit', $topic->id) }}" 
                                                                    class="btn btn-sm btn-warning" 
                                                                    title="Edit Topic">
                                                                    <i class="ti-pencil"></i>
                                                                </a>
                                                                <form action="{{ route('superadmin.homework-topics.destroy', $topic->id) }}" 
                                                                        method="POST" 
                                                                        style="display: inline-block;"
                                                                        onsubmit="return confirm('Are you sure you want to delete this topic? This action cannot be undone.');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" 
                                                                            class="btn btn-sm btn-danger" 
                                                                            title="Delete Topic"
                                                                            {{ $topic->homeworkAssignments->count() > 0 ? 'disabled' : '' }}>
                                                                        <i class="ti-trash"></i>
                                                                    </button>
                                                                </form>

                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <div class="mt-4">
                                        {{ $topics->links() }}

                                    </div>
                                    @else
                                    <!-- Empty State -->
                                    <div class="text-center py-5">
                                        <i class="ti-book" style="font-size: 4rem; color: #cbd5e0;"></i>
                                        <h4>No Topics Found</h4>
                                        <p>There are no homework topics in the system yet.</p>
                                        <a href="{{ route('superadmin.homework-topics.create') }}" class="btn btn-primary">
                                            <i class="ti-plus"></i> Create Your First Topic
                                        </a>
                                    </div>
                                    @endif
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
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Confirm delete for topics with assignments
    $('form[onsubmit]').on('submit', function(e) {
        const hasAssignments = $(this).find('button').attr('disabled');
        if (hasAssignments) {
            e.preventDefault();
            alert('Cannot delete this topic as it is assigned to homework assignments.');
            return false;
        }
    });
});
</script>
@endpush
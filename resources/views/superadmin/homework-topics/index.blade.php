@extends('layouts.app')

@section('title', 'Homework Topics Management')

@push('styles')
    <style>
        .page-header {
            margin-bottom: 30px;
        }

        .topics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .topics-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
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

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
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

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }

        .filter-section .form-group {
            margin-bottom: 15px;
        }

        .filter-section label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #495057;
            margin-bottom: 8px;
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
                <div class="topics-stats">
                    <div class="stat-card">
                        <div class="stat-value">{{ $topics->total() }}</div>
                        <div class="stat-label">Total Topics</div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-value">{{ $topics->where('is_active', true)->count() }}</div>
                        <div class="stat-label">Active Topics</div>
                    </div>
                    <div class="stat-card orange">
                        <div class="stat-value">{{ $topics->where('is_active', false)->count() }}</div>
                        <div class="stat-label">Inactive Topics</div>
                    </div>
                    <div class="stat-card red">
                        <div class="stat-value">{{ $topics->pluck('subject')->unique()->count() }}</div>
                        <div class="stat-label">Subjects</div>
                    </div>
                </div>

                <!-- Header with Create Button -->
                <div class="topics-header">
                    <div>
                        <h3 style="margin: 0; color: #2c3e50;">
                            <i class="ti-list"></i> All Topics
                        </h3>
                        <p style="margin: 0; color: #6c757d; font-size: 0.9375rem;">
                            Manage homework topics for assignment categorization
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('superadmin.homework-topics.create') }}" class="btn btn-primary">
                            <i class="ti-plus"></i> Create New Topic
                        </a>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search"><i class="ti-search"></i> Search</label>
                                    <input type="text" name="search" id="search" class="form-control" 
                                           placeholder="Search topics..." value="{{ request('search') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti-filter"></i> Apply Filters
                                </button>
                                <a href="{{ route('superadmin.homework-topics.index') }}" class="btn btn-secondary">
                                    <i class="ti-reload"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Topics Table -->
                <div class="topics-table card">
                    <div class="card-header">
                        <h2><i class="ti-bookmark-alt"></i> Topics List</h2>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        @if($topics->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="25%">Topic Name</th>
                                            <th width="15%">Subject</th>
                                            <th width="30%">Description</th>
                                            <th width="10%">Status</th>
                                            <th width="10%">Usage</th>
                                            <th width="15%" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topics as $topic)
                                            <tr>
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
                            <div style="padding: 20px;">
                                {{ $topics->links() }}
                            </div>
                        @else
                            <!-- Empty State -->
                            <div class="empty-state">
                                <i class="ti-bookmark-alt"></i>
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
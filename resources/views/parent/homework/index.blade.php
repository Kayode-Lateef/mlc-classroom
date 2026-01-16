@extends('layouts.app')

@section('title', 'Homework Assignments')

@push('styles')
<style>
    .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
    }

    .filter-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .empty-state i {
        color: #cbd5e0;
        margin-bottom: 20px;
    }

    .child-selector {
        background: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 2px solid #e9ecef;
    }

    .child-option {
        padding: 10px 15px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
    }

    .child-option:hover {
        background: #f8f9fa;
    }

    .child-option.active {
        background: #e7f3ff;
        border-color: #007bff;
    }

    .student-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        object-fit: cover;
    }

    .student-initial {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.875rem;
        background-color: #007bff;
        color: white;
    }

    .homework-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        height: 100%;
    }

    .homework-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .grade-badge {
        font-size: 1.2rem;
        padding: 8px 15px;
        border-radius: 8px;
        font-weight: bold;
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
                                <h1>Homework Assignments</h1>
                            </div>
                        </div>
                        <span>View your children's homework assignments and progress</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Homework</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    @if($children->isEmpty())
                        <!-- No Children State -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card alert">
                                    <div class="card-body text-center py-5">
                                        <i class="ti-user" style="font-size: 4rem; color: #cbd5e0;"></i>
                                        <h3 class="mt-3 mb-2">No Children Found</h3>
                                        <p class="text-muted mb-4">
                                            You don't have any children registered in the system.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Child Selector -->
                        @if($children->count() > 1)
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="child-selector">
                                    <h5 class="mb-3">Select Child</h5>
                                    <div class="row">
                                        @foreach($children as $child)
                                        <div class="col-md-3">
                                            <a href="{{ route('parent.homework.index', ['child_id' => $child->id]) }}" 
                                               class="child-option {{ $selectedChild && $selectedChild->id == $child->id ? 'active' : '' }}"
                                               style="display: block; text-decoration: none; color: inherit;">
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    @if($child->profile_photo)
                                                        <img src="{{ asset('storage/' . $child->profile_photo) }}" 
                                                             alt="{{ $child->full_name }}" 
                                                             class="student-avatar">
                                                    @else
                                                        <div class="student-initial">
                                                            {{ strtoupper(substr($child->first_name, 0, 1)) }}{{ strtoupper(substr($child->last_name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $child->full_name }}</strong>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($selectedChild && $stats)
                            <!-- Statistics Cards -->
                            <div class="row">
                                <div class="col-lg-2">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="stat-icon dib"><i class="ti-book color-primary border-primary"></i></div>
                                            <div class="stat-content dib">
                                                <div class="stat-text">Total</div>
                                                <div class="stat-digit">{{ $stats['total'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="stat-icon dib"><i class="ti-time color-warning border-warning"></i></div>
                                            <div class="stat-content dib">
                                                <div class="stat-text">Pending</div>
                                                <div class="stat-digit">{{ $stats['pending'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="stat-icon dib"><i class="ti-check color-success border-success"></i></div>
                                            <div class="stat-content dib">
                                                <div class="stat-text">Submitted</div>
                                                <div class="stat-digit">{{ $stats['submitted'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="stat-icon dib"><i class="ti-alert color-danger border-danger"></i></div>
                                            <div class="stat-content dib">
                                                <div class="stat-text">Overdue</div>
                                                <div class="stat-digit">{{ $stats['overdue'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="stat-icon dib"><i class="ti-star color-info border-info"></i></div>
                                            <div class="stat-content dib">
                                                <div class="stat-text">Graded</div>
                                                <div class="stat-digit">{{ $stats['graded'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="stat-icon dib"><i class="ti-stats-up color-pink border-pink"></i></div>
                                            <div class="stat-content dib">
                                                <div class="stat-text">Avg Grade</div>
                                                <div class="stat-digit">{{ $stats['average_grade'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Stats -->
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card alert">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <p><strong>Completion Rate:</strong> {{ $stats['completion_rate'] }}%</p>
                                                    <p><strong>Upcoming (7 days):</strong> {{ $stats['upcoming'] }}</p>
                                                </div>
                                                <div class="col-6">
                                                    <p><strong>Late Submissions:</strong> {{ $stats['late'] }}</p>
                                                    <p><strong>Overdue:</strong> {{ $stats['overdue'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card alert">
                                        <div class="card-body">
                                            <h5>Completion Progress</h5>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar bg-success" style="width: {{ $stats['completion_rate'] }}%">
                                                    {{ $stats['completion_rate'] }}%
                                                </div>
                                            </div>
                                            <small class="text-muted mt-2 d-block">
                                                {{ $stats['submitted'] + $stats['late'] + $stats['graded'] }} of {{ $stats['total'] }} completed
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filters -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="filter-card">
                                        <form method="GET" action="{{ route('parent.homework.index') }}">
                                            <input type="hidden" name="child_id" value="{{ $selectedChild->id }}">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label style="font-weight: 500;">Class</label>
                                                        <select name="class_id" class="form-control">
                                                            <option value="">All Classes</option>
                                                            @foreach($classes as $class)
                                                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                                {{ $class->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label style="font-weight: 500;">Status</label>
                                                        <select name="status" class="form-control">
                                                            <option value="">All Status</option>
                                                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                                                            <option value="graded" {{ request('status') == 'graded' ? 'selected' : '' }}>Graded</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label style="font-weight: 500;">Search</label>
                                                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search homework..." class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <div style="display: flex; gap: 5px;">
                                                            <button type="submit" class="btn btn-primary flex-fill">
                                                                <i class="ti-filter"></i> Filter
                                                            </button>
                                                            <a href="{{ route('parent.homework.index', ['child_id' => $selectedChild->id]) }}" class="btn btn-secondary">
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

                            <!-- Homework Cards (Grid View) -->
                            @if($homework->count() > 0)
                            <div class="row">
                                @foreach($homework as $submission)
                                @php
                                    $assignment = $submission->homeworkAssignment;
                                    $today = now()->format('Y-m-d');
                                    $dueDate = $assignment->due_date->format('Y-m-d');
                                    
                                    if ($dueDate < $today && $submission->status === 'pending') {
                                        $dueBadgeClass = 'badge-danger';
                                        $dueBadgeText = 'Overdue';
                                    } elseif ($dueDate == $today) {
                                        $dueBadgeClass = 'badge-warning';
                                        $dueBadgeText = 'Due Today';
                                    } else {
                                        $dueBadgeClass = 'badge-success';
                                        $dueBadgeText = 'Upcoming';
                                    }
                                @endphp
                                
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <a href="{{ route('parent.homework.show', ['homework' => $assignment->id, 'child_id' => $selectedChild->id]) }}" 
                                       style="text-decoration: none; color: inherit;">
                                        <div class="card homework-card">
                                            <div class="card-header" style="background: #f8f9fa;">
                                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                                    <h5 style="margin: 0; flex: 1;">{{ Str::limit($assignment->title, 40) }}</h5>
                                                    <span class="badge {{ $dueBadgeClass }}" style="margin-left: 10px;">
                                                        {{ $dueBadgeText }}
                                                    </span>
                                                </div>
                                                <div style="margin-top: 8px;">
                                                    <span class="badge badge-light">{{ $assignment->class->name }}</span>
                                                </div>
                                            </div>

                                            <div class="card-body">
                                                @if($assignment->description)
                                                <p style="color: #6c757d; margin-bottom: 15px;">
                                                    {{ Str::limit($assignment->description, 80) }}
                                                </p>
                                                @endif

                                                <div style="margin-bottom: 10px;">
                                                    <i class="ti-user" style="color: #6c757d;"></i>
                                                    <small>{{ $assignment->teacher->name }}</small>
                                                </div>

                                                <div style="margin-bottom: 10px;">
                                                    <i class="ti-calendar" style="color: #6c757d;"></i>
                                                    <small>Due: {{ $assignment->due_date->format('d M Y') }}</small>
                                                    <small class="text-muted">({{ $assignment->due_date->diffForHumans() }})</small>
                                                </div>

                                                @if($assignment->file_path)
                                                <div style="margin-bottom: 15px;">
                                                    <i class="ti-clip" style="color: #007bff;"></i>
                                                    <small style="color: #007bff;">Has attachment</small>
                                                </div>
                                                @endif

                                                <hr style="margin: 15px 0;">

                                                <!-- Submission Status -->
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <strong>Status:</strong>
                                                        @switch($submission->status)
                                                            @case('pending')
                                                                <span class="badge badge-warning">Pending</span>
                                                                @break
                                                            @case('submitted')
                                                                <span class="badge badge-info">Submitted</span>
                                                                @break
                                                            @case('late')
                                                                <span class="badge badge-danger">Late</span>
                                                                @break
                                                            @case('graded')
                                                                <span class="badge badge-success">Graded</span>
                                                                @break
                                                        @endswitch
                                                    </div>
                                                    
                                                    @if($submission->status === 'graded' && $submission->grade)
                                                    <div>
                                                        <span class="grade-badge badge badge-success">
                                                            {{ $submission->grade }}
                                                        </span>
                                                    </div>
                                                    @endif
                                                </div>

                                                @if($submission->status === 'pending')
                                                <div style="margin-top: 15px;">
                                                    <button class="btn btn-primary btn-sm btn-block">
                                                        <i class="ti-upload"></i> Submit Now
                                                    </button>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            @if($homework->hasPages())
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="mt-4">
                                        {{ $homework->appends(request()->query())->links() }}
                                    </div>
                                </div>
                            </div>
                            @endif
                            @else
                            <!-- Empty State -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="empty-state">
                                        <i class="ti-book" style="font-size: 4rem;"></i>
                                        <h3 class="mb-3">No Homework Found</h3>
                                        <p class="text-muted mb-4">No homework assignments match your filters.</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endif
                    @endif

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Homework Assignments</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
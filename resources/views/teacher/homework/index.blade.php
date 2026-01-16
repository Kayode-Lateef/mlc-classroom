@extends('layouts.app')

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
                        <span>Manage and oversee all homework assignments across the system</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Homework</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
              
                    <!-- Create Button -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-right mb-3">
                                <a href="{{ route('teacher.homework.create') }}" class="btn btn-primary">
                                    <i class="ti-plus"></i> Create Homework
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-book color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Homework</div>
                                        <div class="stat-digit">{{ number_format($stats['total_homework']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-calendar color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Upcoming</div>
                                        <div class="stat-digit">{{ number_format($stats['upcoming']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-alarm-clock color-warning border-warning"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Due Today</div>
                                        <div class="stat-digit">{{ number_format($stats['due_today']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-alert color-danger border-danger"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Overdue</div>
                                        <div class="stat-digit">{{ number_format($stats['overdue']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="filter-card">
                                <form method="GET" action="{{ route('teacher.homework.index') }}">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Date From</label>
                                                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Date To</label>
                                                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
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
                                        {{-- <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Teacher</label>
                                                <select name="teacher_id" class="form-control">
                                                    <option value="">All Teachers</option>
                                                    @foreach($teachers as $teacher)
                                                    <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                                        {{ $teacher->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div> --}}
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="">All Status</option>
                                                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                                    <option value="due_today" {{ request('status') == 'due_today' ? 'selected' : '' }}>Due Today</option>
                                                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Search</label>
                                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Title..." class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <a href="{{ route('teacher.homework.index') }}" class="btn btn-secondary btn-sm">
                                            <i class="ti-reload"></i> Clear
                                        </a>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="ti-filter"></i> Apply Filters
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Homework Grid -->
                        @if($homework->count() > 0)
                        <div class="row">
                            @foreach($homework as $assignment)
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="panel lobipanel-basic panel-info">
                                    <div class="panel-heading">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                            <div class="panel-title">
                                                {{ Str::limit($assignment->title, 50) }}
                                            </div>
                                            @php
                                                $today = now()->format('Y-m-d');
                                                $dueDate = $assignment->due_date->format('Y-m-d');
                                                if ($dueDate < $today) {
                                                    $statusClass = 'danger';
                                                    $statusText = 'Overdue';
                                                } elseif ($dueDate == $today) {
                                                    $statusClass = 'warning';
                                                    $statusText = 'Due Today';
                                                } else {
                                                    $statusClass = 'success';
                                                    $statusText = 'Upcoming';
                                                }
                                            @endphp
                                            <span class="label-{{ $statusClass }} status-badge">{{ $statusText }}</span>
                                        </div>
                                        <span class="badge badge-light" style="font-size: 1rem;">{{ $assignment->class->name }}</span>
                            
                                    </div>

                                    <!-- Description -->
                                    @if($assignment->description)
                                    <div style="padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                                        <p style="margin: 0; color: #6c757d; line-height: 1.5;">
                                            {{ Str::limit($assignment->description, 100) }}
                                        </p>
                                    </div>
                                    @endif

                                    <div class="panel-body">
                                        <div style="padding: 15px;">
                                            <div style="margin-bottom: 10px;">
                                                <i class="ti-calendar" style="color: #6c757d; margin-right: 8px;"></i>
                                                <span style="color: #495057;">
                                                    <strong>Assigned:</strong> {{ $assignment->assigned_date->format('d M Y') }}
                                                </span>
                                            </div>
                                            <div style="margin-bottom: 10px;">
                                                <i class="ti-alarm-clock" style="color: #6c757d; margin-right: 8px;"></i>
                                                <span style="color: #495057;">
                                                    <strong>Due:</strong> {{ $assignment->due_date->format('d M Y') }}
                                                </span>
                                                <span style="font-size: 1rem; color: #6c757d;">({{ $assignment->due_date->diffForHumans() }})</span>
                                            </div>
                                            <div style="margin-bottom: 10px;">
                                                <i class="ti-user" style="color: #6c757d; margin-right: 8px;"></i>
                                                <span style="color: #495057;">{{ $assignment->teacher->name }}</span>
                                            </div>
                                            @if($assignment->file_path)
                                            <div>
                                                <i class="ti-clip" style="color: #007bff; margin-right: 8px;"></i>
                                                <span style="color: #007bff;">Has attachment</span>
                                            </div>
                                            @endif
                                        </div>

                                        <!-- Submission Progress -->
                                        <div style="padding: 15px; background-color: #f8f9fa; border-top: 1px solid #e9ecef;">
                                            @php
                                                $totalStudents = $assignment->submissions->count();
                                                $submitted = $assignment->submissions->whereIn('status', ['submitted', 'late', 'graded'])->count();
                                                $graded = $assignment->submissions->where('status', 'graded')->count();
                                                $submissionRate = $totalStudents > 0 ? round(($submitted / $totalStudents) * 100) : 0;
                                            @endphp

                                            <div style="margin-bottom: 10px;">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                                    <span style="color: #6c757d;">Submission Progress</span>
                                                    <span style="font-weight: 600; color: #495057;">{{ $submitted }}/{{ $totalStudents }} ({{ $submissionRate }}%)</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-primary w-{{ $submissionRate }}" role="progressbar" aria-valuenow="{{ $submissionRate }}" aria-valuemin="0" aria-valuemax="100">
                                                        
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="display: flex; justify-content: space-between; color: #6c757d;">
                                                <div>
                                                    <i class="ti-check" style="color: #28a745; margin-right: 4px;"></i>
                                                    <span>{{ $graded }} graded</span>
                                                </div>
                                                <div>
                                                    <i class="ti-user" style="margin-right: 4px;"></i>
                                                    <span>{{ $totalStudents }} students</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div style="padding: 15px; border-top: 1px solid #e9ecef;">
                                            <a href="{{ route('teacher.homework.show', $assignment) }}" class="btn btn-primary btn-sm flex-fill">
                                                <i class="ti-eye"></i> View Details
                                            </a>
                                            <a href="{{ route('teacher.homework.edit', $assignment) }}" class="btn btn-success btn-sm">
                                                <i class="ti-pencil-alt"></i>
                                            </a>
                                            @if($assignment->file_path)
                                            <a href="{{ route('teacher.homework.download', $assignment) }}" class="btn btn-success btn-sm" title="Download">
                                                <i class="ti-download"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
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
                                <i class="ti-book"></i>
                                <h3 class="mb-3">No Homework Assignments Found</h3>
                                <p class="text-muted mb-4">Get started by creating your first homework assignment.</p>
                                <a href="{{ route('teacher.homework.create') }}" class="btn btn-primary">
                                    <i class="ti-plus"></i> Create Homework
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif



                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Homework Management</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
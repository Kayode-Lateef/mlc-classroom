@extends('layouts.app')

@section('title', 'Classes Management')

@push('styles')
    <style>
        .filter-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .class-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .class-card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: white;
        }

        .class-card-header h3 {
            margin: 0 0 5px 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .class-card-header p {
            margin: 0;
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .class-card-body {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .class-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 1.4rem;
            color: #6c757d;
        }

        .class-info-item i {
            margin-right: 8px;
            font-size: 1.6rem;
            width: 16px;
            text-align: center;
        }

        .capacity-bar {
            margin: 15px 0;
        }

        .capacity-bar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
            font-size: 1.2rem;
            color: #6c757d;
        }

        .capacity-bar-track {
            width: 100%;
            background-color: #e9ecef;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }

        .capacity-bar-fill {
            height: 100%;
            background-color: #007bff;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .capacity-bar-fill.warning {
            background-color: #ffc107;
        }

        .capacity-bar-fill.full {
            background-color: #dc3545;
        }

        .class-actions {
            display: flex;
            gap: 8px;
            margin-top: auto;
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
            color: #cbd5e0;
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
                                <h1>Classes Management</h1>
                            </div>
                        </div>
                        <span>Manage and view all classes in the system</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Classes</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-primary">
                                        <i class="ti-blackboard"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ $stats['total'] }}</div>
                                        <div class="stat-text">Total Classes</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-success">
                                        <i class="ti-check"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ $stats['with_teacher'] }}</div>
                                        <div class="stat-text">With Teacher</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-warning">
                                        <i class="ti-alert"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ $stats['without_teacher'] }}</div>
                                        <div class="stat-text">No Teacher</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="card p-0">
                                <div class="stat-widget-three">
                                    <div class="stat-icon bg-info">
                                        <i class="ti-user"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-digit">{{ $stats['total_enrolled'] }}</div>
                                        <div class="stat-text">Total Students</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Class Button -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-right mb-3">
                                <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
                                    <i class="ti-plus"></i> Add New Class
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="filter-card">
                                <form method="GET" action="{{ route('admin.classes.index') }}">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Search</label>
                                                <input type="text" name="search" class="form-control" placeholder="Class name, room..." value="{{ request('search') }}">
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Subject</label>
                                                <select name="subject" class="form-control">
                                                    <option value="">All Subjects</option>
                                                    @foreach($subjects as $subject)
                                                        <option value="{{ $subject }}" {{ request('subject') == $subject ? 'selected' : '' }}>
                                                            {{ $subject }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Level</label>
                                                <select name="level" class="form-control">
                                                    <option value="">All Levels</option>
                                                    @foreach($levels as $level)
                                                        <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                                            {{ $level }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Teacher</label>
                                                <select name="teacher_id" class="form-control">
                                                    <option value="">All Teachers</option>
                                                    @foreach($teachers as $teacher)
                                                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                                            {{ $teacher->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Sort By</label>
                                                <select name="sort_by" class="form-control">
                                                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                                                    <option value="subject" {{ request('sort_by') == 'subject' ? 'selected' : '' }}>Subject</option>
                                                    <option value="capacity" {{ request('sort_by') == 'capacity' ? 'selected' : '' }}>Capacity</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    <i class="ti-search"></i>
                                                </button>
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
                                <div class="alert alert-success fade in alert-dismissable">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class="ti-check"></i> {{ session('success') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="alert alert-danger fade in alert-dismissable">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class="ti-alert"></i> {{ session('error') }}
                                </div>
                            </div>
                        </div>
                    @endif



                    <!-- Classes Grid -->
                    @if($classes->count() > 0)
                    <div class="row">
                        @foreach($classes as $class)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="panel lobipanel-basic panel-info">
                                <div class="panel-heading">
                                    <div class="panel-title">
                                        <h3>{{ $class->name }}</h3>
                                        <p>{{ $class->subject }}</p>
                                    </div>
                                </div>
                                <div class="panel-body">
                                     <!-- Class Information -->
                                    <div class="class-info-item">
                                        <i class="ti-user"></i>
                                        <span>Teacher: <strong>{{ $class->teacher->name ?? 'Not assigned' }}</strong></span>
                                    </div>
                                    <div class="class-info-item">
                                        <i class="ti-id-badge"></i>
                                        <span>Students: <strong>{{ $class->enrollments()->where('status', 'active')->count() }} / {{ $class->capacity }}</strong></span>
                                    </div>

                                    @if($class->room_number)
                                    <div class="class-info-item">
                                        <i class="ti-home"></i>
                                        <span>Room: <strong>{{ $class->room_number }}</strong></span>
                                    </div>
                                    @endif

                                    @if($class->level)
                                    <div class="class-info-item">
                                        <i class="ti-bar-chart"></i>
                                        <span>Level: <strong>{{ $class->level }}</strong></span>
                                    </div>
                                    @endif

                                    <!-- Capacity Bar -->
                                    @php
                                        $enrolledCount = $class->enrollments()->where('status', 'active')->count();
                                        $fillPercentage = $class->capacity > 0 ? ($enrolledCount / $class->capacity) * 100 : 0;
                                        $barClass = '';
                                        if ($fillPercentage >= 100) {
                                            $barClass = 'full';
                                        } elseif ($fillPercentage >= 80) {
                                            $barClass = 'warning';
                                        }
                                    @endphp
                                    <div class="capacity-bar">
                                        <div class="capacity-bar-header">
                                            <span>Capacity</span>
                                            <span><strong>{{ number_format($fillPercentage, 0) }}%</strong> Full</span>
                                        </div>
                                        <div class="capacity-bar-track">
                                            <div class="capacity-bar-fill {{ $barClass }}" style="width: {{ min($fillPercentage, 100) }}%"></div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="class-actions">
                                        <a href="{{ route('admin.classes.show', $class) }}" class="btn btn-primary btn-sm flex-fill">
                                            <i class="ti-eye"></i> View Details
                                        </a>
                                        <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-success btn-sm">
                                            <i class="ti-pencil-alt"></i>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                        @endforeach
                       
                    </div>

                    <!-- Pagination -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mt-4">
                                {{ $classes->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- Empty State -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="empty-state">
                                <i class="ti-blackboard"></i>
                                <h3 class="mb-3">No Classes Found</h3>
                                <p class="text-muted mb-4">Get started by adding your first class.</p>
                                <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
                                    <i class="ti-plus"></i> Add Class
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Classes Management</p>
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
@extends('layouts.app')

@section('title', 'Progress Sheets')

@push('styles')
    <style>
        .filter-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Force proper grid layout */
        .progress-sheets-grid {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }

        .progress-sheet-col {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
            padding: 0 15px;
            margin-bottom: 30px;
        }

        @media (max-width: 991px) {
            .progress-sheet-col {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (max-width: 767px) {
            .progress-sheet-col {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        .progress-sheet-card {
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .progress-sheet-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .progress-sheet-card .card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .progress-sheet-title {
            font-weight: 600;
            color: #212529;
            margin-bottom: 8px;
            line-height: 1.4;
            font-size: 18px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 50px;
        }

        .progress-sheet-meta {
            color: #6c757d;
            font-size: 14px;
        }

        .progress-sheet-meta i {
            margin-right: 5px;
            width: 16px;
            text-align: center;
        }

        .progress-sheet-meta > div {
            margin-bottom: 5px;
        }

        .performance-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            min-height: 32px;
            margin-top: auto;
            padding-top: 12px;
        }

        .performance-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 13px;
        }

        .badge-excellent {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-good {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .badge-average {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-struggling {
            background-color: #fff3cd;
            color: #ff6b6b;
            border: 1px solid #ff6b6b;
        }

        .badge-absent {
            background-color: #f8f9fa;
            color: #6c757d;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
        }

        .action-buttons .flex-fill {
            flex: 1;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            color: #cbd5e0;
            margin-bottom: 20px;
            font-size: 72px;
        }

        .stat-widget-one {
            padding: 20px;
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
                                <h1>Progress Sheets</h1>
                            </div>
                        </div>
                        <span>Manage and oversee all progress sheets across the system</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Progress Sheets</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="stat-widget-one" style="display: flex; align-items: center;">
                                <div class="stat-icon dib"><i class="ti-clipboard color-primary border-primary"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">Total Sheets</div>
                                    <div class="stat-digit">{{ number_format($stats['total_sheets']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="stat-widget-one" style="display: flex; align-items: center;">
                                <div class="stat-icon dib"><i class="ti-pencil-alt color-pink border-pink"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">Student Notes</div>
                                    <div class="stat-digit">{{ number_format($stats['total_notes']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="stat-widget-one" style="display: flex; align-items: center;">
                                <div class="stat-icon dib"><i class="ti-calendar color-success border-success"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">This Week</div>
                                    <div class="stat-digit">{{ number_format($stats['this_week']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="stat-widget-one" style="display: flex; align-items: center;">
                                <div class="stat-icon dib"><i class="ti-stats-up color-orange border-orange"></i></div>
                                <div class="stat-content dib">
                                    <div class="stat-text">This Month</div>
                                    <div class="stat-digit">{{ number_format($stats['this_month']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Button -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-right mb-3">
                            <a href="{{ route('superadmin.progress-sheets.create') }}" class="btn btn-primary">
                                <i class="ti-plus"></i> Create Progress Sheet
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="filter-card">
                            <form method="GET" action="{{ route('superadmin.progress-sheets.index') }}">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Date From</label>
                                            <input 
                                                type="date" 
                                                name="date_from" 
                                                value="{{ $dateFrom }}" 
                                                class="form-control"
                                            >
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Date To</label>
                                            <input 
                                                type="date" 
                                                name="date_to" 
                                                value="{{ $dateTo }}" 
                                                max="{{ now()->format('Y-m-d') }}"
                                                class="form-control"
                                            >
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Class</label>
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
                                            <label>Search</label>
                                            <input 
                                                type="text" 
                                                name="search" 
                                                value="{{ request('search') }}" 
                                                placeholder="Topic..."
                                                class="form-control"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <a href="{{ route('superadmin.progress-sheets.index') }}" class="btn btn-secondary">
                                        <i class="ti-reload"></i> Clear
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti-filter"></i> Apply Filters
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Progress Sheets Grid -->
                @if($progressSheets->count() > 0)
                    <div class="row">
                        <div class="col-12">
                            <div class="progress-sheets-grid">
                                @foreach($progressSheets as $sheet)
                                <div class="progress-sheet-col">
                                    <div class="card progress-sheet-card">
                                        <div class="card-body">
                                            <!-- Header -->
                                            <div class="mb-3">
                                                <h3 class="progress-sheet-title">{{ $sheet->topic }}</h3>
                                                <span class="badge badge-primary">{{ $sheet->class->name }}</span>
                                            </div>

                                            <!-- Objective -->
                                            @if($sheet->objective)
                                            <p style="color: #6c757d; margin-bottom: 12px; line-height: 1.5;">
                                                {{ Str::limit($sheet->objective, 100) }}
                                            </p>
                                            @endif

                                            <!-- Meta Info -->
                                            <div class="progress-sheet-meta mb-3">
                                                <div>
                                                    <i class="ti-calendar"></i>
                                                    {{ \Carbon\Carbon::parse($sheet->date)->format('d M Y') }}
                                                </div>
                                                <div>
                                                    <i class="ti-user"></i>
                                                    {{ $sheet->teacher->name }}
                                                </div>
                                                @if($sheet->schedule)
                                                <div>
                                                    <i class="ti-time"></i>
                                                    {{ $sheet->schedule->day_of_week }} • 
                                                    {{ \Carbon\Carbon::parse($sheet->schedule->start_time)->format('H:i') }} - 
                                                    {{ \Carbon\Carbon::parse($sheet->schedule->end_time)->format('H:i') }}
                                                </div>
                                                @endif
                                            </div>

                                            <!-- Performance Summary -->
                                            @if($sheet->progressNotes->count() > 0)
                                            <div class="performance-badges">
                                                @php
                                                    $excellent = $sheet->progressNotes->where('performance', 'excellent')->count();
                                                    $good = $sheet->progressNotes->where('performance', 'good')->count();
                                                    $average = $sheet->progressNotes->where('performance', 'average')->count();
                                                    $struggling = $sheet->progressNotes->where('performance', 'struggling')->count();
                                                    $absent = $sheet->progressNotes->where('performance', 'absent')->count();
                                                @endphp
                                                
                                                @if($excellent > 0)
                                                <span class="performance-badge badge-excellent">
                                                    ✨ {{ $excellent }}
                                                </span>
                                                @endif
                                                @if($good > 0)
                                                <span class="performance-badge badge-good">
                                                    ✓ {{ $good }}
                                                </span>
                                                @endif
                                                @if($average > 0)
                                                <span class="performance-badge badge-average">
                                                    ~ {{ $average }}
                                                </span>
                                                @endif
                                                @if($struggling > 0)
                                                <span class="performance-badge badge-struggling">
                                                    ⚠ {{ $struggling }}
                                                </span>
                                                @endif
                                                @if($absent > 0)
                                                <span class="performance-badge badge-absent">
                                                    ✗ {{ $absent }}
                                                </span>
                                                @endif
                                            </div>
                                            @endif

                                            <!-- Actions -->
                                            <div class="action-buttons">
                                                <a href="{{ route('superadmin.progress-sheets.show', $sheet) }}" class="btn btn-primary btn-sm flex-fill">
                                                    <i class="ti-eye"></i> View
                                                </a>
                                                <a href="{{ route('superadmin.progress-sheets.edit', $sheet) }}" class="btn btn-success btn-sm">
                                                    <i class="ti-pencil-alt"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    @if($progressSheets->hasPages())
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mt-4">
                                {{ $progressSheets->appends(request()->query())->links() }} 
                            </div>
                        </div>
                    </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="empty-state">
                                <i class="ti-clipboard"></i>
                                <h3 class="mb-3">No Progress Sheets Found</h3>
                                <p class="text-muted mb-4">Get started by creating your first progress sheet.</p>
                                <a href="{{ route('superadmin.progress-sheets.create') }}" class="btn btn-primary">
                                    <i class="ti-plus"></i> Create Progress Sheet
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Footer -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer">
                            <p>MLC Classroom - Progress Sheets Management</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@extends('layouts.app')

@section('title', 'Class Schedules')

@push('styles')
    <style>
        .schedule-card {
            transition: all 0.3s ease;
            border-left: 4px solid #007bff;
        }
        
        .schedule-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .day-column {
            min-height: 400px;
            background-color: #fff;
        }
        
        .schedule-time {
            color: #6c757d;
        }
        
        .recurring-badge {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #28a745;
            display: inline-block;
        }

        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background-color: #dee2e6;
        }
        
        .conflict-indicator {
            background-color: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
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
                                <h1>Class Schedules</h1>     
                            </div>
                        </div>
                        <span>Manage class schedules and timetables</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Schedules</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                </div>

                <div id="main-content">                    
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-calendar color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Schedules</div>
                                        <div class="stat-digit">{{ $stats['total_schedules'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-blackboard color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Unique Classes</div>
                                        <div class="stat-digit">{{ $stats['unique_classes'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-time color-info border-info"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">This Week</div>
                                        <div class="stat-digit">{{ $stats['this_week'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                       
                    </div>
                       <!-- Statistics Cards -->
                    <div class="row">                      
                        <div class="col-lg-4 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-alert {{ $stats['conflicts'] > 0 ? 'color-warning border-warning' : 'color-success border-success' }}"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Conflicts</div>
                                        <div class="stat-digit {{ $stats['conflicts'] > 0 ? 'text-danger' : 'text-success' }}"> {{ $stats['conflicts'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                         <div class="col-lg-4 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-user color-info border-info"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Active Teachers</div>
                                        <div class="stat-digit">{{ $stats['active_teachers'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one" style="display: flex; align-items: center;">
                                    <div class="stat-icon dib"><i class="ti-reload color-pink border-pink"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Recurring Schedules</div>
                                        <div class="stat-digit">{{ $stats['recurring_schedules'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- View Toggle & Filters Card -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4>
                                        <i class="ti-filter"></i> Filters & View Options
                                    </h4>
                                    <div class="card-header-right-icon">
                                        <a href="{{ route('superadmin.schedules.create') }}" class="btn btn-primary btn-sm">
                                            <i class="ti-plus"></i> Add Schedule
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="{{ route('superadmin.schedules.index') }}">
                                        <!-- View Type Toggle -->
                                        <div class="row mb-4">
                                            <div class="col-lg-12">
                                                <div class="card-toggle-body">
                                                    <div class="button-list">
                                                        <div class="btn-group" role="group">
                                                            <button type="submit" name="view" value="calendar" class="btn {{ $viewType === 'calendar' ? 'active btn-primary' : 'btn-outline-primary' }}"><i class="ti-calendar"></i> Calendar View</button>
                                                            <button type="submit" name="view" value="list" class="btn {{ $viewType === 'list' ? 'active btn-primary' : 'btn-outline-primary' }}"><i class="ti-view-list"></i> List View</button>
                                                        </div>
                                                         <a href="{{ route('superadmin.schedules.index') }}" class="btn btn-secondary btn-sm float-right">
                                                            <i class="ti-reload"></i> Clear Filters
                                                        </a>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>

                                        <!-- Filters -->
                                        <div class="row">
                                            <!-- Class Filter -->
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Filter by Class</label>
                                                    <select name="class_id" class="form-control">
                                                        <option value="">All Classes</option>
                                                        @foreach($classes as $class)
                                                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                            {{ $class->name }} @if($class->teacher) - {{ $class->teacher->name }} @endif
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Day Filter -->
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Filter by Day</label>
                                                    <select name="day_of_week" class="form-control">
                                                        <option value="">All Days</option>
                                                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                                        <option value="{{ $day }}" {{ request('day_of_week') == $day ? 'selected' : '' }}>{{ $day }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Teacher Filter -->
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Filter by Teacher</label>
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
                                        </div>

                                        <div class="text-right">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti-search"></i> Apply Filters
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Calendar View -->
                    @if($viewType === 'calendar')
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-calendar"></i> Weekly Schedule</h4>
                                </div>
                                <div class="card-body p-0">
                                    <div class="calendar-grid">
                                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                        <div class="day-column p-3">
                                            <h5 class="mb-3 pb-2 border-bottom">
                                                <i class="ti-time"></i> {{ $day }}
                                            </h5>
                                            
                                            <div>
                                                @php
                                                    $daySchedules = isset($schedulesByDay) ? ($schedulesByDay->get($day) ?? collect()) : collect();
                                                @endphp
                                                
                                                @if($daySchedules->count() > 0)
                                                    @foreach($daySchedules->sortBy('start_time') as $schedule)
                                                    <div class="schedule-card bg-light p-3 mb-3 rounded">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="mb-0">{{ $schedule->class->name }}</h6>
                                                            @if($schedule->recurring)
                                                            <span class="recurring-badge" title="Recurring"></span>
                                                            @endif
                                                        </div>
                                                        
                                                        <p class="schedule-time mb-2">
                                                            <i class="ti-time"></i>
                                                            {{ $schedule->start_time->format('H:i') }} - 
                                                            {{ $schedule->end_time->format('H:i') }}
                                                        </p>
                                                        
                                                        @if($schedule->class->teacher)
                                                        <p class="text-muted mb-2">
                                                            <i class="ti-user"></i>
                                                            {{ $schedule->class->teacher->name }}
                                                        </p>
                                                        @endif
                                                        
                                                        @if($schedule->class->room_number)
                                                        <p class="text-muted mb-2">
                                                            <i class="ti-home"></i>
                                                            Room {{ $schedule->class->room_number }}
                                                        </p>
                                                        @endif
                                                        
                                                        <div class="mt-2 pt-2 border-top">
                                                            <a href="{{ route('superadmin.schedules.edit', $schedule) }}" class="btn btn-sm btn-success mr-1">
                                                                <i class="ti-pencil"></i>
                                                            </a>
                                                       <form action="{{ route('superadmin.schedules.destroy', $schedule) }}" 
                                                                method="POST" 
                                                                style="display: inline-block;"
                                                                class="delete-schedule-form"
                                                                data-class-name="{{ $schedule->class->name }}"
                                                                data-day="{{ $schedule->day_of_week }}"
                                                                data-time="{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="ti-trash"></i> Delete
                                                                </button>
                                                        </form>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                @else
                                                    <p class="text-muted text-center py-4">
                                                        <i class="ti-info-alt"></i><br>
                                                        No classes scheduled
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- List View -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-view-list"></i> Schedule List</h4>
                                </div>
                                <div class="card-body">
                                    @if($schedules->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Class</th>
                                                    <th>Teacher</th>
                                                    <th>Day</th>
                                                    <th>Time</th>
                                                    <th>Room</th>
                                                    <th>Recurring</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($schedules as $schedule)
                                                <tr>
                                                    <td>{{ $loop->iteration + ($schedules->currentPage() - 1) * $schedules->perPage() }}</td>
                                                    <td>
                                                        <strong>{{ $schedule->class->name }}</strong><br>
                                                        <small class="text-muted">{{ $schedule->class->subject }}</small>
                                                    </td>
                                                    <td>
                                                        {{ $schedule->class->teacher ? $schedule->class->teacher->name : 'Not assigned' }}
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-primary">
                                                            {{ $schedule->day_of_week }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        {{ $schedule->start_time->format('H:i') }} - 
                                                        {{ $schedule->end_time->format('H:i') }}
                                                    </td>
                                                    <td>
                                                        {{ $schedule->class->room_number ?? '-' }}
                                                    </td>
                                                    <td>
                                                        @if($schedule->recurring)
                                                        <span class="badge badge-success">Yes</span>
                                                        @else
                                                        <span class="badge badge-secondary">No</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('superadmin.schedules.edit', $schedule) }}" class="btn btn-sm btn-success" title="Edit">
                                                            <i class="ti-pencil"></i>
                                                        </a>
                                                       <form action="{{ route('superadmin.schedules.destroy', $schedule) }}" 
                                                                method="POST" 
                                                                style="display: inline-block;"
                                                                class="delete-schedule-form"
                                                                data-class-name="{{ $schedule->class->name }}"
                                                                data-day="{{ $schedule->day_of_week }}"
                                                                data-time="{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="ti-trash"></i> Delete
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
                                        {{ $schedules->appends(request()->query())->links() }}
                                    </div>
                                    @else
                                    <!-- Empty State -->
                                    <div class="text-center py-5">
                                        <i class="ti-calendar" style="font-size: 4rem; color: #cbd5e0;"></i>
                                        <h3 class="mt-3 mb-2">No Schedules Found</h3>
                                        <p class="text-muted mb-4">Get started by creating your first schedule.</p>
                                        <a href="{{ route('superadmin.schedules.create') }}" class="btn btn-primary">
                                            <i class="ti-plus"></i> Add Schedule
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Schedules Management</p>
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
            // Smooth hover effects
            $('.schedule-card').hover(
                function() {
                    $(this).addClass('shadow');
                },
                function() {
                    $(this).removeClass('shadow');
                }
            );

            // ✅ FIXED: Single unified delete handler for both calendar and list views
            $('.delete-schedule-form').on('submit', function(e) {
                e.preventDefault();
                
                const form = this;
                const className = $(this).data('class-name');
                const dayOfWeek = $(this).data('day');
                const time = $(this).data('time');
                
                swal({
                    title: "Delete Schedule?",
                    text: "Are you sure you want to delete this schedule?\n\nClass: " + className + "\nDay: " + dayOfWeek + "\nTime: " + time + "\n\nThis action cannot be undone!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function(isConfirm){
                    if (isConfirm) {
                        form.submit();
                    }
                });
                
                return false;
            });
        });
    </script>
@endpush
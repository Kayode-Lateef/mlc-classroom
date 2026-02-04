@extends('layouts.app')

@section('title', 'My Children')

@push('styles')
    <style>
        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: 100%;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-label {
            color: #6c757d;
            margin-bottom: 3px;
        }

        .stat-value {
            font-weight: bold;
        }

        .filter-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .child-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .child-avatar-initial {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .children-grid-row {
            display: flex;
            flex-wrap: wrap;
            margin-left: -15px;
            margin-right: -15px;
        }

        .child-card-col {
            display: flex;
            flex-direction: column;
            padding-left: 15px;
            padding-right: 15px;
            margin-bottom: 30px;
        }

        .child-card-col .panel {
            display: flex;
            flex-direction: column;
            height: 100%; /* ✅ Makes all cards same height */
            margin-bottom: 0;
        }

        .child-card-col .panel-body {
            display: flex;
            flex-direction: column;
            flex-grow: 1; /* ✅ Fills available space */
        }

        .role-badge {
            padding: 4px 8px;
        }

        .related-info {
            color: #6c757d;
        }

        .action-buttons a,
        .action-buttons button {
            margin: 0 2px;
        }

        .child-card {
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .child-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .progress-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .progress-bar-custom {
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background-color: #28a745;
            transition: width 0.3s;
        }

        .quick-stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .quick-stat-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
        }

        .quick-stat-value {
            font-weight: bold;
            display: block;
            margin-bottom: 3px;
        }

        .quick-stat-label {
            color: #6c757d;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
        }

        .info-label {
            color: #6c757d;
            display: block;
            margin-bottom: 3px;
        }

        .info-value {
            font-weight: bold;
            display: block;
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

        .action-buttons-row {
            display: flex;
            gap: 10px;
            margin-top: auto; /* Pushes to bottom */
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }

        .attendance-progress-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .alert-mini {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
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
                                <h1>My Children</h1>
                            </div>
                        </div>
                        <span>View your children's academic information</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                                    <li class="active">My Children</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    @if($children->count() > 0)
                        <!-- Statistics Cards -->

                        <div class="row mb-4">
                             <div class="col-lg-3">
                                <div class="card">
                                    <div class="stat-widget-one">
                                       <div class="stat-icon dib"><i class="ti-user color-primary border-primary"></i></div>
                                        <div class="stat-content dib">
                                            <div class="stat-text">Total Children</div>
                                            <div class="stat-digit">{{ $children->count() }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="card">
                                    <div class="stat-widget-one">
                                        <div class="stat-icon dib"><i class="ti-book color-success border-success"></i></div>
                                        <div class="stat-content dib">
                                            <div class="stat-text">Total Classes</div>
                                            <div class="stat-digit">{{ $children->sum('enrolled_classes') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="card">
                                    <div class="stat-widget-one">
                                        <div class="stat-icon dib"><i class="ti-alarm-clock color-warning border-warning"></i></div>
                                        <div class="stat-content dib">
                                            <div class="stat-text">Pending Homework</div>
                                            <div class="stat-digit">{{ $children->sum('pending_homework') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="card">
                                    <div class="stat-widget-one">
                                        <div class="stat-icon dib"><i class="ti-stats-up color-info border-info"></i></div>
                                        <div class="stat-content dib">
                                            <div class="stat-text">Avg Attendance</div>
                                            <div class="stat-digit">{{ round($children->avg('attendance_rate'), 1) }}%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Children Cards Grid -->
                        <div class="children-grid-row">
                            @foreach($children as $child)
                            <div class="col-lg-6 child-card-col">
                                <div class="panel lobipanel-basic panel-info child-card">
                                    <!-- Child Header -->
                                    <div class="panel-heading">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                            <div class="panel-title" style="display: flex; align-items: center; gap: 15px;">
                                                @if($child->profile_photo)
                                                    <img src="{{ asset('storage/' . $child->profile_photo) }}" 
                                                         alt="{{ $child->full_name }}" 
                                                         class="child-avatar">
                                                @else
                                                    <div class="child-avatar-initial bg-primary text-white">
                                                        {{ strtoupper(substr($child->first_name, 0, 1)) }}{{ strtoupper(substr($child->last_name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <h4 style="margin: 0;">{{ $child->full_name }}</h4>
                                                    <small class="">
                                                        Student ID: {{ $child->id }} • Age: {{ $child->age }} years
                                                    </small>
                                                </div>
                                            </div>
                                            <span class="badge badge-success">Active</span>
                                        </div>
                                    </div>

                                    <div class="panel-body">
                                        <!-- Quick Stats -->
                                        <div class="quick-stats-grid">
                                            <div class="quick-stat-item">
                                                <span class="quick-stat-value text-primary">{{ $child->enrolled_classes }}</span>
                                                <span class="quick-stat-label">Enrolled Classes</span>
                                            </div>
                                            <div class="quick-stat-item">
                                                <span class="quick-stat-value text-success">{{ $child->attendance_rate }}%</span>
                                                <span class="quick-stat-label">Attendance Rate</span>
                                            </div>
                                        </div>

                                        <!-- Attendance Progress -->
                                        <div class="attendance-progress-container">
                                            <div class="progress-stat">
                                                <span style="color: #6c757d;">This Month's Attendance</span>
                                                <span style="font-weight: bold; color: #495057;">{{ $child->attendance_rate }}%</span>
                                            </div>
                                            <div class="progress-bar-custom">
                                                <div class="progress-fill" style="width: {{ $child->attendance_rate }}%;"></div>
                                            </div>
                                        </div>

                                        <!-- Info Grid -->
                                        <div class="info-grid">
                                            <div class="info-item">
                                                <span class="info-label">Date of Birth</span>
                                                <span class="info-value">{{ $child->date_of_birth->format('d M Y') }}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Enrolled Since</span>
                                                <span class="info-value">{{ $child->enrollment_date->format('d M Y') }}</span>
                                            </div>
                                        </div>

                                        <!-- Pending Homework Alert -->
                                        @if($child->pending_homework > 0)
                                        <div class="alert alert-warning alert-mini">
                                            <i class="ti-alert" style="margin-right: 5px;"></i>
                                            <strong>{{ $child->pending_homework }}</strong> pending homework assignment(s)
                                        </div>
                                        @endif

                                        <!-- Action Buttons -->
                                        <div class="action-buttons-row">
                                            <a href="{{ route('parent.students.show', $child) }}" class="btn btn-primary btn-sm flex-fill">
                                                <i class="ti-eye"></i> View Details
                                            </a>
                                            <a href="{{ route('parent.attendance.index', ['child_id' => $child->id]) }}" class="btn btn-info btn-sm flex-fill">
                                                <i class="ti-clipboard"></i> Attendance
                                            </a>
                                            <a href="{{ route('parent.homework.index', ['child_id' => $child->id]) }}" class="btn btn-success btn-sm">
                                                <i class="ti-book"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Summary Table -->
                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="panel lobipanel-basic panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <h4>Children Summary</h4>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Name</th>
                                                        <th>Age</th>
                                                        <th>Enrolled Classes</th>
                                                        <th>Attendance Rate</th>
                                                        <th>Pending Homework</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($children as $child)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>
                                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                                @if($child->profile_photo)
                                                                    <img src="{{ asset('storage/' . $child->profile_photo) }}" 
                                                                         alt="{{ $child->full_name }}" 
                                                                         style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
                                                                @else
                                                                    <div style="width: 35px; height: 35px; border-radius: 50%; background: #007bff; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.875rem;">
                                                                        {{ strtoupper(substr($child->first_name, 0, 1)) }}{{ strtoupper(substr($child->last_name, 0, 1)) }}
                                                                    </div>
                                                                @endif
                                                                <strong>{{ $child->full_name }}</strong>
                                                            </div>
                                                        </td>
                                                        <td>{{ $child->age }} years</td>
                                                        <td>
                                                            <span class="badge badge-info">{{ $child->enrolled_classes }}</span>
                                                        </td>
                                                        <td>
                                                            @if($child->attendance_rate >= 90)
                                                                <span class="badge badge-success">{{ $child->attendance_rate }}%</span>
                                                            @elseif($child->attendance_rate >= 75)
                                                                <span class="badge badge-warning">{{ $child->attendance_rate }}%</span>
                                                            @else
                                                                <span class="badge badge-danger">{{ $child->attendance_rate }}%</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($child->pending_homework > 0)
                                                                <span class="badge badge-warning">{{ $child->pending_homework }}</span>
                                                            @else
                                                                <span class="badge badge-success">0</span>
                                                            @endif
                                                        </td>
                                                        <td class="action-buttons">
                                                            <a href="{{ route('parent.students.show', $child) }}" 
                                                               class="btn btn-sm btn-info" 
                                                               title="View Details">
                                                                <i class="ti-eye"></i>
                                                            </a>
                                                            <a href="{{ route('parent.attendance.index', ['child_id' => $child->id]) }}" 
                                                               class="btn btn-sm btn-primary" 
                                                               title="Attendance">
                                                                <i class="ti-clipboard"></i>
                                                            </a>
                                                            <a href="{{ route('parent.homework.index', ['child_id' => $child->id]) }}" 
                                                               class="btn btn-sm btn-success" 
                                                               title="Homework">
                                                                <i class="ti-book"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="empty-state">
                                    <i class="ti-user" style="font-size: 5rem;"></i>
                                    <h3 class="mb-3">No Children Found</h3>
                                    <p class="text-muted mb-4">
                                        You don't have any children registered in the system yet.
                                    </p>
                                    <p class="text-muted">
                                        Please contact the school administrator to register your children.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - My Children</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
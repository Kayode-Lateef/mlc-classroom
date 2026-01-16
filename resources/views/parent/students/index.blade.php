@extends('layouts.app')

@section('title', 'My Children')

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
            font-size: 1.2rem;
        }

        .role-badge {
            font-size: 0.75rem;
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
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: box-shadow 0.2s;
        }

        .child-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
                        <!-- Children Cards -->
                        <div class="row">
                            @foreach($children as $child)
                            <div class="col-lg-6">
                                <div class="child-card card">
                                    <!-- Child Header -->
                                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                                        @if($child->profile_photo)
                                            <img src="{{ asset('storage/' . $child->profile_photo) }}" 
                                                 alt="{{ $child->full_name }}" 
                                                 class="child-avatar" 
                                                 style="margin-right: 15px;">
                                        @else
                                            <div class="child-avatar-initial bg-primary text-white" style="margin-right: 15px;">
                                                {{ strtoupper(substr($child->first_name, 0, 1)) }}{{ strtoupper(substr($child->last_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div style="flex: 1;">
                                            <h4 style="margin: 0;">{{ $child->full_name }}</h4>
                                            <small class="text-muted">
                                                Student ID: {{ $child->id }} | Age: {{ $child->age }} years
                                            </small>
                                        </div>
                                        <div>
                                            <span class="badge badge-success">Active</span>
                                        </div>
                                    </div>

                                    <!-- Quick Stats -->
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center;">
                                                <div style="font-size: 1.5rem; font-weight: bold; color: #007bff;">
                                                    {{ $child->enrolled_classes }}
                                                </div>
                                                <div style="font-size: 0.875rem; color: #6c757d;">
                                                    Enrolled Classes
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center;">
                                                <div style="font-size: 1.5rem; font-weight: bold; color: #28a745;">
                                                    {{ $child->attendance_rate }}%
                                                </div>
                                                <div style="font-size: 0.875rem; color: #6c757d;">
                                                    Attendance Rate
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Attendance Progress Bar -->
                                    <div class="mb-3">
                                        <div class="progress-stat">
                                            <span style="font-size: 0.875rem; color: #6c757d;">This Month's Attendance</span>
                                            <span style="font-weight: bold;">{{ $child->attendance_rate }}%</span>
                                        </div>
                                        <div class="progress-bar-custom">
                                            <div class="progress-fill" style="width: {{ $child->attendance_rate }}%;"></div>
                                        </div>
                                    </div>

                                    <!-- Additional Info -->
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Date of Birth:</small><br>
                                            <strong>{{ $child->date_of_birth->format('d M Y') }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Enrolled Since:</small><br>
                                            <strong>{{ $child->enrollment_date->format('d M Y') }}</strong>
                                        </div>
                                    </div>

                                    <!-- Pending Homework Alert -->
                                    @if($child->pending_homework > 0)
                                    <div class="alert alert-warning" style="margin-bottom: 15px; padding: 10px;">
                                        <i class="ti-alert"></i>
                                        <strong>{{ $child->pending_homework }}</strong> pending homework assignment(s)
                                    </div>
                                    @endif

                                    <!-- Action Buttons -->
                                    <div style="display: flex; gap: 10px;">
                                        <a href="{{ route('parent.students.show', $child) }}" class="btn btn-primary flex-fill">
                                            <i class="ti-eye"></i> View Details
                                        </a>
                                        <a href="{{ route('parent.attendance.index', ['child_id' => $child->id]) }}" class="btn btn-info flex-fill">
                                            <i class="ti-clipboard"></i> Attendance
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Summary Table (Optional) -->
                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="card alert">
                                    <div class="card-header">
                                        <h4>Children Summary</h4>
                                    </div>
                                    <div class="card-body">
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
                                <div class="card alert">
                                    <div class="card-body text-center py-5">
                                        <i class="ti-user" style="font-size: 5rem; color: #cbd5e0;"></i>
                                        <h3 class="mt-3 mb-2">No Children Found</h3>
                                        <p class="text-muted mb-4">
                                            You don't have any children registered in the system yet.
                                        </p>
                                        <p class="text-muted">
                                            Please contact the school administrator to register your children.
                                        </p>
                                    </div>
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
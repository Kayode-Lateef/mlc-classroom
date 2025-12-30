@extends('layouts.app')

@section('title', $user->name)

@push('styles')
    <style>

        .profile-widget{
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .user-profile-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .profile-header {
            display: flex;
            align-items: flex-start;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }

        .profile-photo-large {
            width: 150px;
            height: 150px;
            border-radius: 12px;
            object-fit: cover;
            border: 3px solid #e9ecef;
        }

        .profile-initial-large {
            width: 150px;
            height: 150px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 3rem;
            border: 3px solid #e9ecef;
        }

        .info-item {
            margin-bottom: 20px;
        }

        .info-value {
            font-size: 1rem;
            color: #212529;
            font-weight: 500;
        }

        .role-badge-large {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .verification-badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.8rem;
            margin-top: 5px;
            padding: 3px 8px;
            border-radius: 4px;
        }

        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0 5px;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .class-card, .child-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .class-card:hover, .child-card:hover {
            /* border-color: #007bff; */
            box-shadow: 0 3px 10px rgba(0,123,255,0.1);
        }

        .activity-item {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #007bff;
            margin-bottom: 10px;
        }

        .activity-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e7f3ff;
            color: #007bff;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .action-button-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
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
                                <h1>{{ $user->name }}</h1>
                            </div>
                        </div>
                        <span>{{ $user->email }}</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.users.index') }}">Users</a></li>
                                    <li class="active">{{ $user->name }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- /# row -->
                <div id="main-content">
                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="action-button-group" style="justify-content: flex-end;">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                                    <i class="ti-pencil-alt"></i> Edit User
                                </a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="ti-trash"></i> Delete User
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="user-profile">
                                        <div class="row">
                                            <div class="col-lg-12">

                                                <div style="display: flex; align-items: center;">
                                                    <div class="user-photo m-b-30" style="margin-right: 10px;">
                                                        @if($user->profile_photo)
                                                            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="profile-photo-large">
                                                        @else
                                                            <div class="profile-initial-large 
                                                                {{ $user->role === 'superadmin' ? 'bg-danger text-white' : 
                                                                ($user->role === 'admin' ? 'bg-success text-white' : 
                                                                ($user->role === 'teacher' ? 'bg-info text-white' : 'bg-warning text-white')) }}">
                                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="user-profile-name">{{ $user->name }}
                                                        @if($user->id === auth()->id())
                                                            <span class="badge badge-info ml-2">You</span>
                                                        @endif
                                                    </div>
                                                    <div class="info-value">
                                                        @if($user->role === 'superadmin')
                                                            <span class="role-badge-large badge-danger text-white">
                                                                <i class="ti-crown"></i> Super Admin
                                                            </span>
                                                        @elseif($user->role === 'admin')
                                                            <span class="role-badge-large badge-success text-white">
                                                                <i class="ti-id-badge"></i> Admin
                                                            </span>
                                                        @elseif($user->role === 'teacher')
                                                            <span class="role-badge-large badge-info text-white">
                                                                <i class="ti-briefcase"></i> Teacher
                                                            </span>
                                                        @elseif($user->role === 'parent')
                                                            <span class="role-badge-large badge-warning text-white">
                                                                <i class="ti-user"></i> Parent
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                
                                            </div>

                                            <div class="col-lg-12">
                                                <div class="custom-tab user-profile-tab">
                                                    <ul class="nav nav-tabs" role="tablist">
                                                        <li role="presentation" class="active"><a href="#1" aria-controls="1" role="tab" data-toggle="tab">Account Information</a></li>
                                                    </ul>
                                                    <div class="tab-content">
                                                        <div role="tabpanel" class="tab-pane active" id="1">
                                                            <div class="contact-information">
                                                                <div class="phone-content">
                                                                    <span class="contact-title">Phone Number:</span>
                                                                    <span class="phone-number">{{ $user->phone ?? 'Not provided' }}</span>
                                                                </div>
                                                                <div class="email-content">
                                                                    <span class="contact-title">Email Address:</span>
                                                                    <span class="contact-email">{{ $user->email }}
                                                                        @if($user->email_verified_at)
                                                                            <span class="verification-badge badge-success">
                                                                                <i class="ti-check"></i> Verified
                                                                            </span>
                                                                        @else
                                                                            <span class="verification-badge badge-warning">
                                                                                <i class="ti-alert"></i> Not Verified
                                                                            </span>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="basic-information">
                                                                <div class="birthday-content">
                                                                    <span class="contact-title">Account Created:</span>
                                                                    <span class="birth-date">
                                                                        {{ $user->created_at->format('d F Y') }}
                                                                        <small class="text-muted d-block">{{ $user->created_at->diffForHumans() }}</small>
                                                                    </span>
                                                                </div>
                                                                <div class="gender-content">
                                                                    <span class="contact-title">Last Updated:</span>
                                                                    <span class="gender">{{ $user->updated_at->format('d F Y') }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                     
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- /# column -->
                        <div class="col-lg-4">
                            <div class="card alert">
                                <div class="card-header">
                                   <h4><i class="ti-time"></i> Recent Activity</h4>
                                </div>
                                <div class="card-body">
                                    @if($recentActivity->count() > 0)
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        @foreach($recentActivity->take(10) as $activity)
                                        <div class="activity-item">
                                            <div style="display: flex; align-items: flex-start; gap: 15px;">
                                                <div class="activity-icon">
                                                    <i class="ti-info-alt"></i>
                                                </div>
                                                <div style="flex: 1;">
                                                    <p style="margin-bottom: 5px; color: #212529;">{{ $activity->description }}</p>
                                                    <small class="text-muted">
                                                        <i class="ti-time"></i> {{ $activity->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <div class="empty-state">
                                        <i class="ti-time"></i>
                                        <h4>No Recent Activity</h4>
                                        <p>No activity recorded for this user yet.</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- /# row -->
  
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Role-Specific Statistics -->
                            @if($user->role === 'teacher')
                            <!-- Teacher Statistics -->
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="profile-widget">
                                                <div class="stat-icon bg-primary text-white">
                                                    <i class="ti-briefcase"></i>
                                                </div>
                                                <div class="stat-text">{{ $userStats['students'] ?? 0 }}</div>
                                                <div class="stat-digit">Assigned Classes</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="profile-widget">
                                                <div class="stat-icon bg-success text-white">
                                                    <i class="ti-user"></i>
                                                </div>
                                                <div class="stat-text">Total Students</div>
                                                <div class="stat-digit">{{ $userStats['students'] ?? 0 }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="profile-widget">
                                                <div class="stat-icon bg-success text-white">
                                                    <i class="ti-book"></i>
                                                </div>
                                                <div class="stat-text">Homework Assigned</div>
                                                <div class="stat-digit">{{ $user->homeworkAssignments ? $user->homeworkAssignments->count() : 0 }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="profile-widget">
                                                <div class="stat-icon bg-info text-white">
                                                    <i class="ti-file"></i>
                                                </div>
                                                <div class="stat-text">Learning Resources</div>
                                                <div class="stat-digit">{{ $userStats['resources'] ?? 0 }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                    
                            <!-- Assigned Classes -->
                            <div class="row mt-4">
                                <div class="col-lg-12">
                                    <div class="card alert">
                                        <div class="card-header">
                                            <h4><i class="ti-briefcase"></i> Assigned Classes</h4>
                                        </div>
                                        <div class="card-body">
                                            @if($user->teachingClasses->count() > 0)
                                            <div class="row">
                                                @foreach($user->teachingClasses as $class)
                                                <div class="col-lg-4 col-md-6 mb-3">
                                                    <div class="class-card">
                                                        <h5 style="margin-bottom: 10px;">{{ $class->name }}</h5>
                                                        <p class="text-muted mb-2">
                                                            <i class="ti-book"></i> {{ $class->subject }}
                                                            @if($class->level)
                                                                <span class="badge badge-secondary ml-2">{{ $class->level }}</span>
                                                            @endif
                                                        </p>
                                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                                                            <span class="text-muted">
                                                                <i class="ti-user"></i> {{ $class->enrollments()->where('status', 'active')->count() }} students
                                                            </span>
                                                            <a href="{{ route('admin.classes.show', $class) }}" class="btn btn-sm btn-primary">
                                                                View <i class="ti-arrow-right"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @else
                                            <div class="empty-state">
                                                <i class="ti-briefcase"></i>
                                                <h4>No Classes Assigned</h4>
                                                <p>This teacher hasn't been assigned to any classes yet.</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>


                            @elseif($user->role === 'parent')
                            <!-- Parent Statistics -->
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="profile-widget">
                                                <div class="stat-icon bg-primary text-white">
                                                    <i class="ti-user"></i>
                                                </div>
                                                <div class="stat-text">Children</div>
                                                <div class="stat-digit">{{ $userStats['children'] ?? 0 }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="profile-widget">
                                                <div class="stat-icon bg-success text-white">
                                                    <i class="ti-briefcase"></i>
                                                </div>
                                                <div class="stat-text">Total Enrollments</div>
                                                <div class="stat-digit">{{ $user->children->sum(function($child) { return $child->classes->count(); }) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="profile-widget">
                                                <div class="stat-icon bg-info text-white">
                                                    <i class="ti-bell"></i>
                                                </div>
                                                <div class="stat-text">Unread Notifications</div>
                                                <div class="stat-digit">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Children List -->
                            <div class="row mt-4">
                                <div class="col-lg-12">
                                    <div class="card alert">
                                        <div class="card-header">
                                            <h4><i class="ti-user"></i> Children</h4>
                                        </div>
                                        <div class="card-body">
                                            @if($user->children->count() > 0)
                                            <div style="display: flex; flex-direction: column; gap: 15px;">
                                                @foreach($user->children as $child)
                                                <div class="child-card">
                                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                                        <div style="display: flex; align-items: center; gap: 15px;">
                                                            @if($child->profile_photo)
                                                                <img src="{{ asset('storage/' . $child->profile_photo) }}" alt="{{ $child->full_name }}" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                                                            @else
                                                                <div style="width: 60px; height: 60px; border-radius: 50%; background-color: #e7f3ff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.5rem; color: #007bff;">
                                                                    {{ strtoupper(substr($child->first_name, 0, 1)) }}
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h5 style="margin-bottom: 5px;">{{ $child->full_name }}</h5>
                                                                <p class="text-muted mb-0">
                                                                    <i class="ti-briefcase"></i> {{ $child->classes->count() }} classes enrolled
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div style="display: flex; align-items: center; gap: 15px;">
                                                            @if($child->status === 'active')
                                                                <span class="badge badge-success">Active</span>
                                                            @else
                                                                <span class="badge badge-secondary">{{ ucfirst($child->status) }}</span>
                                                            @endif
                                                            <a href="{{ route('admin.students.show', $child) }}" class="btn btn-sm btn-primary">
                                                                View <i class="ti-arrow-right"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @else
                                            <div class="empty-state">
                                                <i class="ti-user"></i>
                                                <h4>No Children Enrolled</h4>
                                                <p>This parent doesn't have any children enrolled yet.</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>



                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - User Details</p>
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
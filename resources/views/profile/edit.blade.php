@extends('layouts.app')

@section('content')
    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row">
                    <div class="col-lg-8 p-r-0 title-margin-right">
                        <div class="page-header">
                            <div class="page-title"><h1>My Profile</h1></div>
                        </div>
                        <span>Manage your account settings and preferences</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route(auth()->user()->role . '.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Profile</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="ti-check"></i> {{ session('success') }}
                        </div>
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="ti-alert"></i> {{ session('error') }}
                        </div>
                    </div>
                </div>
                @endif

                <!-- User Info Card -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                <h4 style="color: white; margin: 0;">
                                    <i class="ti-user"></i> {{ $user->name }}
                                    <span class="badge badge-light" style="margin-left: 10px;">{{ ucfirst($user->role) }}</span>
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 text-center">
                                        @if($user->profile_photo)
                                            <img src="{{ Storage::url($user->profile_photo) }}" 
                                                 alt="{{ $user->name }}" 
                                                 class="img-thumbnail rounded-circle"
                                                 style="width: 150px; height: 150px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto"
                                                 style="width: 150px; height: 150px; font-size: 3rem;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p style="font-size: 0.875rem; color: #6c757d; margin-bottom: 5px;">Email</p>
                                                <p style="font-size: 0.9375rem; font-weight: 600;">{{ $user->email }}</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p style="font-size: 0.875rem; color: #6c757d; margin-bottom: 5px;">Member Since</p>
                                                <p style="font-size: 0.9375rem; font-weight: 600;">{{ $userStats['member_since'] }}</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p style="font-size: 0.875rem; color: #6c757d; margin-bottom: 5px;">Last Login</p>
                                                <p style="font-size: 0.9375rem; font-weight: 600;">{{ $userStats['last_login'] }}</p>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            @if(isset($userStats['classes']))
                                            <div class="col-md-4">
                                                <p style="font-size: 0.875rem; color: #6c757d; margin-bottom: 5px;">Classes Teaching</p>
                                                <p style="font-size: 0.9375rem; font-weight: 600;">{{ $userStats['classes'] }}</p>
                                            </div>
                                            @endif
                                            @if(isset($userStats['children']))
                                            <div class="col-md-4">
                                                <p style="font-size: 0.875rem; color: #6c757d; margin-bottom: 5px;">Children</p>
                                                <p style="font-size: 0.9375rem; font-weight: 600;">{{ $userStats['children'] }}</p>
                                            </div>
                                            @endif
                                            <div class="col-md-4">
                                                <p style="font-size: 0.875rem; color: #6c757d; margin-bottom: 5px;">Total Actions</p>
                                                <p style="font-size: 0.9375rem; font-weight: 600;">{{ number_format($userStats['total_actions']) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-body">
                                <!-- Nav Tabs -->
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#profile-info" style="font-size: 0.9375rem;">
                                            <i class="ti-user"></i> Profile Information
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#change-password" style="font-size: 0.9375rem;">
                                            <i class="ti-lock"></i> Change Password
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#activity-log" style="font-size: 0.9375rem;">
                                            <i class="ti-time"></i> Activity Log
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#security" style="font-size: 0.9375rem;">
                                            <i class="ti-shield"></i> Security
                                        </a>
                                    </li>
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content mt-4">
                                    <!-- Profile Info Tab -->
                                    <div id="profile-info" class="tab-pane fade show active">
                                        @include('profile.partials.profile-info')
                                    </div>

                                    <!-- Change Password Tab -->
                                    <div id="change-password" class="tab-pane fade">
                                        @include('profile.partials.change-password')
                                    </div>

                                    <!-- Activity Log Tab -->
                                    <div id="activity-log" class="tab-pane fade">
                                        @include('profile.partials.activity-log')
                                    </div>

                                    <!-- Security Tab -->
                                    <div id="security" class="tab-pane fade">
                                        @include('profile.partials.delete-account')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer">
                            <p>MLC Classroom - My Profile</p>
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
    // Check if there are validation errors and switch to appropriate tab
    @if($errors->updatePassword->any())
        $('.nav-tabs a[href="#change-password"]').tab('show');
    @endif
    
    @if($errors->userDeletion->any())
        $('.nav-tabs a[href="#security"]').tab('show');
    @endif
});
</script>
@endpush
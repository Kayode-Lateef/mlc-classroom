@extends('layouts.app')

@section('title', 'Progress Sheet Details')

@push('styles')
    <style>
        .sheet-header {
            padding: 30px;
            border-radius: 8px 8px 0 0;
        }

        .detail-item {
            margin-bottom: 20px;
        }

        .detail-label {
            color: #6c757d;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .detail-value {
            color: #212529;
        }

        .performance-summary-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
        }

        .performance-card {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            transition: transform 0.2s;
        }

    

        .performance-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .student-note-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff;
            transition: background-color 0.2s;
        }

        .student-note-item:hover {
            background-color: #f8f9fa;
        }

        .student-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }

        .student-avatar-initial {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
            background-color: #007bff;
            color: white;
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

        .info-card {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
        }

        .info-card h4 {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .performance-summary-grid {
                grid-template-columns: repeat(2, 1fr);
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
                                <h1>Progress Sheet Details</h1>
                            </div>
                        </div>
                        <span>{{ $progressSheet->topic }}</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.progress-sheets.index') }}">Progress Sheets</a></li>
                                    <li class="active">Details</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <!-- Main Content -->

                    <div class="col-lg-8">
                        <!-- Progress Sheet Details -->
                        <div class="card alert">
                            <div class="card-header">
                                <h2>{{ $progressSheet->topic }}</h2>
                                <p>{{ $progressSheet->class->name }} • {{ $progressSheet->class->subject }}</p>
                            </div>

                            <div class="card-body">
                                @if($progressSheet->objective)
                                <div style="margin-bottom: 20px;">
                                    <h4 style="font-weight: 600; margin-bottom: 10px;">
                                        <i class="ti-target"></i> Lesson Objective
                                    </h4>
                                    <p style="color: #6c757d; line-height: 1.6;">{{ $progressSheet->objective }}</p>
                                </div>
                                @endif

                                @if($progressSheet->notes)
                                <div>
                                    <h4 style="font-weight: 600; margin-bottom: 10px;">
                                        <i class="ti-pencil-alt"></i> General Class Notes
                                    </h4>
                                    <div style="background-color: #f8f9fa; border-radius: 8px; padding: 15px;">
                                        <p style="color: #6c757d; margin: 0; line-height: 1.6;">{{ $progressSheet->notes }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Performance Summary -->
                        @if($stats['total_students'] > 0)
                        <div class="card alert">
                            <div class="card-header mb-3">
                                <h4><i class="ti-stats-up"></i> Performance Summary</h4>
                            </div>

                             <div class="performance-summary-grid">
                                <div class="">
                                    <div class="card p-0">
                                        <div class="stat-widget-three">
                                            <div class="performance-card bg-success">
                                                <div class="stat-digit">{{ $stats['excellent'] }}</div>
                                                <div class="stat-text">Excellent</div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="">
                                    <div class="card p-0">
                                        <div class="stat-widget-three">
                                            <div class="performance-card bg-primary">
                                                <div class="stat-digit">{{ $stats['good'] }}</div>
                                                <div class="stat-text">Good</div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="">
                                    <div class="card p-0">
                                        <div class="stat-widget-three">
                                            <div class="performance-card bg-warning">
                                                <div class="stat-digit">{{ $stats['average'] }}</div>
                                                <div class="stat-text">Average</div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="">
                                    <div class="card p-0">
                                        <div class="stat-widget-three">
                                            <div class="performance-card bg-danger">
                                                <div class="stat-digit">{{ $stats['struggling'] }}</div>
                                                <div class="stat-text">Struggling</div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="">
                                    <div class="card p-0">
                                        <div class="stat-widget-three">
                                            <div class="performance-card bg-pink">
                                                <div class="stat-digit">{{ $stats['absent'] }}</div>
                                                <div class="stat-text">Absent</div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Student Progress Notes -->
                        <div class="card alert">
                            <div class="card-header mb-3">
                                <h4><i class="ti-user"></i> Student Progress Notes</h4>
                            </div>
                            <div class="card-body">
                                @if($progressSheet->progressNotes->count() > 0)
                                    @foreach($progressSheet->progressNotes as $note)
                                    <div class="student-note-item">
                                        <div style="display: flex; gap: 15px;">
                                            <!-- Student Avatar -->
                                            <div style="flex-shrink: 0;">
                                                @if($note->student->profile_photo)
                                                <img src="{{ Storage::url($note->student->profile_photo) }}" alt="{{ $note->student->full_name }}" class="student-avatar">
                                                @else
                                                <div class="student-avatar-initial">
                                                    {{ strtoupper(substr($note->student->first_name, 0, 1)) }}{{ strtoupper(substr($note->student->last_name, 0, 1)) }}
                                                </div>
                                                @endif
                                            </div>

                                            <!-- Student Info -->
                                            <div style="flex: 1;">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                                    <h5 style="margin: 0;">{{ $note->student->full_name }}</h5>
                                                    @if($note->performance)
                                                    @switch($note->performance)
                                                        @case('excellent')
                                                            <span class="badge badge-success">✨ Excellent</span>
                                                            @break
                                                        @case('good')
                                                            <span class="badge badge-info">✓ Good</span>
                                                            @break
                                                        @case('average')
                                                            <span class="badge badge-warning">~ Average</span>
                                                            @break
                                                        @case('struggling')
                                                            <span class="badge badge-danger">⚠ Struggling</span>
                                                            @break
                                                        @case('absent')
                                                            <span class="badge badge-secondary">✗ Absent</span>
                                                            @break
                                                    @endswitch
                                                    @endif
                                                </div>

                                                @if($note->notes)
                                                <p style="color: #6c757d; margin: 0;">{{ $note->notes }}</p>
                                                @else
                                                <p style="color: #adb5bd; font-style: italic; margin: 0;">No additional notes</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="empty-state">
                                        <i class="ti-user"></i>
                                        <p>No student notes recorded for this session</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Quick Actions -->
                        <div class="card alert">
                            <div class="card-header mb-3">
                                <h4><i class="ti-settings"></i> Actions</h4>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('admin.progress-sheets.edit', $progressSheet) }}" class="btn btn-primary btn-block mb-2">
                                    <i class="ti-pencil-alt"></i> Edit Progress Sheet
                                </a>

                                <form action="{{ route('admin.progress-sheets.destroy', $progressSheet) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this progress sheet? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="ti-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Details Card -->
                        <div class="info-card mt-3">
                            <h4><i class="ti-info-alt"></i> Details</h4>
                            
                            <div class="detail-item">
                                <div class="detail-label">Date</div>
                                <div class="detail-value">{{ \Carbon\Carbon::parse($progressSheet->date)->format('l, d F Y') }}</div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">Class</div>
                                <div class="detail-value">{{ $progressSheet->class->name }}</div>
                                <small class="text-muted">{{ $progressSheet->class->subject }}</small>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">Teacher</div>
                                <div style="display: flex; align-items: center;">
                                    @if($progressSheet->teacher->profile_photo)
                                        <img src="{{ asset('storage/' . $progressSheet->teacher->profile_photo) }}" alt="{{ $progressSheet->teacher->name }}" class="user-avatar" style="margin-right: 12px;">
                                    @else
                                    <div class="student-avatar-initial" style="width: 32px; height: 32px; font-size: 0.75rem; margin-right: 10px;">
                                        {{ strtoupper(substr($progressSheet->teacher->name, 0, 1)) }}
                                    </div>
                                    @endif
                                    <div>
                                        <div class="detail-value">{{ $progressSheet->teacher->name }}</div>
                                        <small class="text-muted">{{ ucfirst($progressSheet->teacher->role) }}</small>
                                    </div>
                                </div>
                            </div>

                            @if($progressSheet->schedule)
                            <div class="detail-item">
                                <div class="detail-label">Schedule</div>
                                <div class="detail-value">{{ $progressSheet->schedule->day_of_week }}</div>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($progressSheet->schedule->start_time)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($progressSheet->schedule->end_time)->format('H:i') }}
                                </small>
                            </div>
                            @endif

                            <div class="detail-item">
                                <div class="detail-label">Students Assessed</div>
                                <div class="detail-value">{{ $stats['total_students'] }}</div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">Created</div>
                                <div class="detail-value">{{ $progressSheet->created_at->format('d M Y, H:i') }}</div>
                            </div>

                            @if($progressSheet->updated_at != $progressSheet->created_at)
                            <div class="detail-item" style="margin-bottom: 0;">
                                <div class="detail-label">Last Updated</div>
                                <div class="detail-value">{{ $progressSheet->updated_at->diffForHumans() }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer">
                            <p>MLC Classroom - Progress Sheet Details</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
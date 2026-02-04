@extends('layouts.app')

@section('title', 'Homework Details')

@push('styles')
<style>
    .homework-header {
        padding: 25px;
        border-radius: 8px 8px 0 0;
    }

    .stat-box {
        text-align: center;
        padding: 20px;
        border-radius: 8px;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 1rem;
        opacity: 0.9;
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

    .submission-box {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .submission-box.pending {
        border-color: #ffc107;
        background: #fff3cd;
    }

    .submission-box.submitted {
        border-color: #17a2b8;
        background: #d1ecf1;
    }

    .submission-box.graded {
        border-color: #28a745;
        background: #d4edda;
    }

    .submission-box.late {
        border-color: #dc3545;
        background: #f8d7da;
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
                                <h1>Homework Details</h1>
                            </div>
                        </div>
                        <span>View homework assignment details and submission status</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('parent.homework.index') }}">Homework</a></li>
                                    <li class="active">Details</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Child Selector (if parent has multiple children with this homework) -->
                    @if($children->count() > 1)
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="child-selector">
                                <h5 class="mb-3">View submission for:</h5>
                                <div class="row">
                                    @foreach($children as $child)
                                    @php
                                        $childSubmission = \App\Models\HomeworkSubmission::where('homework_assignment_id', $homework->id)
                                            ->where('student_id', $child->id)
                                            ->first();
                                    @endphp
                                    @if($childSubmission)
                                    <div class="col-md-3">
                                        <a href="{{ route('parent.homework.show', ['homework' => $homework->id, 'child_id' => $child->id]) }}" 
                                           class="child-option {{ $selectedChild->id == $child->id ? 'active' : '' }}"
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
                                                    <br>
                                                    <small class="badge badge-{{ $childSubmission->status === 'graded' ? 'success' : ($childSubmission->status === 'pending' ? 'warning' : 'info') }}">
                                                        {{ ucfirst($childSubmission->status) }}
                                                    </small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <!-- Main Content -->
                        <div class="col-lg-8">
                            <!-- Homework Details -->
                            <div class="card alert">
                                @php
                                    if ($isOverdue) {
                                        $statusClass = 'badge-danger';
                                        $statusText = 'Overdue';
                                    } elseif ($daysUntilDue == 0) {
                                        $statusClass = 'badge-warning';
                                        $statusText = 'Due Today';
                                    } else {
                                        $statusClass = 'badge-success';
                                        $statusText = 'Upcoming';
                                    }
                                @endphp

                                <div class="homework-header card-header">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div style="flex: 1;">
                                            <h2 style="font-weight: 700; margin-bottom: 8px;">
                                                {{ $homework->title }}
                                            </h2>
                                            <p style="margin: 0;">
                                                {{ $homework->class->name }} • {{ $homework->class->subject }}
                                            </p>
                                        </div>
                                        <span class="badge {{ $statusClass }}" style="font-size: 1rem; padding: 6px 12px;">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                </div>

                                <div class="card-body mt-3">
                                    @if($homework->description)
                                    <div style="margin-bottom: 20px;">
                                        <h4 style="font-weight: 600; margin-bottom: 10px;">
                                            <i class="ti-align-left"></i> Description
                                        </h4>
                                        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px;">
                                            <p style="margin: 0; color: #495057; white-space: pre-line;">{{ $homework->description }}</p>
                                        </div>
                                    </div>
                                    @endif

                                    @if($homework->file_path)
                                    <div style="margin-bottom: 20px;">
                                        <h4 style="font-weight: 600; margin-bottom: 10px;">
                                            <i class="ti-clip"></i> Attachment
                                        </h4>
                                        <a href="{{ route('parent.homework.download', $homework) }}" class="btn btn-primary btn-sm">
                                            <i class="ti-download"></i> Download Attachment
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Submission Status for Selected Child -->
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <h4>
                                        <i class="ti-user"></i> 
                                        Submission for {{ $selectedChild->full_name }}
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="submission-box {{ $submission->status }}">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Status:</strong></p>
                                                @switch($submission->status)
                                                    @case('pending')
                                                        <span class="badge badge-warning" style="font-size: 1rem; padding: 6px 12px;">
                                                            <i class="ti-time"></i> Pending Submission
                                                        </span>
                                                        @break
                                                    @case('submitted')
                                                        <span class="badge badge-info" style="font-size: 1rem; padding: 6px 12px;">
                                                            <i class="ti-check"></i> Submitted
                                                        </span>
                                                        @break
                                                    @case('late')
                                                        <span class="badge badge-danger" style="font-size: 1rem; padding: 6px 12px;">
                                                            <i class="ti-alert"></i> Late Submission
                                                        </span>
                                                        @break
                                                    @case('graded')
                                                        <span class="badge badge-success" style="font-size: 1rem; padding: 6px 12px;">
                                                            <i class="ti-star"></i> Graded
                                                        </span>
                                                        @break
                                                @endswitch
                                            </div>

                                            @if($submission->submitted_date)
                                            <div class="col-md-6">
                                                <p><strong>Submitted:</strong></p>
                                                <p>{{ $submission->submitted_date->format('d M Y, H:i') }}</p>
                                            </div>
                                            @endif
                                        </div>

                                        @if($submission->status === 'graded')
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <p><strong>Grade:</strong></p>
                                                <h3 class="text-success">{{ $submission->grade }}</h3>
                                            </div>
                                            @if($submission->teacher_comments)
                                            <div class="col-md-12 mt-3">
                                                <p><strong>Teacher Comments:</strong></p>
                                                <div style="background: white; padding: 15px; border-radius: 6px;">
                                                    {{ $submission->teacher_comments }}
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        @endif

                                        @if($submission->file_path)
                                        <div class="mt-3">
                                            <p><strong>Submitted File:</strong></p>
                                            <a href="{{ asset('storage/' . $submission->file_path) }}" 
                                               target="_blank" 
                                               class="btn btn-info">
                                                <i class="ti-download"></i> Download Submission
                                            </a>
                                        </div>
                                        @endif

                                        <!-- Submit Button (if pending) -->
                                        {{-- @if($submission->status === 'pending')
                                        <div class="mt-4">
                                            <h5>Submit Homework</h5>
                                            <form action="{{ route('parent.homework.submit', $homework) }}" 
                                                  method="POST" 
                                                  enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="child_id" value="{{ $selectedChild->id }}">
                                                
                                                <div class="form-group">
                                                    <label class="required-field">Upload File</label>
                                                    <input type="file" 
                                                           name="file" 
                                                           required 
                                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                                           class="form-control">
                                                    <small class="text-muted">
                                                        Accepted formats: PDF, DOC, DOCX, JPG, PNG (Max: 10MB)
                                                    </small>
                                                </div>

                                                @if($isOverdue)
                                                <div class="alert alert-warning">
                                                    <i class="ti-alert"></i>
                                                    <strong>Note:</strong> This homework is overdue. Your submission will be marked as late.
                                                </div>
                                                @endif

                                                <button type="submit" class="btn btn-success">
                                                    <i class="ti-upload"></i> Submit Homework
                                                </button>
                                            </form>
                                        </div>
                                        @endif --}}
                                    @if($submission->status === 'pending')
                                        <div class="alert alert-info">
                                            <i class="ti-info-alt"></i> 
                                            <strong>Physical Submission Required:</strong> 
                                            This homework must be submitted physically in class. 
                                            Your child's teacher will mark it as submitted once received.
                                        </div>
                                    @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <!-- Details Card -->
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-info-alt"></i> Details</h4>
                                </div>
                                <div class="card-body">
                                    <div style="margin-bottom: 15px;">
                                        <p style="margin: 0 0 5px 0; color: #6c757d; font-weight: 500;">Class</p>
                                        <p style="margin: 0; font-weight: 600; color: #212529;">{{ $homework->class->name }}</p>
                                        <p style="margin: 0; color: #6c757d;">{{ $homework->class->subject }}</p>
                                    </div>

                                    <div style="margin-bottom: 15px;">
                                        <p style="margin: 0 0 5px 0; color: #6c757d; font-weight: 500;">Teacher</p>
                                        <p style="margin: 0; font-weight: 600; color: #212529;">{{ $homework->teacher->name }}</p>
                                    </div>

                                    <div style="margin-bottom: 15px;">
                                        <p style="margin: 0 0 5px 0; color: #6c757d; font-weight: 500;">Assigned Date</p>
                                        <p style="margin: 0; color: #212529;">{{ $homework->assigned_date->format('d M Y') }}</p>
                                    </div>

                                    <div style="margin-bottom: 15px;">
                                        <p style="margin: 0 0 5px 0; color: #6c757d; font-weight: 500;">Due Date</p>
                                        <p style="margin: 0; font-weight: 600; color: {{ $isOverdue ? '#dc3545' : '#212529' }};">
                                            {{ $homework->due_date->format('d M Y, H:i') }}
                                        </p>
                                        <p style="margin: 0; color: #6c757d;">
                                            @if($isOverdue)
                                                {{ abs($daysUntilDue) }} days overdue
                                            @elseif($daysUntilDue == 0)
                                                Due today
                                            @else
                                                {{ $daysUntilDue }} days remaining
                                            @endif
                                        </p>
                                    </div>

                                    <div style="margin-bottom: 15px;">
                                        <p style="margin: 0 0 5px 0; color: #6c757d; font-weight: 500;">Student</p>
                                        <p style="margin: 0; font-weight: 600; color: #212529;">{{ $selectedChild->full_name }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="card alert" style="margin-top: 20px;">
                                <div class="card-header">
                                    <h4><i class="ti-menu"></i> Quick Actions</h4>
                                </div>
                                <div class="card-body">
                                    <a href="{{ route('parent.homework.index', ['child_id' => $selectedChild->id]) }}" 
                                       class="btn btn-primary btn-block mb-2">
                                        <i class="ti-back-left"></i> Back to Homework List
                                    </a>

                                    @if($homework->file_path)
                                    <a href="{{ route('parent.homework.download', $homework) }}" 
                                       class="btn btn-success btn-block mb-2">
                                        <i class="ti-download"></i> Download Assignment
                                    </a>
                                    @endif

                                    @if($submission->file_path)
                                    <a href="{{ asset('storage/' . $submission->file_path) }}" 
                                       target="_blank"
                                       class="btn btn-info btn-block mb-2">
                                        <i class="ti-download"></i> Download Submission
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Homework Details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
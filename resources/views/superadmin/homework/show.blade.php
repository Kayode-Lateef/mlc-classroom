@extends('layouts.app')

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

    .submission-item {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .submission-item:hover {
        background-color: #f8f9fa;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .student-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
    }

    .student-avatar-initial {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: #007bff;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1rem;
    }

    .tab-content-wrapper {
        margin-top: 20px;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }

    .modal.show {
        display: block;
    }

    .modal-dialog {
        position: relative;
        margin: 5% auto;
        max-width: 500px;
    }

    .modal-content {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }


    .progress-custom {
        height: 12px;
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
                                <h1>Homework Details</h1>
                            </div>
                        </div>
                        <span>View homework assignment details and student submissions</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('superadmin.homework.index') }}">Homework</a></li>
                                    <li class="active">Details</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
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

                    <div class="row">
                        <!-- Main Content -->
                        <div class="col-lg-8">
                            <!-- Homework Details -->
                            <div class="card alert">
                                @php
                                    $today = now()->format('Y-m-d');
                                    $dueDate = $homework->due_date->format('Y-m-d');
                                    if ($dueDate < $today) {
                                        $statusClass = 'badge-danger';
                                        $statusText = 'Overdue';
                                    } elseif ($dueDate == $today) {
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
                                        <a href="{{ route('superadmin.homework.download', $homework) }}" class="btn btn-primary btn-sm">
                                            <i class="ti-download"></i> Download Attachment
                                        </a>
                                    </div>
                                    @endif

                                    @if($homework->progressSheet)
                                    <div>
                                        <h4 style="font-weight: 600; margin-bottom: 10px;">
                                            <i class="ti-clipboard"></i> Linked Progress Sheet
                                        </h4>
                                        <div style="background-color: #e7f3ff; padding: 12px; border-radius: 6px;">
                                            <p style="margin: 0; font-weight: 600; color: #495057;">{{ $homework->progressSheet->topic }}</p>
                                            <p style="margin: 5px 0 0 0; color: #6c757d;">{{ $homework->progressSheet->date->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Submission Statistics -->
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-stats-up"></i> Submission Statistics</h4>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="card p-0">
                                            <div class="stat-widget-three">
                                                <div class="stat-box bg-primary">
                                                    <div class="stat-digit">{{ $stats['total_students'] }}</div>
                                                    <div class="stat-text">Total Students</div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="card p-0">
                                            <div class="stat-widget-three">
                                                <div class="stat-box bg-success">
                                                    <div class="stat-digit">{{ $stats['submitted'] }}</div>
                                                    <div class="stat-text">Submitted</div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="card p-0">
                                            <div class="stat-widget-three">
                                                <div class="stat-box bg-pink">
                                                    <div class="stat-digit">{{ $stats['graded'] }}</div>
                                                    <div class="stat-text">Graded</div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="card p-0">
                                            <div class="stat-widget-three">
                                                <div class="stat-box bg-warning">
                                                    <div class="stat-digit">{{ $stats['pending'] }}</div>
                                                    <div class="stat-text">Pending</div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>                                   
                                </div>

                                <div class="card-body">
                                    @php
                                        $submissionRate = $stats['total_students'] > 0 ? round(($stats['submitted'] / $stats['total_students']) * 100) : 0;
                                        $gradingRate = $stats['total_students'] > 0 ? round(($stats['graded'] / $stats['total_students']) * 100) : 0;
                                    @endphp

                                    <div style="margin-top: 25px;">
                                        <div style="margin-bottom: 20px;">
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                                <span style="font-weight: 500;">Submission Progress</span>
                                                <span style="font-weight: 600;">{{ $submissionRate }}%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-primary w-{{ $submissionRate }}" role="progressbar" aria-valuenow="{{ $submissionRate }}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                                <span style="font-weight: 500;">Grading Progress</span>
                                                <span style="font-weight: 600;">{{ $gradingRate }}%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-warning w-{{ $gradingRate }}" role="progressbar" aria-valuenow="{{ $gradingRate }}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                               

                            <!-- Student Submissions -->
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <h4><i class="ti-user"></i> Student Submissions ({{ $homework->submissions->count() }})</h4>
                                </div>
                                <div class="card-body">
                                    @foreach($homework->submissions as $submission)
                                    <div class="submission-item">
                                        <div style="display: flex; gap: 15px;">
                                            <!-- Student Avatar -->
                                            <div style="flex-shrink: 0;">
                                                @if($submission->student->profile_photo)
                                                <img src="{{ asset('storage/' . $submission->student->profile_photo) }}" alt="{{ $submission->student->full_name }}" class="student-avatar">
                                                @else
                                                <div class="student-avatar-initial">
                                                    {{ strtoupper(substr($submission->student->first_name, 0, 1)) }}{{ strtoupper(substr($submission->student->last_name, 0, 1)) }}
                                                </div>
                                                @endif
                                            </div>

                                            <!-- Student Info -->
                                            <div style="flex: 1;">
                                                <h5 style="margin: 0 0 5px 0; font-weight: 600;">{{ $submission->student->full_name }}</h5>
                                                <p style="margin: 0 0 8px 0; color: #6c757d;">
                                                    Parent: {{ $submission->student->parent->name }}
                                                </p>

                                                @if($submission->submitted_date)
                                                <p style="margin: 0 0 8px 0; color: #495057;">
                                                    <i class="ti-calendar"></i> Submitted: {{ \Carbon\Carbon::parse($submission->submitted_date)->format('d M Y, H:i') }}
                                                </p>
                                                @endif

                                                @if($submission->status === 'graded' && $submission->grade)
                                                <div style="margin-top: 10px;">
                                                    <span class="badge badge-success" style="padding: 4px 10px;">
                                                        Grade: {{ $submission->grade }}
                                                    </span>
                                                </div>
                                                @endif

                                                @if($submission->teacher_comments)
                                                <div style="margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 4px;">
                                                    <strong>Teacher Comments:</strong>
                                                    <p style="margin: 5px 0 0 0; color: #495057;">{{ $submission->teacher_comments }}</p>
                                                </div>
                                                @endif
                                            </div>

                                            <!-- Status & Actions -->
                                            <div style="flex-shrink: 0; text-align: right;">
                                                @switch($submission->status)
                                                    @case('pending')
                                                        <span class="badge badge-secondary">Pending</span>
                                                        @break
                                                    @case('submitted')
                                                        <span class="badge badge-primary">Submitted</span>
                                                        @break
                                                    @case('late')
                                                        <span class="badge badge-warning">Late</span>
                                                        @break
                                                    @case('graded')
                                                        <span class="badge badge-success">Graded</span>
                                                        @break
                                                @endswitch

                                                @if($submission->file_path)
                                                <div style="margin-top: 8px;">
                                                    <a href="{{ asset('storage/' . $submission->file_path) }}" target="_blank" class="btn btn-sm btn-info" style="font-size: 0.75rem;">
                                                        <i class="ti-download"></i> Download
                                                    </a>
                                                </div>
                                                @endif

                                                @if(in_array($submission->status, ['submitted', 'late']))
                                                <div style="margin-top: 8px;">
                                                    <button 
                                                        onclick="openGradeModal({{ $submission->id }}, '{{ $submission->student->full_name }}')"
                                                        class="btn btn-sm btn-success"
                                                        style="font-size: 1rem;"
                                                    >
                                                        <i class="ti-pencil"></i> Grade
                                                    </button>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
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
                                    <a href="{{ route('superadmin.homework.edit', $homework) }}" class="btn btn-primary btn-block mb-2">
                                        <i class="ti-pencil-alt"></i> Edit Homework
                                    </a>

                                    @if($homework->file_path)
                                    <a href="{{ route('superadmin.homework.download', $homework) }}" class="btn btn-success btn-block mb-2">
                                        <i class="ti-download"></i> Download File
                                    </a>
                                    @endif

                                    <form action="{{ route('superadmin.homework.destroy', $homework) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this homework? All {{ $homework->submissions->count() }} submissions will be deleted.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-block">
                                            <i class="ti-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Details Card -->
                            <div class="card alert" style="margin-top: 20px;">
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
                                        <p style="margin: 0; font-weight: 600; color: #212529;">{{ $homework->due_date->format('d M Y') }}</p>
                                        <p style="margin: 0; color: #6c757d;">{{ $homework->due_date->diffForHumans() }}</p>
                                    </div>

                                    <div style="margin-bottom: 15px;">
                                        <p style="margin: 0 0 5px 0; color: #6c757d; font-weight: 500;">Created</p>
                                        <p style="margin: 0; color: #212529;">{{ $homework->created_at->format('d M Y, H:i') }}</p>
                                    </div>

                                    @if($homework->updated_at != $homework->created_at)
                                    <div>
                                        <p style="margin: 0 0 5px 0; color: #6c757d; font-weight: 500;">Last Updated</p>
                                        <p style="margin: 0; color: #212529;">{{ $homework->updated_at->diffForHumans() }}</p>
                                    </div>
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

    <!-- Grade Modal -->
    <div id="gradeModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="card alert" style="margin: 0;">
                    <div class="card-header">
                        <h4 style="margin: 0;"><i class="ti-pencil"></i> Grade Homework</h4>
                    </div>
                    <div class="card-body">
                        <p style="margin-bottom: 15px; font-size: 0.9375rem;">
                            Student: <strong id="modalStudentName"></strong>
                        </p>
                        
                        <form id="gradeForm" method="POST">
                            @csrf
                            <div class="form-group">
                                <label class="required-field">Grade</label>
                                <input 
                                    type="text" 
                                    name="grade" 
                                    required
                                    maxlength="50"
                                    placeholder="e.g., A+, 95%, Excellent"
                                    class="form-control"
                                >
                            </div>

                            <div class="form-group">
                                <label>Teacher Comments</label>
                                <textarea 
                                    name="teacher_comments" 
                                    rows="3"
                                    maxlength="1000"
                                    placeholder="Optional feedback for the student..."
                                    class="form-control"
                                ></textarea>
                            </div>

                            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                <button type="button" onclick="closeGradeModal()" class="btn btn-secondary">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="ti-check"></i> Submit Grade
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function openGradeModal(submissionId, studentName) {
    document.getElementById('modalStudentName').textContent = studentName;
    document.getElementById('gradeForm').action = '/superadmin/homework/submissions/' + submissionId + '/grade';
    document.getElementById('gradeModal').classList.add('show');
}

function closeGradeModal() {
    document.getElementById('gradeModal').classList.remove('show');
    document.getElementById('gradeForm').reset();
}

// Close modal on outside click
document.getElementById('gradeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeGradeModal();
    }
});
</script>
@endpush
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

    /* ========================================
       MODAL STYLES - PERFECTLY CENTERED
       ======================================== */
    .modal {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 9999;
        display: none;
        overflow-x: hidden;
        overflow-y: auto;
        outline: 0;
        /* Flexbox for perfect centering */
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex !important; /* Use flex to enable centering */
    }

    .modal-backdrop {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 9998;
        background-color: #000;
        opacity: 0.5;
    }

    .modal-dialog {
        position: relative;
        width: 90%;
        max-width: 500px;
        margin: 0; /* No margin needed with flexbox */
        z-index: 9999;
    }

    .modal-content {
        position: relative;
        background-color: #fff;
        border: 1px solid rgba(0,0,0,.2);
        border-radius: 6px;
        box-shadow: 0 5px 15px rgba(0,0,0,.5);
        background-clip: padding-box;
        outline: 0;
        animation: modalZoomIn 0.3s ease-out;
    }

    /* Zoom in animation for centered modal */
    @keyframes modalZoomIn {
        from {
            opacity: 0;
            transform: scale(0.7);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Close button styling */
    .modal-close {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        border: none;
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        color: #000;
        opacity: 0.4;
        cursor: pointer;
        z-index: 10;
        padding: 0;
        width: 30px;
        height: 30px;
        transition: opacity 0.2s;
    }

    .modal-close:hover,
    .modal-close:focus {
        opacity: 0.8;
        outline: none;
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
                                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('admin.homework.index') }}">Homework</a></li>
                                    <li class="active">Details</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
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
                                        <a href="{{ route('admin.homework.download', $homework) }}" class="btn btn-primary btn-sm">
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
                                                    Parent: {{ $submission->student->parent->name ?? 'No parent assigned' }}
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
                                                        onclick="openGradeModal({{ $submission->id }}, '{{ addslashes($submission->student->full_name) }}')"
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
                                    <a href="{{ route('admin.homework.edit', $homework) }}" class="btn btn-primary btn-block mb-2">
                                        <i class="ti-pencil-alt"></i> Edit Homework
                                    </a>

                                    @if($homework->file_path)
                                    <a href="{{ route('admin.homework.download', $homework) }}" class="btn btn-success btn-block mb-2">
                                        <i class="ti-download"></i> Download File
                                    </a>
                                    @endif

                                    <form action="{{ route('admin.homework.destroy', $homework) }}" method="POST" id="deleteHomeworkForm">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-block" onclick="confirmDeleteHomework({{ $homework->submissions->count() }})">
                                            <i class="ti-trash"></i> Delete Homework
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

    <!-- Grade Modal - BOOTSTRAP 3 STRUCTURE WITH PERFECT CENTERING -->
    <div id="gradeModal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button type="button" class="modal-close" onclick="closeGradeModal()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                
                <div class="card alert" style="margin: 0; border: none; border-radius: 6px;">
                    <div class="card-header">
                        <h4 style="margin: 0;"><i class="ti-pencil"></i> Grade Homework</h4>
                    </div>
                    <div class="card-body">
                        <p style="margin-bottom: 15px; font-size: 0.9375rem;">
                            Student: <strong id="modalStudentName"></strong>
                        </p>
                        
                        <form id="gradeForm" method="POST">
                            @csrf
                            <input type="hidden" name="submission_id" id="modalSubmissionId">
                            
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
                                <small class="form-text text-muted">
                                    <span id="commentCount">0</span>/1000 characters
                                </small>
                            </div>

                            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                                <button type="button" onclick="closeGradeModal()" class="btn btn-secondary">
                                    <i class="ti-close"></i> Cancel
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
    <div id="gradeModalBackdrop" class="modal-backdrop" style="display: none;"></div>
@endsection

@push('scripts')
<script>
    // ==========================================
    // ✅ DELETE HOMEWORK CONFIRMATION
    // ==========================================
    function confirmDeleteHomework(submissionCount) {
        let warningMessage = '';
        let confirmButtonText = 'Yes, delete it!';
        
        if (submissionCount > 0) {
            warningMessage = `This homework has ${submissionCount} submission(s) from students.\n\n` +
                           `⚠️ All submissions will be permanently deleted!\n\n` +
                           `This action cannot be undone!`;
            confirmButtonText = `Delete (${submissionCount} submissions)`;
        } else {
            warningMessage = 'This homework has no submissions yet.\n\n' +
                           'Are you sure you want to delete it?\n\n' +
                           'This action cannot be undone!';
        }
        
        swal({
            title: "Delete Homework?",
            text: warningMessage,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: confirmButtonText,
            cancelButtonText: "No, keep it",
            closeOnConfirm: false,
            showLoaderOnConfirm: true
        }, function(isConfirm) {
            if (isConfirm) {
                // Show loading state
                swal({
                    title: "Deleting...",
                    text: "Please wait while we delete the homework and all submissions",
                    type: "info",
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                
                // Submit the delete form
                setTimeout(function() {
                    document.getElementById('deleteHomeworkForm').submit();
                }, 500);
            }
        });
    }

    // ==========================================
    // ✅ GRADE MODAL FUNCTIONS
    // ==========================================
    function openGradeModal(submissionId, studentName) {
        document.getElementById('modalStudentName').textContent = studentName;
        document.getElementById('modalSubmissionId').value = submissionId;
        document.getElementById('gradeForm').action = '/admin/homework/submissions/' + submissionId + '/grade';
        
        // Show modal and backdrop
        const modal = document.getElementById('gradeModal');
        const backdrop = document.getElementById('gradeModalBackdrop');
        
        if (modal && backdrop) {
            modal.classList.add('show');
            backdrop.style.display = 'block';
            
            // Prevent body scroll when modal is open
            document.body.style.overflow = 'hidden';
            document.body.style.paddingRight = '15px'; // Prevent content shift
        }
    }

    function closeGradeModal() {
        const modal = document.getElementById('gradeModal');
        const backdrop = document.getElementById('gradeModalBackdrop');
        const form = document.getElementById('gradeForm');
        const commentCount = document.getElementById('commentCount');
        
        if (modal) modal.classList.remove('show');
        if (backdrop) backdrop.style.display = 'none';
        if (form) form.reset();
        if (commentCount) commentCount.textContent = '0';
        
        // Restore body scroll
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }

    // ==========================================
    // ✅ DOCUMENT READY HANDLERS
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {
        
        // ✅ Character count for grade comments
        const commentField = document.querySelector('textarea[name="teacher_comments"]');
        const commentCount = document.getElementById('commentCount');
        
        if (commentField && commentCount) {
            commentField.addEventListener('input', function() {
                const length = this.value.length;
                commentCount.textContent = length;
                
                // Warn if approaching limit (if you have a max length)
                if (this.hasAttribute('maxlength')) {
                    const maxLength = parseInt(this.getAttribute('maxlength'));
                    if (length > maxLength * 0.9) {
                        commentCount.style.color = '#f0ad4e'; // Warning color
                    } else {
                        commentCount.style.color = '';
                    }
                }
            });
        }

        // ✅ Close modal when clicking backdrop
        const backdrop = document.getElementById('gradeModalBackdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function() {
                closeGradeModal();
            });
        }

        // ✅ Prevent grade form double submission
        const gradeForm = document.getElementById('gradeForm');
        if (gradeForm) {
            gradeForm.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                
                // Check if already submitting
                if (submitBtn && submitBtn.disabled) {
                    e.preventDefault();
                    return false;
                }
                
                // Validate grade input
                const gradeInput = this.querySelector('input[name="grade"]');
                if (gradeInput && gradeInput.value) {
                    const grade = parseFloat(gradeInput.value);
                    const maxGrade = parseFloat(gradeInput.getAttribute('max') || 100);
                    
                    if (grade < 0 || grade > maxGrade) {
                        e.preventDefault();
                        swal({
                            title: "Invalid Grade!",
                            text: `Grade must be between 0 and ${maxGrade}`,
                            type: "error",
                            confirmButtonText: "OK"
                        });
                        return false;
                    }
                }
                
                // Disable submit button and show loading
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="ti-reload fa-spin"></i> Submitting Grade...';
                }
            });
        }

        // ✅ Grade input validation (real-time)
        const gradeInput = document.querySelector('input[name="grade"]');
        if (gradeInput) {
            gradeInput.addEventListener('input', function() {
                const value = parseFloat(this.value);
                const max = parseFloat(this.getAttribute('max') || 100);
                
                if (value < 0) {
                    this.value = 0;
                } else if (value > max) {
                    this.value = max;
                }
            });
        }
    });

    // ==========================================
    // ✅ KEYBOARD SHORTCUTS
    // ==========================================
    document.addEventListener('keydown', function(event) {
        // Close modal on ESC key
        if (event.key === 'Escape' || event.keyCode === 27) {
            const modal = document.getElementById('gradeModal');
            if (modal && modal.classList.contains('show')) {
                closeGradeModal();
            }
        }
    });
</script>
@endpush







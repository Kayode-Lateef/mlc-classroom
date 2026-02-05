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

    .progress-custom {
        height: 12px;
        border-radius: 6px;
    }

    /* Highlight changed rows */
.submission-row.changed {
    background-color: #fff3cd !important;
}

.submission-row.changed td {
    border-left: 3px solid #ffc107;
}

/* Input styling */
.grade-input, .comments-input {
    border: 1px solid #ced4da;
    transition: all 0.2s;
}

.grade-input:focus, .comments-input:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.grade-input.changed, .comments-input.changed {
    border-color: #ffc107;
    background-color: #fff3cd;
}

/* Quick action buttons */
.btn-xs {
    padding: 2px 6px;
    font-size: 11px;
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

                                <!-- Display Topics -->
                                @if($homework->topics->count() > 0)
                                    <div class="topics-section" style="margin-bottom: 20px;">
                                        <h4><i class="ti-bookmark-alt"></i> Topics Covered</h4>
                                        <div class="topics-list">
                                            @foreach($homework->topics as $topic)
                                                <span class="badge badge-info" style="padding: 8px 12px; margin-right: 8px;">
                                                    {{ $topic->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

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
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <h4><i class="ti-user"></i> Student Submissions ({{ $homework->submissions->count() }})</h4>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-primary" id="save-all-changes-btn" style="display: none;">
                                                <i class="ti-save"></i> Save All Changes
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">
                                                    <input type="checkbox" id="select-all-checkbox" title="Select all">
                                                </th>
                                                <th width="25%">Student</th>
                                                <th width="12%">Status</th>
                                                <th width="15%">Submitted</th>
                                                <th width="15%">Grade</th>
                                                <th width="28%">Comments</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($homework->submissions as $submission)
                                            <tr data-submission-id="{{ $submission->id }}" class="submission-row">
                                                <td>
                                                    <input type="checkbox" 
                                                        class="submission-checkbox" 
                                                        value="{{ $submission->id }}"
                                                        data-status="{{ $submission->status }}">
                                                </td>
                                                
                                                <!-- Student Info -->
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        <div style="flex-shrink: 0;">
                                                            @if($submission->student->profile_photo)
                                                            <img src="{{ asset('storage/' . $submission->student->profile_photo) }}" 
                                                                alt="{{ $submission->student->full_name }}" 
                                                                class="student-avatar">
                                                            @else
                                                            <div class="student-avatar-initial">
                                                                {{ strtoupper(substr($submission->student->first_name, 0, 1)) }}{{ strtoupper(substr($submission->student->last_name, 0, 1)) }}
                                                            </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <strong>{{ $submission->student->full_name }}</strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                {{ $submission->student->parent->name ?? 'No parent' }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                
                                                <!-- Status with Quick Actions -->
                                                <td>
                                                    @if($submission->status === 'pending')
                                                        <span class="badge badge-secondary">Pending</span>
                                                        <br>
                                                        <button class="btn btn-xs btn-warning mt-1 quick-mark-submitted" 
                                                                data-id="{{ $submission->id }}"
                                                                title="Mark as submitted">
                                                            <i class="ti-check"></i> Submit
                                                        </button>
                                                    @elseif($submission->status === 'submitted')
                                                        <span class="badge badge-warning">Submitted</span>
                                                    @elseif($submission->status === 'late')
                                                        <span class="badge badge-danger">Late</span>
                                                    @elseif($submission->status === 'graded')
                                                        <span class="badge badge-success">Graded</span>
                                                    @endif
                                                </td>
                                                
                                                <!-- Submitted Date -->
                                                <td>
                                                    @if($submission->submitted_date)
                                                        <small>{{ $submission->submitted_date->format('d/m/Y') }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $submission->submitted_date->format('H:i') }}</small>
                                                        @if($submission->submittedByUser)
                                                            <br><small class="text-muted" title="Submitted by">{{ $submission->submittedByUser->name }}</small>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                
                                                <!-- INLINE GRADE INPUT -->
                                                <td>
                                                    @if(in_array($submission->status, ['submitted', 'late', 'graded']))
                                                        <input type="text" 
                                                            class="form-control form-control-sm grade-input" 
                                                            data-submission-id="{{ $submission->id }}"
                                                            data-original-value="{{ $submission->grade }}"
                                                            value="{{ $submission->grade }}" 
                                                            placeholder="e.g., 85, A+"
                                                            style="width: 100px;">
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                
                                                <!-- INLINE COMMENTS INPUT -->
                                                <td>
                                                    @if(in_array($submission->status, ['submitted', 'late', 'graded']))
                                                        <textarea class="form-control form-control-sm comments-input" 
                                                                data-submission-id="{{ $submission->id }}"
                                                                data-original-value="{{ $submission->teacher_comments }}"
                                                                rows="2" 
                                                                placeholder="Add feedback..."
                                                                style="resize: vertical;">{{ $submission->teacher_comments }}</textarea>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>


                            </div>
                            <!-- Bulk Actions at Bottom -->                           
                            <div class="card-footer" style="background: #f8f9fa; padding: 15px;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-sm btn-primary" id="select-all-bottom">
                                            <i class="ti-check-box"></i> Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" id="bulk-mark-submitted-bottom">
                                            <i class="ti-pencil-alt"></i> Mark Selected as Submitted
                                        </button>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <span class="text-muted" id="changes-indicator" style="display: none;">
                                            <i class="ti-info-alt"></i> You have unsaved changes
                                        </span>
                                    </div>
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

                                    {{-- ✅ UPDATED: Remove onsubmit, add ID, change to button --}}
                                    <form action="{{ route('superadmin.homework.destroy', $homework) }}" method="POST" id="deleteHomeworkForm">
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

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let changedSubmissions = new Set();
    
    // ========================================
    // TRACK CHANGES IN GRADE/COMMENTS
    // ========================================
    $('.grade-input, .comments-input').on('input', function() {
        const submissionId = $(this).data('submission-id');
        const originalValue = $(this).data('original-value') || '';
        const currentValue = $(this).val();
        
        if (currentValue !== originalValue) {
            $(this).addClass('changed');
            changedSubmissions.add(submissionId);
            $(`tr[data-submission-id="${submissionId}"]`).addClass('changed');
        } else {
            $(this).removeClass('changed');
            changedSubmissions.delete(submissionId);
            
            // Check if row has any other changes
            const row = $(`tr[data-submission-id="${submissionId}"]`);
            if (row.find('.changed').length === 0) {
                row.removeClass('changed');
            }
        }
        
        updateChangesIndicator();
    });
    
    // Auto-save on blur (optional - saves individual field)
    $('.grade-input, .comments-input').on('blur', function() {
        const submissionId = $(this).data('submission-id');
        const originalValue = $(this).data('original-value') || '';
        const currentValue = $(this).val();
        
        if (currentValue !== originalValue) {
            autoSaveSingle(submissionId);
        }
    });
    
    // ========================================
    // AUTO-SAVE SINGLE SUBMISSION
    // ========================================
    function autoSaveSingle(submissionId) {
        const row = $(`tr[data-submission-id="${submissionId}"]`);
        const grade = row.find('.grade-input').val();
        const comments = row.find('.comments-input').val();
        
        if (!grade) return; // Don't save if no grade
        
        $.ajax({
            url: '{{ route("superadmin.homework.submissions.grade", $homework->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                submission_id: submissionId,
                grade: grade,
                teacher_comments: comments
            },
            success: function(response) {
                // Update original values
                row.find('.grade-input').data('original-value', grade);
                row.find('.comments-input').data('original-value', comments);
                
                // Remove changed indicators
                row.find('.grade-input, .comments-input').removeClass('changed');
                row.removeClass('changed');
                changedSubmissions.delete(submissionId);
                updateChangesIndicator();
                
                // Update status badge
                row.find('td:eq(2)').html('<span class="badge badge-success">Graded</span>');
                
                // ✅ UPDATED: SweetAlert success message
                toastr.success('Grade saved successfully!', 'Success', {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 3000
                });
                
                showSuccessIndicator(row);
            },
            error: function(xhr) {
                // ✅ UPDATED: SweetAlert error message
                swal({
                    title: "Error!",
                    text: "Failed to save grade. Please try again.",
                    type: "error",
                    confirmButtonText: "OK"
                });
            }
        });
    }
    
    // ========================================
    // ✅ UPDATED: SAVE ALL CHANGES WITH SWEETALERT
    // ========================================
    $('#save-all-changes-btn').click(function() {
        if (changedSubmissions.size === 0) {
            swal({
                title: "No Changes",
                text: "No changes to save",
                type: "info",
                confirmButtonText: "OK"
            });
            return;
        }
        
        swal({
            title: "Save All Grades?",
            text: `Save grades for ${changedSubmissions.size} student(s)?`,
            type: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, save all!",
            cancelButtonText: "Cancel",
            closeOnConfirm: false
        }, function(isConfirm) {
            if (!isConfirm) return;
            
            // Show loading
            swal({
                title: "Saving...",
                text: "Please wait while we save all grades",
                type: "info",
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            let savedCount = 0;
            const total = changedSubmissions.size;
            
            changedSubmissions.forEach(submissionId => {
                const row = $(`tr[data-submission-id="${submissionId}"]`);
                const grade = row.find('.grade-input').val();
                const comments = row.find('.comments-input').val();
                
                if (!grade) return;
                
                $.ajax({
                    url: '{{ route("superadmin.homework.submissions.grade", $homework->id) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        submission_id: submissionId,
                        grade: grade,
                        teacher_comments: comments
                    },
                    success: function() {
                        row.find('.grade-input').data('original-value', grade);
                        row.find('.comments-input').data('original-value', comments);
                        row.find('.grade-input, .comments-input').removeClass('changed');
                        row.removeClass('changed');
                        row.find('td:eq(2)').html('<span class="badge badge-success">Graded</span>');
                        
                        savedCount++;
                        if (savedCount === total) {
                            changedSubmissions.clear();
                            updateChangesIndicator();
                            
                            swal({
                                title: "Success!",
                                text: `Successfully graded ${savedCount} submission(s)!`,
                                type: "success",
                                confirmButtonText: "OK"
                            }, function() {
                                location.reload();
                            });
                        }
                    }
                });
            });
        });
    });
    
// ========================================
// ✅ UPDATED: QUICK MARK AS SUBMITTED WITH BROWSER ALERT
// ========================================
$('.quick-mark-submitted').click(function() {
    const submissionId = $(this).data('id');
    const row = $(`tr[data-submission-id="${submissionId}"]`);
    const studentName = row.find('strong').first().text();
    
    // Confirm with a simple browser alert (using the confirm dialog)
    const confirmAction = confirm(`Mark ${studentName}'s homework as submitted?`);
    
    if (!confirmAction) return;

    // Simulate processing (show a simple alert here)
    alert("Processing... Marking homework as submitted");

    $.ajax({
        url: '{{ route("superadmin.homework.mark-submitted", $homework->id) }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            submission_id: submissionId
        },
        success: function() {
            alert("Success! Homework marked as submitted.");
            location.reload();  // Reload the page after success
        },
        error: function() {
            alert("Error! Failed to mark as submitted.");
        }
    });
});

    // ========================================
    // ✅ UPDATED: BULK MARK AS SUBMITTED WITH SWEETALERT
    // ========================================
    $('#bulk-mark-submitted-bottom').click(function() {
        const checked = $('.submission-checkbox:checked');
        
        if (checked.length === 0) {
            swal({
                title: "No Selection",
                text: "Please select at least one submission",
                type: "warning",
                confirmButtonText: "OK"
            });
            return;
        }
        
        const submissionIds = checked.map(function() { return $(this).val(); }).get();
        
        swal({
            title: "Bulk Mark as Submitted?",
            text: `Mark ${submissionIds.length} submission(s) as submitted?`,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#f0ad4e",
            cancelButtonColor: "#6c757d",
            confirmButtonText: `Yes, mark ${submissionIds.length} submissions`,
            cancelButtonText: "Cancel",
            closeOnConfirm: false
        }, function(isConfirm) {
            if (!isConfirm) return;
            
            // Show loading
            swal({
                title: "Processing...",
                text: `Marking ${submissionIds.length} submissions...`,
                type: "info",
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            const form = $('<form>', {
                method: 'POST',
                action: '{{ route("superadmin.homework.bulk-mark-submitted", $homework->id) }}'
            });
            
            form.append($('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }));
            submissionIds.forEach(id => {
                form.append($('<input>', { type: 'hidden', name: 'submission_ids[]', value: id }));
            });
            
            $('body').append(form);
            
            setTimeout(function() {
                form.submit();
            }, 500);
        });
    });
    
    // ========================================
    // SELECT ALL
    // ========================================
    $('#select-all-checkbox, #select-all-bottom').on('change click', function() {
        const isChecked = $(this).prop('checked');
        $('.submission-checkbox').prop('checked', isChecked !== false);
    });
    
    // ========================================
    // HELPER FUNCTIONS
    // ========================================
    function updateChangesIndicator() {
        if (changedSubmissions.size > 0) {
            $('#changes-indicator').show();
            $('#save-all-changes-btn').show();
        } else {
            $('#changes-indicator').hide();
            $('#save-all-changes-btn').hide();
        }
    }
    
    function showSuccessIndicator(row) {
        row.css('background-color', '#d4edda');
        setTimeout(() => {
            row.css('background-color', '');
        }, 1000);
    }
    
    // ✅ UPDATED: Warn before leaving with SweetAlert
    window.addEventListener('beforeunload', function(e) {
        if (changedSubmissions.size > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    // ========================================
    // ✅ TOASTR NOTIFICATIONS (Flash Messages)
    // ========================================
    @if(session('success'))
        toastr.success("{{ session('success') }}", "Success", {
            closeButton: true,
            progressBar: true,
            timeOut: 5000,
            positionClass: "toast-top-right"
        });
    @endif

    @if(session('error'))
        toastr.error("{{ session('error') }}", "Error", {
            closeButton: true,
            progressBar: true,
            timeOut: 8000,
            positionClass: "toast-top-right"
        });
    @endif

    @if(session('warning'))
        toastr.warning("{{ session('warning') }}", "Warning", {
            closeButton: true,
            progressBar: true,
            timeOut: 6000,
            positionClass: "toast-top-right"
        });
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            toastr.error("{{ $error }}", "Validation Error", {
                closeButton: true,
                progressBar: true,
                timeOut: 6000,
                positionClass: "toast-top-right"
            });
        @endforeach
    @endif
});

// ==========================================
// ✅ DELETE HOMEWORK CONFIRMATION (GLOBAL)
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
</script>

@endpush







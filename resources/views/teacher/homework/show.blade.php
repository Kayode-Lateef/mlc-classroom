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
        background-color: #3386f7;
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

    /* Quick action buttons */
    .btn-xs {
        padding: 2px 6px;
        font-size: 11px;
    }

    /* Inline date edit */
    .edit-date-btn {
        padding: 1px 5px;
        border-color: #3386f7;
        color: #3386f7;
    }

    .edit-date-btn:hover {
        background: #3386f7;
        color: white;
    }

    .date-input {
        border: 1px solid #3386f7;
    }

    .date-input:focus {
        border-color: #3386f7;
        box-shadow: 0 0 0 0.15rem rgba(51, 134, 247, 0.25);
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
                                    <li><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('teacher.homework.index') }}">Homework</a></li>
                                    <li class="active">Details</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <div class="row">
                        <!-- ============================================ -->
                        <!-- MAIN CONTENT (Left Column) -->
                        <!-- ============================================ -->
                        <div class="col-lg-8">

                            {{-- ======================================== --}}
                            {{-- 1. HOMEWORK DETAILS --}}
                            {{-- ======================================== --}}
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
                                                {{ $homework->class->name }} &bull; {{ $homework->class->subject }}
                                            </p>
                                        </div>
                                        <span class="badge {{ $statusClass }}" style="padding: 6px 12px;">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Display Topics with Max Scores -->
                                @if($homework->topics->count() > 0)
                                    <div class="topics-section" style="margin-bottom: 20px;">
                                        <h4><i class="ti-bookmark-alt"></i> Topics Covered</h4>
                                        <div class="topics-list">
                                            @foreach($homework->topics as $topic)
                                                <span class="badge badge-info" style="padding: 8px 12px; margin-right: 8px; margin-bottom: 5px;">
                                                    {{ $topic->name }}
                                                    @if($topic->pivot->max_score)
                                                        <span style="background: rgba(255,255,255,0.25); padding: 1px 6px; border-radius: 3px; margin-left: 4px;">
                                                            /{{ $topic->pivot->max_score }}
                                                        </span>
                                                    @endif
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
                                        <a href="{{ route('teacher.homework.download', $homework) }}" class="btn btn-primary btn-sm">
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

                            {{-- ======================================== --}}
                            {{-- 2. SUBMISSION STATISTICS --}}
                            {{-- ======================================== --}}
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

                            {{-- ======================================== --}}
                            {{-- 3. STUDENT SUBMISSIONS TABLE --}}
                            {{-- (Submission tracking + read-only scores) --}}
                            {{-- ======================================== --}}
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <h4><i class="ti-user"></i> Student Submissions ({{ $homework->submissions->count() }})</h4>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">
                                                    <input type="checkbox" id="select-all-checkbox" title="Select all">
                                                </th>
                                                <th width="30%">Student</th>
                                                <th width="13%">Status</th>
                                                <th width="17%">Submitted</th>
                                                @if($homework->topics->count() > 0)
                                                <th width="20%">Topic Score</th>
                                                @endif
                                                <th width="15%">Overall</th>
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

                                                <!-- Submitted Date (Inline Editable) -->
                                                <td>
                                                    @if($submission->submitted_date)
                                                        {{-- Display Mode --}}
                                                        <div class="date-display" data-submission-id="{{ $submission->id }}">
                                                            <small>{{ $submission->submitted_date->format('d/m/Y') }}</small>
                                                            <br>
                                                            <small class="text-muted">{{ $submission->submitted_date->format('H:i') }}</small>
                                                            @if($submission->submittedByUser)
                                                                <br><small class="text-muted" title="Submitted by">{{ $submission->submittedByUser->name }}</small>
                                                            @endif
                                                            <br>
                                                            <button class="btn btn-xs btn-outline-primary mt-1 edit-date-btn"
                                                                    data-submission-id="{{ $submission->id }}"
                                                                    data-current-date="{{ $submission->submitted_date->format('Y-m-d') }}"
                                                                    title="Edit submitted date">
                                                                <i class="ti-pencil"></i> Edit
                                                            </button>
                                                        </div>

                                                        {{-- Edit Mode (hidden by default) --}}
                                                        <div class="date-edit" data-submission-id="{{ $submission->id }}" style="display: none;">
                                                            <input type="date"
                                                                class="form-control form-control-sm date-input"
                                                                data-submission-id="{{ $submission->id }}"
                                                                value="{{ $submission->submitted_date->format('Y-m-d') }}"
                                                                max="{{ now()->format('Y-m-d') }}"
                                                                style="width: 140px; font-size: 1rem;">
                                                            <div class="mt-1">
                                                                <button class="btn btn-xs btn-success save-date-btn"
                                                                        data-submission-id="{{ $submission->id }}"
                                                                        title="Save">
                                                                    <i class="ti-check"></i>
                                                                </button>
                                                                <button class="btn btn-xs btn-secondary cancel-date-btn"
                                                                        data-submission-id="{{ $submission->id }}"
                                                                        title="Cancel">
                                                                    <i class="ti-close"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>

                                                <!-- Topic Score Summary (Read-Only) -->
                                                @if($homework->topics->count() > 0)
                                                <td>
                                                    @if($submission->topicGrades->count() > 0)
                                                        @php
                                                            $topicCount = $homework->topics->count();
                                                            $gradedTopicCount = $submission->topicGrades->count();
                                                            $totalScore = $submission->topicGrades->sum('score');
                                                            $totalMax = $submission->topicGrades->sum('max_score');
                                                            $percentage = $totalMax > 0 ? round(($totalScore / $totalMax) * 100, 1) : 0;
                                                        @endphp
                                                        <strong style="color: {{ $percentage >= 70 ? '#28a745' : ($percentage >= 50 ? '#e06829' : '#dc3545') }};">
                                                            {{ $totalScore }}/{{ $totalMax }}
                                                        </strong>
                                                        <br>
                                                        <small style="color: {{ $percentage >= 70 ? '#28a745' : ($percentage >= 50 ? '#e06829' : '#dc3545') }}; font-weight: 600;">
                                                            {{ $percentage }}%
                                                        </small>
                                                        <br>
                                                        <small class="text-muted">{{ $gradedTopicCount }}/{{ $topicCount }} topics</small>
                                                    @elseif(in_array($submission->status, ['submitted', 'late', 'graded']))
                                                        <small class="text-muted"><i class="ti-time"></i> Not graded</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                @endif

                                                <!-- Overall Grade (Read-Only) -->
                                                <td>
                                                    @if($submission->grade)
                                                        <strong style="color: #28a745;">{{ $submission->grade }}</strong>
                                                        @if($submission->graded_at)
                                                            <br><small class="text-muted">{{ $submission->graded_at->format('d/m/Y') }}</small>
                                                        @endif
                                                    @elseif(in_array($submission->status, ['submitted', 'late']))
                                                        <small class="text-muted">Awaiting</small>
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

                            <!-- Bulk Actions -->
                            <div class="card-footer" style="background: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 0 0 8px 8px;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-sm btn-primary" id="select-all-bottom">
                                            <i class="ti-check-box"></i> Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" id="bulk-mark-submitted-bottom">
                                            <i class="ti-pencil-alt"></i> Mark Selected as Submitted
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- ======================================== --}}
                            {{-- 4. TOPIC-LEVEL GRADING --}}
                            {{-- (Detailed score/max grading per topic) --}}
                            {{-- ======================================== --}}
                            @php $routePrefix = 'teacher'; @endphp
                            @include('partials.homework._topic_grading_section', ['routePrefix' => $routePrefix])

                        </div>

                        <!-- ============================================ -->
                        <!-- SIDEBAR (Right Column) -->
                        <!-- ============================================ -->
                        <div class="col-lg-4">
                            <!-- Quick Actions -->
                            <div class="card alert">
                                <div class="card-header mb-3">
                                    <h4><i class="ti-settings"></i> Actions</h4>
                                </div>
                                <div class="card-body">
                                    <a href="{{ route('teacher.homework.edit', $homework) }}" class="btn btn-primary btn-block mb-2">
                                        <i class="ti-pencil-alt"></i> Edit Homework
                                    </a>

                                    @if($homework->file_path)
                                    <a href="{{ route('teacher.homework.download', $homework) }}" class="btn btn-success btn-block mb-2">
                                        <i class="ti-download"></i> Download File
                                    </a>
                                    @endif

                                    <form action="{{ route('teacher.homework.destroy', $homework) }}" method="POST" id="deleteHomeworkForm">
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

    // ========================================
    // QUICK MARK AS SUBMITTED
    // ========================================
    $(document).on('click', '.quick-mark-submitted', function(e) {
        e.preventDefault();

        const submissionId = $(this).data('id');
        const row = $(`tr[data-submission-id="${submissionId}"]`);
        const studentName = row.find('strong').first().text();

        swal({
            title: "Mark as Submitted?",
            text: "Mark " + studentName + "'s homework as submitted?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#f0ad4e",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, mark submitted!",
            cancelButtonText: "Cancel",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function(isConfirm) {
            if (!isConfirm) return;

            swal({
                title: "Processing...",
                text: "Marking homework as submitted",
                type: "info",
                showConfirmButton: false,
                allowEscapeKey: false
            });

            $.ajax({
                url: '{{ route("teacher.homework.mark-submitted", $homework->id) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    submission_id: submissionId
                },
                success: function(response) {
                    swal({
                        title: "Success!",
                        text: "Homework marked as submitted.",
                        type: "success"
                    }, function() {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    swal({
                        title: "Error!",
                        text: "Failed to mark as submitted. Please try again.",
                        type: "error"
                    });
                }
            });
        });
    });

    // ========================================
    // BULK MARK AS SUBMITTED
    // ========================================
    $('#bulk-mark-submitted-bottom').click(function() {
        const checked = $('.submission-checkbox:checked');

        if (checked.length === 0) {
            swal({
                title: "No Selection",
                text: "Please select at least one submission",
                type: "warning"
            });
            return;
        }

        const submissionIds = checked.map(function() { return $(this).val(); }).get();
        const count = submissionIds.length;

        swal({
            title: "Bulk Mark as Submitted?",
            text: "Mark " + count + " submission(s) as submitted?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#f0ad4e",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, mark " + count + " submissions",
            cancelButtonText: "Cancel",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function(isConfirm) {
            if (!isConfirm) return;

            swal({
                title: "Processing...",
                text: "Marking " + count + " submissions...",
                type: "info",
                showConfirmButton: false,
                allowEscapeKey: false
            });

            const form = $('<form>', {
                method: 'POST',
                action: '{{ route("teacher.homework.bulk-mark-submitted", $homework->id) }}'
            });

            form.append($('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }));
            submissionIds.forEach(function(id) {
                form.append($('<input>', { type: 'hidden', name: 'submission_ids[]', value: id }));
            });

            $('body').append(form);
            form.submit();
        });
    });

    // ========================================
    // SELECT ALL CHECKBOX
    // ========================================
    $('#select-all-checkbox').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.submission-checkbox').prop('checked', isChecked);
    });

    $('#select-all-bottom').on('click', function() {
        const allChecked = $('.submission-checkbox:checked').length === $('.submission-checkbox').length;
        $('.submission-checkbox').prop('checked', !allChecked);
        $('#select-all-checkbox').prop('checked', !allChecked);
    });

    // ========================================
    // TOASTR FLASH MESSAGES
    // ========================================
    @if(session('success'))
        if (typeof toastr !== 'undefined') {
            toastr.success("{{ session('success') }}", "Success", {
                closeButton: true,
                progressBar: true,
                timeOut: 5000,
                positionClass: "toast-top-right"
            });
        }
    @endif

    @if(session('error'))
        if (typeof toastr !== 'undefined') {
            toastr.error("{{ session('error') }}", "Error", {
                closeButton: true,
                progressBar: true,
                timeOut: 8000,
                positionClass: "toast-top-right"
            });
        }
    @endif

    @if(session('warning'))
        if (typeof toastr !== 'undefined') {
            toastr.warning("{{ session('warning') }}", "Warning", {
                closeButton: true,
                progressBar: true,
                timeOut: 6000,
                positionClass: "toast-top-right"
            });
        }
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            if (typeof toastr !== 'undefined') {
                toastr.error("{{ $error }}", "Validation Error", {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 6000,
                    positionClass: "toast-top-right"
                });
            }
        @endforeach
    @endif

    // ========================================
    // INLINE EDIT SUBMITTED DATE
    // ========================================

    // Show edit mode
    $(document).on('click', '.edit-date-btn', function(e) {
        e.preventDefault();
        const submissionId = $(this).data('submission-id');
        $(`.date-display[data-submission-id="${submissionId}"]`).hide();
        $(`.date-edit[data-submission-id="${submissionId}"]`).show();
        $(`.date-edit[data-submission-id="${submissionId}"] .date-input`).focus();
    });

    // Cancel edit
    $(document).on('click', '.cancel-date-btn', function(e) {
        e.preventDefault();
        const submissionId = $(this).data('submission-id');
        $(`.date-edit[data-submission-id="${submissionId}"]`).hide();
        $(`.date-display[data-submission-id="${submissionId}"]`).show();
    });

    // Save date
    $(document).on('click', '.save-date-btn', function(e) {
        e.preventDefault();
        const submissionId = $(this).data('submission-id');
        const newDate = $(`.date-edit[data-submission-id="${submissionId}"] .date-input`).val();

        if (!newDate) {
            if (typeof toastr !== 'undefined') {
                toastr.warning('Please select a date.', 'Warning');
            }
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="ti-reload ti-spin"></i>');

        $.ajax({
            url: '{{ route("teacher.homework.update-submitted-date", $homework->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                submission_id: submissionId,
                submitted_date: newDate
            },
            success: function(response) {
                if (response.success) {
                    const row = $(`tr[data-submission-id="${submissionId}"]`);

                    // Update the date display
                    const displayDiv = $(`.date-display[data-submission-id="${submissionId}"]`);
                    displayDiv.find('small').first().text(response.data.formatted_date);
                    displayDiv.find('.edit-date-btn').data('current-date', newDate);

                    // Update the status badge
                    row.find('td:eq(2)').html(response.data.status_badge);

                    // Switch back to display mode
                    $(`.date-edit[data-submission-id="${submissionId}"]`).hide();
                    displayDiv.show();

                    // Brief green flash
                    row.css('background-color', '#d4edda');
                    setTimeout(function() { row.css('background-color', ''); }, 1500);

                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message, 'Success', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                    }
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to update date.';
                if (typeof toastr !== 'undefined') {
                    toastr.error(msg, 'Error', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 5000
                    });
                } else {
                    swal({ title: "Error!", text: msg, type: "error" });
                }
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="ti-check"></i>');
            }
        });
    });

    // Save on Enter key
    $(document).on('keydown', '.date-input', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const submissionId = $(this).data('submission-id');
            $(`.save-date-btn[data-submission-id="${submissionId}"]`).trigger('click');
        }
        if (e.key === 'Escape') {
            e.preventDefault();
            const submissionId = $(this).data('submission-id');
            $(`.cancel-date-btn[data-submission-id="${submissionId}"]`).trigger('click');
        }
    });
});

    // ==========================================
    // DELETE HOMEWORK CONFIRMATION
    // ==========================================
    function confirmDeleteHomework(submissionCount) {
        var warningMessage = '';
        var confirmButtonText = 'Yes, delete it!';

        if (submissionCount > 0) {
            warningMessage = 'This homework has ' + submissionCount + ' submission(s) from students. ' +
                        'All submissions will be permanently deleted! ' +
                        'This action cannot be undone!';
            confirmButtonText = 'Delete (' + submissionCount + ' submissions)';
        } else {
            warningMessage = 'This homework has no submissions yet. ' +
                        'Are you sure you want to delete it? ' +
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
            closeOnCancel: true
        }, function(isConfirm) {
            if (isConfirm) {
                swal({
                    title: "Deleting...",
                    text: "Please wait while we delete the homework",
                    type: "info",
                    showConfirmButton: false,
                    allowEscapeKey: false
                });

                document.getElementById('deleteHomeworkForm').submit();
            }
        });
    }
</script>
@endpush
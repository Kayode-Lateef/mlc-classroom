@extends('layouts.app')

@push('styles')
<style>
    .current-info-box {
        background-color: #e7f3ff;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .warning-box {
        background-color: #fff3cd;
        padding: 15px;
        border-radius: 4px;
    }

    .file-upload-box {
        border: 2px dashed #cbd5e0;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        background-color: #f8f9fa;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-upload-box:hover {
        border-color: #007bff;
        background-color: #e7f3ff;
    }

    .current-file-box {
        background-color: #e7f3ff;
        border: 1px solid #007bff;
        border-radius: 6px;
        padding: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .guideline-item {
        display: flex;
        align-items: start;
        margin-bottom: 12px;
    }

    .guideline-item i {
        color: #007bff;
        margin-right: 10px;
        margin-top: 2px;
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
                                <h1>Edit Homework Assignment</h1>
                            </div>
                        </div>
                        <span>Update homework assignment details</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('admin.homework.index') }}">Homework</a></li>
                                    <li><a href="{{ route('admin.homework.show', $homework) }}">Details</a></li>
                                    <li class="active">Edit</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.homework.update', $homework) }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      id="homeworkUpdateForm">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Main Content -->
                        <div class="col-lg-8">
                            <!-- Homework Details -->
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-pencil-alt"></i> Homework Details</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Class Selection -->
                                    <div class="form-group">
                                        <label class="required-field">Class</label>
                                        <select name="class_id" required class="form-control">
                                            <option value="">Select a class...</option>
                                            @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id', $homework->class_id) == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }} - {{ $class->subject }}
                                                @if($class->teacher)
                                                ({{ $class->teacher->name }})
                                                @endif
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('class_id')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Title -->
                                    <div class="form-group">
                                        <label class="required-field">Homework Title</label>
                                        <input 
                                            type="text" 
                                            name="title" 
                                            value="{{ old('title', $homework->title) }}"
                                            required
                                            maxlength="255"
                                            class="form-control"
                                        >
                                        @error('title')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Topics Selection with Max Scores -->
                                    <div class="form-group">
                                        <label for="topic_ids">
                                            <i class="ti-bookmark-alt"></i> Topics
                                            <span class="text-muted">(Select topics and set max scores)</span>
                                        </label>
                                        <select name="topic_ids[]" id="topic_ids" class="form-control select2" multiple>
                                            <option value="">-- Select Topics --</option>
                                            @foreach($topics as $topic)
                                                <option value="{{ $topic->id }}"
                                                    {{ in_array($topic->id, old('topic_ids', [])) ? 'selected' : '' }}>
                                                    {{ $topic->name }}
                                                    @if($topic->subject)
                                                        ({{ $topic->subject }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">
                                            Select the topics this homework covers. You can set max scores below.
                                        </small>
                                    </div>

                                    <!-- Dynamic Max Score Inputs (shown after topics are selected) -->
                                    <div id="topic-max-scores-container" style="display: none;" class="mb-4">
                                        <label><i class="ti-stats-up"></i> Max Scores per Topic</label>
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="max-scores-table">
                                                <thead style="background: #f8f9fa;">
                                                    <tr>
                                                        <th style="padding: 8px 12px;">Topic</th>
                                                        <th style="padding: 8px 12px; width: 150px;">Max Score</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="max-scores-body">
                                                    <!-- Dynamically populated -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea 
                                            name="description" 
                                            rows="5"
                                            maxlength="2000"
                                            class="form-control"
                                        >{{ old('description', $homework->description) }}</textarea>
                                        @error('description')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Dates -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="required-field">Assigned Date</label>
                                                <input 
                                                    type="date" 
                                                    name="assigned_date" 
                                                    value="{{ old('assigned_date', $homework->assigned_date->format('Y-m-d')) }}"
                                                    required
                                                    class="form-control"
                                                >
                                                @error('assigned_date')
                                                <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="required-field">Due Date</label>
                                                <input 
                                                    type="date" 
                                                    name="due_date" 
                                                    value="{{ old('due_date', $homework->due_date->format('Y-m-d')) }}"
                                                    required
                                                    class="form-control"
                                                >
                                                @error('due_date')
                                                <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Progress Sheet Link -->
                                    <div class="form-group">
                                        <label>Link to Progress Sheet (Optional)</label>
                                        <select name="progress_sheet_id" class="form-control">
                                            <option value="">No progress sheet</option>
                                            @foreach($progressSheets as $sheet)
                                            <option value="{{ $sheet->id }}" {{ old('progress_sheet_id', $homework->progress_sheet_id) == $sheet->id ? 'selected' : '' }}>
                                                {{ $sheet->class->name }} - {{ $sheet->topic }} ({{ $sheet->date->format('d M Y') }})
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('progress_sheet_id')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Current File -->
                                    @if($homework->file_path)
                                    <div class="form-group">
                                        <label>Current Attachment</label>
                                        <div class="current-file-box">
                                            <div style="display: flex; align-items: center;">
                                                <i class="ti-file" style="font-size: 1.5rem; color: #007bff; margin-right: 12px;"></i>
                                                <span style="color: #495057;">{{ basename($homework->file_path) }}</span>
                                            </div>
                                            <a href="{{ route('admin.homework.download', $homework) }}" class="btn btn-sm btn-primary">
                                                <i class="ti-download"></i> Download
                                            </a>
                                        </div>
                                        <small class="form-text text-muted">Upload a new file below to replace this attachment</small>
                                    </div>
                                    @endif

                                    <!-- File Upload -->
                                    <div class="form-group">
                                        <label>{{ $homework->file_path ? 'Replace Attachment (Optional)' : 'Attachment (Optional)' }}</label>
                                        <div class="file-upload-box">
                                            <input type="file" name="file" id="file-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="display: none;">
                                            <label for="file-input" style="cursor: pointer; margin: 0;">
                                                <i class="ti-cloud-up" style="font-size: 3rem; color: #6c757d; display: block; margin-bottom: 10px;"></i>
                                                <p style="margin: 0;"><strong>Click to upload</strong> or drag and drop</p>
                                                <p style="margin: 5px 0 0 0; font-size: 1rem; color: #6c757d;">PDF, DOC, DOCX, JPG, PNG (MAX. 10MB)</p>
                                            </label>
                                        </div>
                                        <div id="file-name" style="margin-top: 10px; color: #007bff; display: none;">
                                            <i class="ti-file"></i> <span></span>
                                        </div>
                                        @if($homework->file_path)
                                        <small class="form-text text-muted">Leave empty to keep current file</small>
                                        @endif
                                        @error('file')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <!-- Current Info -->
                            <div class="card alert sidebar-sticky">
                                <div class="card-body current-info-box">
                                    <h4 style="font-weight: 600; margin-bottom: 15px;">
                                        <i class="ti-info-alt"></i> Current Information
                                    </h4>
                                    
                                    <div style="margin-bottom: 12px;">
                                        <p style="margin: 0 0 3px 0; color: #007bff;">Class</p>
                                        <p style="margin: 0; font-weight: 600;">{{ $homework->class->name }}</p>
                                    </div>

                                    <div style="margin-bottom: 12px;">
                                        <p style="margin: 0 0 3px 0; color: #007bff;">Teacher</p>
                                        <p style="margin: 0; font-weight: 600;">{{ $homework->teacher->name }}</p>
                                    </div>

                                    <div style="margin-bottom: 12px;">
                                        <p style="margin: 0 0 3px 0; color: #007bff;">Total Students</p>
                                        <p style="margin: 0; font-weight: 600;">{{ $homework->submissions->count() }}</p>
                                    </div>

                                    <div style="margin-bottom: 12px;">
                                        <p style="margin: 0 0 3px 0; color: #007bff;">Submissions</p>
                                        <p style="margin: 0; font-weight: 600;">
                                            {{ $homework->submissions->whereIn('status', ['submitted', 'late', 'graded'])->count() }} submitted
                                        </p>
                                    </div>

                                    <div>
                                        <p style="margin: 0 0 3px 0; color: #007bff;">Created</p>
                                        <p style="margin: 0; font-weight: 600;">{{ $homework->created_at->format('d M Y') }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Guidelines -->
                            <div class="card alert" style="margin-top: 20px;">
                                <div class="card-header" style="margin-bottom: 15px;">
                                    <h4><i class="ti-help-alt"></i> Edit Guidelines</h4>
                                </div>
                                <div class="card-body">
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span>Changes will be visible to all students and parents</span>
                                    </div>
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span>Extending the due date won't affect existing submissions</span>
                                    </div>
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span>Uploading a new file will replace the existing attachment</span>
                                    </div>
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span>Changing the class is not recommended if submissions exist</span>
                                    </div>
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span>All changes are logged in the activity history</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Warning -->
                            @if($homework->submissions->whereIn('status', ['submitted', 'late', 'graded'])->count() > 0)
                            <div class="card alert" style="margin-top: 20px;">
                                <div class="card-body warning-box">
                                    <div style="display: flex; align-items: start;">
                                        <i class="ti-alert" style="font-size: 1.5rem; color: #856404; margin-right: 12px; margin-top: 2px;"></i>
                                        <div>
                                            <p style="margin: 0 0 8px 0; font-weight: 600; color: #856404;">Caution</p>
                                            <p style="margin: 0; color: #856404;">
                                                This homework has {{ $homework->submissions->whereIn('status', ['submitted', 'late', 'graded'])->count() }} submission(s). 
                                                Make changes carefully to avoid confusion.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </form>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <!-- Delete Form -->
                                        <form action="{{ route('admin.homework.destroy', $homework) }}" 
                                            method="POST" 
                                            id="deleteForm"
                                            style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    class="btn btn-danger"
                                                    onclick="confirmDeleteHomework({{ $homework->submissions->count() }})">
                                                <i class="ti-trash"></i> Delete Homework
                                            </button>
                                        </form>

                                        <div style="display: flex; gap: 10px;">
                                            <a href="{{ route('admin.homework.show', $homework) }}" class="btn btn-secondary">
                                                <i class="ti-arrow-left"></i> Cancel
                                            </a>
                                            <button type="button" class="btn btn-primary" onclick="document.getElementById('homeworkUpdateForm').submit();">
                                                <i class="ti-check"></i> Update Homework
                                            </button>
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
                                <p>MLC Classroom - Edit Homework</p>
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
        // File input change handler
        $('#file-input').on('change', function() {
            const file = this.files[0];
            if (file) {
                $('#file-name span').text(file.name);
                $('#file-name').show();
            } else {
                $('#file-name').hide();
            }
        });
    });

    // ============================================
    // DYNAMIC MAX SCORE INPUTS FOR SELECTED TOPICS
    // ============================================
    $('#topic_ids').on('change', function() {
        const selectedOptions = $(this).find(':selected');
        const container = $('#topic-max-scores-container');
        const tbody = $('#max-scores-body');

        tbody.empty();

        if (selectedOptions.length > 0) {
            container.show();
            selectedOptions.each(function() {
                const topicId = $(this).val();
                const topicName = $(this).text().trim();
                const existingValue = $(`input[name="topic_max_scores[${topicId}]"]`).val() || '';

                tbody.append(`
                    <tr>
                        <td style="vertical-align: middle; padding: 8px 12px;">
                            <strong>${topicName}</strong>
                        </td>
                        <td style="padding: 8px 12px;">
                            <input type="number"
                                name="topic_max_scores[${topicId}]"
                                class="form-control form-control-sm"
                                value="${existingValue}"
                                placeholder="e.g., 10, 20"
                                min="1"
                                max="1000"
                                style="width: 100px;">
                        </td>
                    </tr>
                `);
            });
        } else {
            container.hide();
        }
    });

    // After the change handler, set existing values for edit forms
    $(document).ready(function() {
        @if(isset($homework) && $homework->topics->count() > 0)
            const existingMaxScores = {
                @foreach($homework->topics as $topic)
                    '{{ $topic->id }}': '{{ $topic->pivot->max_score ?? '' }}',
                @endforeach
            };

            // Trigger change to build the table
            $('#topic_ids').trigger('change');

            // Then fill in existing values
            setTimeout(function() {
                Object.keys(existingMaxScores).forEach(function(topicId) {
                    $(`input[name="topic_max_scores[${topicId}]"]`).val(existingMaxScores[topicId]);
                });
            }, 100);
        @endif
    });

    // Trigger on page load for edit forms with pre-selected topics
    $(document).ready(function() {
        if ($('#topic_ids').find(':selected').length > 0) {
            $('#topic_ids').trigger('change');
        }
    });

// ==========================================
// DELETE HOMEWORK CONFIRMATION WITH SWEETALERT V1
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
            // Show loading state
            swal({
                title: "Deleting...",
                text: "Please wait while we delete the homework",
                type: "info",
                showConfirmButton: false,
                allowEscapeKey: false
            });
            
            document.getElementById('deleteForm').submit();
        }
    });
}
</script>
@endpush
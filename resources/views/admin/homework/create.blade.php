@extends('layouts.app')

@push('styles')
<style>
    .guidelines-box {
        background-color: #e7f3ff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
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

    .info-box {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .step-item {
        display: flex;
        align-items: start;
        margin-bottom: 15px;
    }

    .step-number {
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: #007bff;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 600;
        margin-right: 12px;
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

    /* .sidebar-card {
        position: sticky;
        top: 20px;
    } */
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
                                <h1>Create Homework Assignment</h1>
                            </div>
                        </div>
                        <span>Create a new homework assignment for your class</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('admin.homework.index') }}">Homework</a></li>
                                    <li class="active">Create</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.homework.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <!-- Main Content -->
                        <div class="col-lg-8">
                            <!-- Homework Details -->
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-book"></i> Homework Details</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Class Selection -->
                                    <div class="form-group">
                                        <label class="required-field">Class</label>
                                        <select name="class_id" required class="form-control">
                                            <option value="">Select a class...</option>
                                            @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
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
                                            value="{{ old('title') }}"
                                            placeholder="e.g., Chapter 5 Exercises - Quadratic Equations"
                                            required
                                            maxlength="255"
                                            class="form-control"
                                        >
                                        @error('title')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    
                                    <!-- Topics Selection -->
                                    <div class="form-group">
                                        <label for="topic_ids">
                                            <i class="ti-bookmark-alt"></i> Topics 
                                            <span class="text-muted">(Select one or more)</span>
                                        </label>
                                        <select name="topic_ids[]" id="topic_ids" class="form-control select2" multiple>
                                            <option value="">-- Select Topics --</option>
                                            @foreach($topics as $topic)
                                                <option value="{{ $topic->id }}">
                                                    {{ $topic->name }} 
                                                    @if($topic->subject)
                                                        <small>({{ $topic->subject }})</small>
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">
                                            You can select multiple topics that this homework covers.
                                        </small>
                                    </div>

                                    <!-- Description -->
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea 
                                            name="description" 
                                            rows="5"
                                            maxlength="2000"
                                            placeholder="Provide detailed instructions for students..."
                                            class="form-control"
                                        >{{ old('description') }}</textarea>
                                        <small class="form-text text-muted">Optional: Provide instructions, requirements, or expectations</small>
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
                                                    value="{{ old('assigned_date', now()->format('Y-m-d')) }}"
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
                                                    value="{{ old('due_date', now()->addDays(7)->format('Y-m-d')) }}"
                                                    required
                                                    class="form-control"
                                                >
                                                @error('due_date')
                                                <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Progress Sheet Link (Optional) -->
                                    <div class="form-group">
                                        <label>Link to Progress Sheet (Optional)</label>
                                        <select name="progress_sheet_id" class="form-control">
                                            <option value="">No progress sheet</option>
                                            @foreach($progressSheets as $sheet)
                                            <option value="{{ $sheet->id }}" {{ old('progress_sheet_id') == $sheet->id ? 'selected' : '' }}>
                                                {{ $sheet->class->name }} - {{ $sheet->topic }} ({{ $sheet->date->format('d M Y') }})
                                            </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Link this homework to a specific lesson</small>
                                        @error('progress_sheet_id')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- File Attachment -->
                                    <div class="form-group">
                                        <label>Attachment (Optional)</label>
                                        <div class="file-upload-box">
                                            <input type="file" name="file" id="file-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="display: none;">
                                            <label for="file-input" style="cursor: pointer; margin: 0;">
                                                <i class="ti-cloud-up" style="font-size: 3rem; color: #6c757d; display: block; margin-bottom: 10px;"></i>
                                                <p style="margin: 0; color: #495057;"><strong>Click to upload</strong> or drag and drop</p>
                                                <p style="margin: 5px 0 0 0; font-size: 1rem; color: #6c757d;">PDF, DOC, DOCX, JPG, PNG (MAX. 10MB)</p>
                                            </label>
                                        </div>
                                        <div id="file-name" style="margin-top: 10px; font-size: 1rem; color: #007bff; display: none;">
                                            <i class="ti-file"></i> <span></span>
                                        </div>
                                        @error('file')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <!-- Guidelines -->
                            <div class="card alert sidebar-card">
                                <div class="card-body guidelines-box">
                                    <h4 style="font-weight: 600; color: #212529; margin-bottom: 15px;">
                                        <i class="ti-info-alt"></i> Guidelines
                                    </h4>
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span style="color: #495057;">Use clear, descriptive titles that explain the assignment</span>
                                    </div>
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span style="color: #495057;">Provide detailed instructions in the description</span>
                                    </div>
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span style="color: #495057;">Give students adequate time (recommended: 3-7 days)</span>
                                    </div>
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span style="color: #495057;">Attach reference materials if needed</span>
                                    </div>
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span style="color: #495057;">Parents will be notified when homework is assigned</span>
                                    </div>
                                </div>
                            </div>

                            <!-- What Happens Next -->
                            <div class="card alert sidebar-card">
                                <div class="card-header" style="margin-bottom: 15px;">
                                    <h4><i class="ti-light-bulb"></i> What Happens Next?</h4>
                                </div>
                                <div class="card-body">
                                    <div class="step-item">
                                        <div class="step-number">1</div>
                                        <span style="color: #495057;">Homework is created for the selected class</span>
                                    </div>
                                    <div class="step-item">
                                        <div class="step-number">2</div>
                                        <span style="color: #495057;">Submission records are created for all students</span>
                                    </div>
                                    <div class="step-item">
                                        <div class="step-number">3</div>
                                        <span style="color: #495057;">Parents receive email notifications</span>
                                    </div>
                                    <div class="step-item">
                                        <div class="step-number">5</div>
                                        <span style="color: #495057;">You can grade submissions as they come in</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <a href="{{ route('admin.homework.index') }}" class="btn btn-secondary">
                                            <i class="ti-arrow-left"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti-check"></i> Create Homework Assignment
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Create Homework</p>
                            </div>
                        </div>
                    </div>
                </form>
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
</script>
@endpush
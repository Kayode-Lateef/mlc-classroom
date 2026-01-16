@extends('layouts.app')

@push('styles')
<style>
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

    .type-card {
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 8px;
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
                                <h1>Upload Learning Resource</h1>
                            </div>
                        </div>
                        <span>Upload a new learning resource for teachers and students</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('teacher.resources.index') }}">Resources</a></li>
                                    <li class="active">Upload</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MAIN FORM STARTS HERE -->
                <form action="{{ route('teacher.resources.store') }}" method="POST" enctype="multipart/form-data" id="resourceForm">
                    @csrf

                    <div class="row">
                        <!-- Main Content -->
                        <div class="col-lg-8">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-upload"></i> Resource Details</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Title -->
                                    <div class="form-group">
                                        <label class="required-field">Resource Title</label>
                                        <input 
                                            type="text" 
                                            name="title" 
                                            value="{{ old('title') }}"
                                            placeholder="e.g., Algebra Worksheet 1"
                                            required
                                            class="form-control"
                                        >
                                        @error('title')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Description -->
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea 
                                            name="description" 
                                            rows="3"
                                            placeholder="Brief description of this resource..."
                                            class="form-control"
                                        >{{ old('description') }}</textarea>
                                        @error('description')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Resource Type -->
                                    <div class="form-group">
                                        <label class="required-field">Resource Type</label>
                                        <select 
                                            name="resource_type" 
                                            id="resource_type" 
                                            required
                                            class="form-control"
                                            onchange="toggleFields()"
                                        >
                                            <option value="">-- Select type --</option>
                                            <option value="pdf" {{ old('resource_type') == 'pdf' ? 'selected' : '' }}>PDF Document</option>
                                            <option value="document" {{ old('resource_type') == 'document' ? 'selected' : '' }}>Word Document</option>
                                            <option value="image" {{ old('resource_type') == 'image' ? 'selected' : '' }}>Image</option>
                                            <option value="video" {{ old('resource_type') == 'video' ? 'selected' : '' }}>Video (YouTube/Vimeo Link)</option>
                                            <option value="link" {{ old('resource_type') == 'link' ? 'selected' : '' }}>External Link</option>
                                        </select>
                                        @error('resource_type')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- File Upload -->
                                    <div class="form-group" id="file-field" style="display: none;">
                                        <label class="required-field">Upload File</label>
                                        <div class="file-upload-box">
                                            <input type="file" name="file" id="file-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif" style="display: none;">
                                            <label for="file-input" style="cursor: pointer; margin: 0;">
                                                <i class="ti-cloud-up" style="font-size: 3rem; color: #6c757d; display: block; margin-bottom: 10px;"></i>
                                                <p style="margin: 0; font-size: 0.875rem;"><strong>Click to upload</strong> or drag and drop</p>
                                                <p style="margin: 5px 0 0 0; font-size: 0.75rem; color: #6c757d;">PDF, DOC, DOCX, JPG, PNG, GIF (MAX. 10MB)</p>
                                            </label>
                                        </div>
                                        <div id="file-name" style="margin-top: 10px; font-size: 0.875rem; color: #007bff; display: none;">
                                            <i class="ti-file"></i> <span></span>
                                        </div>
                                        <small class="form-text text-muted">Max file size: 10MB</small>
                                        @error('file')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Video URL -->
                                    <div class="form-group" id="video-field" style="display: none;">
                                        <label class="required-field">Video URL</label>
                                        <input 
                                            type="url" 
                                            name="video_url" 
                                            value="{{ old('video_url') }}"
                                            placeholder="https://www.youtube.com/watch?v=..."
                                            class="form-control"
                                        >
                                        <small class="form-text text-muted">YouTube or Vimeo URL</small>
                                        @error('video_url')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- External Link -->
                                    <div class="form-group" id="link-field" style="display: none;">
                                        <label class="required-field">External Link</label>
                                        <input 
                                            type="url" 
                                            name="external_link" 
                                            value="{{ old('external_link') }}"
                                            placeholder="https://example.com"
                                            class="form-control"
                                        >
                                        @error('external_link')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Class Assignment -->
                                    <div class="form-group">
                                        <label>Assign to Class (Optional)</label>
                                        <select name="class_id" class="form-control">
                                            <option value="">-- General Resource (All Classes) --</option>
                                            @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                                @if($class->teacher)
                                                - {{ $class->teacher->name }}
                                                @endif
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Subject -->
                                    <div class="form-group">
                                        <label>Subject</label>
                                        <input 
                                            type="text" 
                                            name="subject" 
                                            value="{{ old('subject') }}"
                                            placeholder="e.g., Mathematics, English, Science"
                                            list="subjects"
                                            class="form-control"
                                        >
                                        <datalist id="subjects">
                                            <option value="Mathematics">
                                            <option value="English">
                                            <option value="Science">
                                            <option value="History">
                                            <option value="Geography">
                                            <option value="Art">
                                            <option value="Music">
                                            <option value="Physical Education">
                                        </datalist>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <!-- Guidelines -->
                            <div class="card alert">
                                <div class="card-header mb-2">
                                    <h4><i class="ti-info-alt"></i> Upload Guidelines</h4>
                                </div>
                                <div class="card-body">
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span>Maximum file size is 10MB</span>
                                    </div>
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span>Supported formats: PDF, Word, JPG, PNG, GIF</span>
                                    </div>
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span>Assign to a specific class or make it available to all</span>
                                    </div>
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span>Use descriptive titles for easy searching</span>
                                    </div>
                                    <div class="guideline-item">
                                        <i class="ti-check"></i>
                                        <span>For videos, use YouTube or Vimeo links</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Supported Types -->
                            <div class="card alert" style="margin-top: 20px;">
                                <div class="card-header mb-2">
                                    <h4><i class="ti-files"></i> Supported Types</h4>
                                </div>
                                <div class="card-body">
                                    <div class="type-card" style="background-color: #ffe5e5;">
                                        <i class="ti-file" style="color: #dc3545; margin-right: 8px;"></i>
                                        <span style="color: #495057;">PDF Documents</span>
                                    </div>
                                    <div class="type-card" style="background-color: #fff3e0;">
                                        <i class="ti-notepad" style="color: #ff9800; margin-right: 8px;"></i>
                                        <span style="color: #495057;">Word Documents</span>
                                    </div>
                                    <div class="type-card" style="background-color: #e8f5e9;">
                                        <i class="ti-image" style="color: #28a745; margin-right: 8px;"></i>
                                        <span style="color: #495057;">Images (JPG, PNG, GIF)</span>
                                    </div>
                                    <div class="type-card" style="background-color: #f3e5f5;">
                                        <i class="ti-video-clapper" style="color: #9c27b0; margin-right: 8px;"></i>
                                        <span style="color: #495057;">Video Links</span>
                                    </div>
                                    <div class="type-card" style="background-color: #e3f2fd;">
                                        <i class="ti-link" style="color: #007bff; margin-right: 8px;"></i>
                                        <span style="color: #495057;">External Links</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- MAIN FORM ENDS HERE -->

                <!-- Form Actions (OUTSIDE the form) -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card alert">
                            <div class="card-body">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <a href="{{ route('teacher.resources.index') }}" class="btn btn-secondary">
                                        <i class="ti-arrow-left"></i> Cancel
                                    </a>
                                    <button type="button" class="btn btn-primary" onclick="document.getElementById('resourceForm').submit();">
                                        <i class="ti-upload"></i> Upload Resource
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
                            <p>MLC Classroom - Upload Resource</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function toggleFields() {
    const type = document.getElementById('resource_type').value;
    const fileInput = document.getElementById('file-input');
    
    // Hide all fields
    document.getElementById('file-field').style.display = 'none';
    document.getElementById('video-field').style.display = 'none';
    document.getElementById('link-field').style.display = 'none';
    
    // Reset file input
    fileInput.value = '';
    $('#file-name').hide();
    
    // Show relevant field and set appropriate accept attribute
    if (type === 'pdf') {
        document.getElementById('file-field').style.display = 'block';
        fileInput.setAttribute('accept', '.pdf,application/pdf');
        updateFileUploadText('PDF (MAX. 10MB)');
    } else if (type === 'document') {
        document.getElementById('file-field').style.display = 'block';
        fileInput.setAttribute('accept', '.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        updateFileUploadText('DOC, DOCX (MAX. 10MB)');
    } else if (type === 'image') {
        document.getElementById('file-field').style.display = 'block';
        fileInput.setAttribute('accept', '.jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif');
        updateFileUploadText('JPG, PNG, GIF (MAX. 10MB)');
    } else if (type === 'video') {
        document.getElementById('video-field').style.display = 'block';
    } else if (type === 'link') {
        document.getElementById('link-field').style.display = 'block';
    }
}

function updateFileUploadText(text) {
    $('.file-upload-box p:last-child').text(text);
}

$(document).ready(function() {
    // Initialize on page load
    toggleFields();
    
    // File input change handler with validation
    $('#file-input').on('change', function() {
        const file = this.files[0];
        const resourceType = $('#resource_type').val();
        
        if (file) {
            // File size validation (10MB)
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if (file.size > maxSize) {
                alert('File size exceeds 10MB. Please choose a smaller file.');
                $(this).val('');
                $('#file-name').hide();
                return;
            }
            
            // File type validation
            let validType = false;
            const fileName = file.name.toLowerCase();
            
            if (resourceType === 'pdf') {
                validType = file.type === 'application/pdf' || fileName.endsWith('.pdf');
                if (!validType) {
                    alert('Please upload a PDF file only.');
                }
            } else if (resourceType === 'document') {
                validType = file.type === 'application/msword' || 
                           file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ||
                           fileName.endsWith('.doc') || fileName.endsWith('.docx');
                if (!validType) {
                    alert('Please upload a Word document (.doc or .docx) only.');
                }
            } else if (resourceType === 'image') {
                validType = file.type.startsWith('image/') && 
                           (fileName.endsWith('.jpg') || fileName.endsWith('.jpeg') || 
                            fileName.endsWith('.png') || fileName.endsWith('.gif'));
                if (!validType) {
                    alert('Please upload an image file (JPG, PNG, or GIF) only.');
                }
            }
            
            if (!validType) {
                $(this).val('');
                $('#file-name').hide();
                return;
            }
            
            // Show file name
            $('#file-name span').text(file.name);
            $('#file-name').show();
        } else {
            $('#file-name').hide();
        }
    });
    
    // Form validation before submit
    $('#resourceForm').on('submit', function(e) {
        const resourceType = $('#resource_type').val();
        
        if (!resourceType) {
            e.preventDefault();
            alert('Please select a resource type.');
            return false;
        }
        
        // Check if file is required
        if ((resourceType === 'pdf' || resourceType === 'document' || resourceType === 'image')) {
            const fileInput = document.getElementById('file-input');
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert('Please upload a file for the selected resource type.');
                return false;
            }
        }
        
        // Check if video URL is provided
        if (resourceType === 'video') {
            const videoUrl = $('input[name="video_url"]').val();
            if (!videoUrl || videoUrl.trim() === '') {
                e.preventDefault();
                alert('Please provide a video URL.');
                $('input[name="video_url"]').focus();
                return false;
            }
        }
        
        // Check if external link is provided
        if (resourceType === 'link') {
            const externalLink = $('input[name="external_link"]').val();
            if (!externalLink || externalLink.trim() === '') {
                e.preventDefault();
                alert('Please provide an external link.');
                $('input[name="external_link"]').focus();
                return false;
            }
        }
        
        return true;
    });
});
</script>
@endpush
@extends('layouts.app')

@push('styles')
<style>
    .current-file-box {
        background-color: #e7f3ff;
        border: 1px solid #007bff;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 15px;
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

    .info-item {
        margin-bottom: 12px;
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
                                <h1>Edit Learning Resource</h1>
                            </div>
                        </div>
                        <span>Update learning resource details</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('superadmin.resources.index') }}">Resources</a></li>
                                    <li class="active">Edit</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('superadmin.resources.update', $resource) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Main Content -->
                        <div class="col-lg-8">
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-pencil-alt"></i> Resource Details</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Title -->
                                    <div class="form-group">
                                        <label class="required-field">Resource Title</label>
                                        <input 
                                            type="text" 
                                            name="title" 
                                            value="{{ old('title', $resource->title) }}"
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
                                            class="form-control"
                                        >{{ old('description', $resource->description) }}</textarea>
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
                                            <option value="pdf" {{ old('resource_type', $resource->resource_type) == 'pdf' ? 'selected' : '' }}>PDF Document</option>
                                            <option value="document" {{ old('resource_type', $resource->resource_type) == 'document' ? 'selected' : '' }}>Word Document</option>
                                            <option value="image" {{ old('resource_type', $resource->resource_type) == 'image' ? 'selected' : '' }}>Image</option>
                                            <option value="video" {{ old('resource_type', $resource->resource_type) == 'video' ? 'selected' : '' }}>Video (YouTube/Vimeo Link)</option>
                                            <option value="link" {{ old('resource_type', $resource->resource_type) == 'link' ? 'selected' : '' }}>External Link</option>
                                        </select>
                                        @error('resource_type')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Current File Info -->
                                    @if($resource->file_path)
                                    <div id="current-file-info">
                                        <div class="current-file-box">
                                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                                <div style="display: flex; align-items: center;">
                                                    <i class="ti-file" style="font-size: 1.5rem; color: #007bff; margin-right: 12px;"></i>
                                                    <div>
                                                        <p style="margin: 0; font-weight: 600; color: #007bff; font-size: 0.875rem;">Current File:</p>
                                                        <p style="margin: 0; font-size: 0.875rem; color: #495057; word-break: break-all;">
                                                            {{ filter_var($resource->file_path, FILTER_VALIDATE_URL) ? $resource->file_path : basename($resource->file_path) }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- File Upload -->
                                    <div class="form-group" id="file-field" style="display: none;">
                                        <label>Replace File (Optional)</label>
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
                                        <small class="form-text text-muted">Leave empty to keep current file. Max file size: 10MB</small>
                                        @error('file')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Video URL -->
                                    <div class="form-group" id="video-field" style="display: none;">
                                        <label>Video URL</label>
                                        <input 
                                            type="url" 
                                            name="video_url" 
                                            value="{{ old('video_url', $resource->resource_type == 'video' ? $resource->file_path : '') }}"
                                            placeholder="https://www.youtube.com/watch?v=..."
                                            class="form-control"
                                        >
                                        @error('video_url')
                                        <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- External Link -->
                                    <div class="form-group" id="link-field" style="display: none;">
                                        <label>External Link</label>
                                        <input 
                                            type="url" 
                                            name="external_link" 
                                            value="{{ old('external_link', $resource->resource_type == 'link' ? $resource->file_path : '') }}"
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
                                            <option value="{{ $class->id }}" {{ old('class_id', $resource->class_id) == $class->id ? 'selected' : '' }}>
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
                                            value="{{ old('subject', $resource->subject) }}"
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
                                        </datalist>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <!-- Current Info -->
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-info-alt"></i> Current Information</h4>
                                </div>
                                <div class="card-body">
                                    <div class="info-item">
                                        <p style="margin: 0 0 3px 0; color: #6c757d;">Resource Type</p>
                                        @php
                                            $typeBadges = [
                                                'pdf' => 'badge-danger',
                                                'video' => 'badge-purple',
                                                'link' => 'badge-primary',
                                                'image' => 'badge-success',
                                                'document' => 'badge-warning'
                                            ];
                                            $badgeClass = $typeBadges[$resource->resource_type] ?? 'badge-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}" style="padding: 4px 10px;">
                                            {{ ucfirst($resource->resource_type) }}
                                        </span>
                                    </div>

                                    <div class="info-item">
                                        <p style="margin: 0 0 3px 0; color: #6c757d;">Uploaded By</p>
                                        <p style="margin: 0; font-weight: 600;">{{ $resource->uploader->name }}</p>
                                    </div>

                                    @if($resource->class)
                                    <div class="info-item">
                                        <p style="margin: 0 0 3px 0; color: #6c757d;">Assigned Class</p>
                                        <p style="margin: 0; font-weight: 600;">{{ $resource->class->name }}</p>
                                    </div>
                                    @else
                                    <div class="info-item">
                                        <p style="margin: 0 0 3px 0; color: #6c757d;">Availability</p>
                                        <p style="margin: 0; font-weight: 600;">General Resource (All Classes)</p>
                                    </div>
                                    @endif

                                    @if($resource->subject)
                                    <div class="info-item">
                                        <p style="margin: 0 0 3px 0; color: #6c757d;">Subject</p>
                                        <p style="margin: 0; font-weight: 600;">{{ $resource->subject }}</p>
                                    </div>
                                    @endif

                                    <div class="info-item">
                                        <p style="margin: 0 0 3px 0; color: #6c757d;">Created</p>
                                        <p style="margin: 0; color: #495057;">{{ $resource->created_at->format('d M Y, H:i') }}</p>
                                    </div>

                                    <div class="info-item">
                                        <p style="margin: 0 0 3px 0; color: #6c757d;">Last Updated</p>
                                        <p style="margin: 0; color: #495057;">{{ $resource->updated_at->format('d M Y, H:i') }}</p>
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
                                        <form action="{{ route('superadmin.resources.destroy', $resource) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this resource? This action cannot be undone.');" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="ti-trash"></i> Delete Resource
                                            </button>
                                        </form>

                                        <div style="display: flex; gap: 10px;">
                                            <a href="{{ route('superadmin.resources.index') }}" class="btn btn-secondary">
                                                <i class="ti-arrow-left"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti-check"></i> Update Resource
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
                                <p>MLC Classroom - Edit Resource</p>
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
function toggleFields() {
    const type = document.getElementById('resource_type').value;
    
    // Hide all fields
    document.getElementById('file-field').style.display = 'none';
    document.getElementById('video-field').style.display = 'none';
    document.getElementById('link-field').style.display = 'none';
    
    // Show relevant field
    if (type === 'pdf' || type === 'document' || type === 'image') {
        document.getElementById('file-field').style.display = 'block';
    } else if (type === 'video') {
        document.getElementById('video-field').style.display = 'block';
    } else if (type === 'link') {
        document.getElementById('link-field').style.display = 'block';
    }
}

$(document).ready(function() {
    // Initialize on page load
    toggleFields();
    
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
@extends('layouts.app')

@push('styles')
<style>
    .resource-header {
        background-color: #fff;
        padding: 25px;
        border-radius: 8px 8px 0 0;
        /* color: white; */
    }

    .preview-box {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        background-color: #f8f9fa;
        min-height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .video-container {
        position: relative;
        padding-bottom: 56.25%;
        height: 0;
        overflow: hidden;
        border-radius: 8px;
    }

    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .info-item {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }

    .info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
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
                                <h1>Resource Details</h1>
                            </div>
                        </div>
                        <span>View learning resource details and content</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('admin.resources.index') }}">Resources</a></li>
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
                            <!-- Resource Info -->
                            <div class="card alert">
                                @php
                                    $typeColors = [
                                        'pdf' => ['class' => 'badge-danger', 'icon' => 'ti-file'],
                                        'video' => ['class' => 'badge-purple', 'icon' => 'ti-video-clapper'],
                                        'link' => ['class' => 'badge-primary', 'icon' => 'ti-link'],
                                        'image' => ['class' => 'badge-success', 'icon' => 'ti-image'],
                                        'document' => ['class' => 'badge-warning', 'icon' => 'ti-notepad']
                                    ];
                                    $typeInfo = $typeColors[$resource->resource_type] ?? ['class' => 'badge-secondary', 'icon' => 'ti-file'];
                                @endphp

                                <div class="resource-header">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div style="flex: 1;">
                                            <h2 style="font-weight: 700; margin-bottom: 10px;">
                                                {{ $resource->title }}
                                            </h2>
                                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                                <span class="badge {{ $typeInfo['class'] }}" style="padding: 6px 12px;">
                                                    <i class="{{ $typeInfo['icon'] }}"></i> {{ strtoupper($resource->resource_type) }}
                                                </span>
                                                @if($resource->class)
                                                <span class="badge badge-light" style="padding: 6px 12px;">
                                                    {{ $resource->class->name }}
                                                </span>
                                                @endif
                                                @if($resource->subject)
                                                <span class="badge badge-light" style="padding: 6px 12px;">
                                                    {{ $resource->subject }}
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.resources.edit', $resource) }}" class="btn btn-primary btn-sm">
                                            <i class="ti-pencil"></i> Edit
                                        </a>
                                    </div>
                                </div>

                                <div class="card-body">
                                    @if($resource->description)
                                    <div style="margin-bottom: 20px;">
                                        <h4 style="font-weight: 600; margin-bottom: 10px;">
                                            <i class="ti-align-left"></i> Description
                                        </h4>
                                        <p style="margin: 0; color: #495057; line-height: 1.6;">{{ $resource->description }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Resource Content -->
                            <div class="card alert">
                                <div class="card-header mb-2">
                                    <h4><i class="ti-eye"></i> Resource Content</h4>
                                </div>
                                <div class="card-body">
                                    @if($resource->resource_type === 'image' && !filter_var($resource->file_path, FILTER_VALIDATE_URL))
                                    <!-- Image Preview -->
                                    <div style="border-radius: 8px; overflow: hidden;">
                                        <img src="{{ asset('storage/' . $resource->file_path) }}" alt="{{ $resource->title }}" style="width: 100%; height: auto;">
                                    </div>

                                    @elseif($resource->resource_type === 'video')
                                    <!-- Video Embed -->
                                    @php
                                        $videoUrl = $resource->file_path;
                                        $embedUrl = null;
                                        
                                        if (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false) {
                                            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\?\/]+)/', $videoUrl, $matches);
                                            if (isset($matches[1])) {
                                                $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                                            }
                                        } elseif (strpos($videoUrl, 'vimeo.com') !== false) {
                                            preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches);
                                            if (isset($matches[1])) {
                                                $embedUrl = 'https://player.vimeo.com/video/' . $matches[1];
                                            }
                                        }
                                    @endphp
                                    
                                    @if($embedUrl)
                                    <div class="video-container">
                                        <iframe src="{{ $embedUrl }}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                                    </div>
                                    @else
                                    <div class="preview-box">
                                        <div>
                                            <i class="ti-video-clapper" style="font-size: 4rem; color: #6c757d; margin-bottom: 20px;"></i>
                                            <p style="margin-bottom: 20px; color: #495057;">Video resource</p>
                                            <a href="{{ $videoUrl }}" target="_blank" class="btn btn-primary">
                                                <i class="ti-new-window"></i> Open Video Link
                                            </a>
                                        </div>
                                    </div>
                                    @endif

                                    @elseif($resource->resource_type === 'link')
                                    <!-- External Link -->
                                    <div class="preview-box">
                                        <div>
                                            <i class="ti-link" style="font-size: 4rem; color: #007bff; margin-bottom: 20px;"></i>
                                            <p style="margin-bottom: 20px; color: #495057;">This resource links to an external website</p>
                                            <a href="{{ $resource->file_path }}" target="_blank" class="btn btn-primary">
                                                <i class="ti-new-window"></i> Open External Link
                                            </a>
                                            <p style="margin-top: 15px; font-size: 0.875rem; color: #6c757d; word-break: break-all;">
                                                {{ $resource->file_path }}
                                            </p>
                                        </div>
                                    </div>

                                    @else
                                    <!-- File Download -->
                                    <div class="preview-box">
                                        <div>
                                            <i class="{{ $typeInfo['icon'] }}" style="font-size: 4rem; color: #6c757d; margin-bottom: 20px;"></i>
                                            <p style="margin-bottom: 20px; color: #495057; font-size: 1rem;">
                                                {{ ucfirst($resource->resource_type) }} file available for download
                                            </p>
                                            <a href="{{ route('admin.resources.download', $resource) }}" class="btn btn-success">
                                                <i class="ti-download"></i> Download File
                                            </a>
                                            @if(!filter_var($resource->file_path, FILTER_VALIDATE_URL))
                                            <p style="margin-top: 15px; font-size: 0.875rem; color: #6c757d;">
                                                {{ basename($resource->file_path) }}
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <!-- Quick Actions -->
                            <div class="card alert">
                                <div class="card-header">
                                    <h4><i class="ti-settings"></i> Actions</h4>
                                </div>
                                <div class="card-body">
                                    <a href="{{ route('admin.resources.edit', $resource) }}" class="btn btn-primary btn-block mb-2">
                                        <i class="ti-pencil-alt"></i> Edit Resource
                                    </a>
                                    
                                    @if($resource->resource_type !== 'video' && $resource->resource_type !== 'link')
                                    <a href="{{ route('admin.resources.download', $resource) }}" class="btn btn-success btn-block mb-2">
                                        <i class="ti-download"></i> Download
                                    </a>
                                    @endif
                                    
                                    <form action="{{ route('admin.resources.destroy', $resource) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this resource?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-block">
                                            <i class="ti-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Resource Information -->
                            <div class="card alert" style="margin-top: 20px;">
                                <div class="card-header">
                                    <h4><i class="ti-info-alt"></i> Information</h4>
                                </div>
                                <div class="card-body">
                                    <div class="info-item">
                                        <p style="margin: 0 0 5px 0; color: #6c757d; font-weight: 500;">Uploaded By</p>
                                        <div style="display: flex; align-items: center;">
                                            @if($resource->uploader->profile_photo)
                                                <img src="{{ asset('storage/' . $resource->uploader->profile_photo) }}" alt="{{ $resource->uploader->name }}" class="user-avatar" style="margin-right: 12px;">
                                            @else
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background-color: #007bff; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                                                <span style="color: white; font-weight: 600;">
                                                    {{ strtoupper(substr($resource->uploader->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            @endif
                                            <div>
                                                <p style="margin: 0; font-weight: 600; color: #212529;">{{ $resource->uploader->name }}</p>
                                                <p style="margin: 0; color: #6c757d;">{{ ucfirst($resource->uploader->role) }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    @if($resource->class)
                                    <div class="info-item">
                                        <p style="margin: 0 0 5px 0; color: #6c757d; font-weight: 500;">Assigned Class</p>
                                        <p style="margin: 0; font-weight: 600; color: #212529;">{{ $resource->class->name }}</p>
                                        @if($resource->class->teacher)
                                        <p style="margin: 0; color: #6c757d;">Teacher: {{ $resource->class->teacher->name }}</p>
                                        @endif
                                    </div>
                                    @else
                                    <div class="info-item">
                                        <p style="margin: 0 0 5px 0; color: #6c757d; font-weight: 500;">Availability</p>
                                        <span class="badge badge-success" style="padding: 4px 10px;">General Resource</span>
                                        <p style="margin: 5px 0 0 0; color: #6c757d;">Available to all classes</p>
                                    </div>
                                    @endif

                                    @if($resource->subject)
                                    <div class="info-item">
                                        <p style="margin: 0 0 5px 0; color: #6c757d; font-weight: 500;">Subject</p>
                                        <span class="badge badge-secondary" style="padding: 4px 10px;">{{ $resource->subject }}</span>
                                    </div>
                                    @endif

                                    <div class="info-item">
                                        <p style="margin: 0 0 5px 0; color: #6c757d; font-weight: 500;">Created</p>
                                        <p style="margin: 0; color: #212529;">{{ $resource->created_at->format('d M Y') }}</p>
                                        <p style="margin: 0; color: #6c757d;">{{ $resource->created_at->format('H:i') }}</p>
                                    </div>

                                    <div class="info-item">
                                        <p style="margin: 0 0 5px 0; color: #6c757d; font-weight: 500;">Last Updated</p>
                                        <p style="margin: 0; color: #212529;">{{ $resource->updated_at->format('d M Y') }}</p>
                                        <p style="margin: 0; color: #6c757d;">{{ $resource->updated_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Resource Details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
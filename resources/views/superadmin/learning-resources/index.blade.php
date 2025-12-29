@extends('layouts.app')

@push('styles')
<style>
    .resource-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
        background-color: #f8f9fa;

    }

    .resource-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .resource-preview {
        height: 180px;
        /* background-color: #fff; */
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .resource-type-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 4px 12px;
        border-radius: 12px;
        font-weight: 600;
    }

    .filter-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .empty-state i {
        color: #cbd5e0;
        margin-bottom: 20px;
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
                                <h1>Learning Resources</h1>
                            </div>
                        </div>
                        <span>Manage and organize learning resources for teachers and students</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Resources</li>
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
                                <div class="alert alert-success fade in alert-dismissable">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class="ti-check"></i> {{ session('success') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="alert alert-danger fade in alert-dismissable">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class="ti-alert"></i> {{ session('error') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Create Button -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-right mb-3">
                                <a href="{{ route('superadmin.resources.create') }}" class="btn btn-primary">
                                    <i class="ti-plus"></i> Upload Resource
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-folder color-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Resources</div>
                                        <div class="stat-digit">{{ number_format($stats['total_resources']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-file color-danger border-danger"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">PDF Documents</div>
                                        <div class="stat-digit">{{ number_format($stats['pdf_count']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-video-clapper color-purple border-purple"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Videos</div>
                                        <div class="stat-digit">{{ number_format($stats['video_count']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="ti-world color-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">General Resources</div>
                                        <div class="stat-digit">{{ number_format($stats['general_resources']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="filter-card">
                                <form method="GET" action="{{ route('superadmin.resources.index') }}">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Search</label>
                                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Title..." class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Type</label>
                                                <select name="resource_type" class="form-control">
                                                    <option value="">All Types</option>
                                                    <option value="pdf" {{ request('resource_type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                                    <option value="video" {{ request('resource_type') == 'video' ? 'selected' : '' }}>Video</option>
                                                    <option value="link" {{ request('resource_type') == 'link' ? 'selected' : '' }}>Link</option>
                                                    <option value="image" {{ request('resource_type') == 'image' ? 'selected' : '' }}>Image</option>
                                                    <option value="document" {{ request('resource_type') == 'document' ? 'selected' : '' }}>Document</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Subject</label>
                                                <select name="subject" class="form-control">
                                                    <option value="">All Subjects</option>
                                                    @foreach($subjects as $subject)
                                                    <option value="{{ $subject }}" {{ request('subject') == $subject ? 'selected' : '' }}>
                                                        {{ $subject }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Class</label>
                                                <select name="class_id" class="form-control">
                                                    <option value="">All Classes</option>
                                                    @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                        {{ $class->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label style="font-weight: 500;">Uploaded By</label>
                                                <select name="uploaded_by" class="form-control">
                                                    <option value="">All Teachers</option>
                                                    @foreach($teachers as $teacher)
                                                    <option value="{{ $teacher->id }}" {{ request('uploaded_by') == $teacher->id ? 'selected' : '' }}>
                                                        {{ $teacher->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <a href="{{ route('superadmin.resources.index') }}" class="btn btn-secondary btn-sm">
                                            <i class="ti-reload"></i> Clear
                                        </a>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="ti-filter"></i> Apply Filters
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Resources Grid -->
                    @if($resources->count() > 0)
                    <div class="row">
                        @foreach($resources as $resource)
                        <div class="col-lg-4 mb-4">
                            <div class="resource-card">
                                @php
                                    $bgColors = [
                                        'pdf' => '#ffe5e5;',
                                        'video' => '#f3e5f5;',
                                        'link' => '#e3f2fd;',
                                        'image' => '#e8f5e9;',
                                        'document' => '#fff3e0;'
                                    ];
                                    $bgColor = $bgColors[$resource->resource_type] ?? '#6c757d';
                                @endphp

                                <!-- Preview -->
                                <div class="resource-preview" style="background-color: {{ $bgColor }};">
                                    @php
                                        $typeColors = [
                                            'pdf' => ['bg' => 'badge-danger', 'icon' => 'ti-file'],
                                            'video' => ['bg' => 'badge-purple', 'icon' => 'ti-video-clapper'],
                                            'link' => ['bg' => 'badge-primary', 'icon' => 'ti-link'],
                                            'image' => ['bg' => 'badge-success', 'icon' => 'ti-image'],
                                            'document' => ['bg' => 'badge-warning', 'icon' => 'ti-notepad']
                                        ];
                                        $typeInfo = $typeColors[$resource->resource_type] ?? ['bg' => 'badge-secondary', 'icon' => 'ti-file'];
                                    @endphp
                                    <span class="badge {{ $typeInfo['bg'] }} resource-type-badge">
                                        <i class="{{ $typeInfo['icon'] }}"></i> {{ strtoupper($resource->resource_type) }}
                                    </span>

                                    @if($resource->resource_type === 'image' && !filter_var($resource->file_path, FILTER_VALIDATE_URL))
                                    <img src="{{ asset('storage/' . $resource->file_path) }}" alt="{{ $resource->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                    @php
                                        $iconColors = [
                                            'pdf' => '#dc3545;',
                                            'video' => '#9c27b0;',
                                            'link' => '#007bff;',
                                            'image' => '#28a745;',
                                            'document' => '#ff9800;'
                                        ];
                                        $iconColor = $iconColors[$resource->resource_type] ?? '#6c757d';
                                    @endphp

                                    <i class="{{ $typeInfo['icon'] }}" style="font-size: 4rem; color: {{ $iconColor }};"></i>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="" style="padding: 15px;">
                                    <h5 style="margin: 0 0 10px 0; font-weight: 600; color: #212529;">
                                        {{ Str::limit($resource->title, 50) }}
                                    </h5>

                                    @if($resource->description)
                                    <p style="margin: 0 0 12px 0; color: #6c757d; line-height: 1.5;">
                                        {{ Str::limit($resource->description, 80) }}
                                    </p>
                                    @endif

                                    <!-- Meta -->
                                    <div style="display: flex; justify-content: space-between; color: #6c757d; margin-bottom: 12px;">
                                        <div>
                                            <i class="ti-user"></i> {{ $resource->uploader->name }}
                                        </div>
                                        <div>
                                            <i class="ti-calendar"></i> {{ $resource->created_at->format('d M Y') }}
                                        </div>
                                    </div>

                                    <!-- Tags -->
                                    <div style="display: flex; gap: 6px; margin-bottom: 15px; flex-wrap: wrap;">
                                        @if($resource->class)
                                        <span class="badge badge-primary">{{ $resource->class->name }}</span>
                                        @endif
                                        @if($resource->subject)
                                        <span class="badge badge-secondary">{{ $resource->subject }}</span>
                                        @endif
                                        @if(!$resource->class_id)
                                        <span class="badge badge-success">General</span>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div style="display: flex; gap: 8px; padding-top: 12px; border-top: 1px solid #e9ecef;">
                                        <a href="{{ route('superadmin.resources.show', $resource) }}" class="btn btn-primary btn-sm" style="flex: 1;">
                                            <i class="ti-eye"></i> View
                                        </a>
                                        <a href="{{ route('superadmin.resources.edit', $resource) }}" class="btn btn-success btn-sm" style="flex: 1;">
                                            <i class="ti-pencil"></i> Edit
                                        </a>
                                        @if($resource->resource_type !== 'video' && $resource->resource_type !== 'link')
                                        <a href="{{ route('superadmin.resources.download', $resource) }}" class="btn btn-pink btn-sm">
                                            <i class="ti-download"></i>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($resources->hasPages())
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mt-4">
                                {{ $resources->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                    @endif
                    @else
                    <!-- Empty State -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="empty-state">
                                <i class="ti-folder"></i>
                                <h3 class="mb-3">No Learning Resources Found</h3>
                                <p class="text-muted mb-4">Get started by uploading your first resource.</p>
                                <a href="{{ route('superadmin.resources.create') }}" class="btn btn-primary">
                                    <i class="ti-plus"></i> Upload Resource
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Learning Resources</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
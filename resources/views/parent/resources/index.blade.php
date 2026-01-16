@extends('layouts.app')

@section('title', 'Learning Resources')

@push('styles')
<style>
    .resource-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
        background-color: #fff;
        height: 100%;
    }

    .resource-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .resource-preview {
        height: 180px;
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

    .child-selector {
        background: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 2px solid #e9ecef;
    }

    .child-option {
        padding: 10px 15px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
    }

    .child-option:hover {
        background: #f8f9fa;
    }

    .child-option.active {
        background: #e7f3ff;
        border-color: #007bff;
    }

    .student-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        object-fit: cover;
    }

    .student-initial {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.875rem;
        background-color: #007bff;
        color: white;
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
                        <span>Access study materials and resources for your children's classes</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                                    <li class="active">Resources</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    @if($children->isEmpty())
                        <!-- No Children State -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card alert">
                                    <div class="card-body text-center py-5">
                                        <i class="ti-user" style="font-size: 4rem; color: #cbd5e0;"></i>
                                        <h3 class="mt-3 mb-2">No Children Found</h3>
                                        <p class="text-muted mb-4">You don't have any children registered in the system.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Child Selector -->
                        @if($children->count() > 1)
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="child-selector">
                                    <h5 class="mb-3">Select Child</h5>
                                    <div class="row">
                                        @foreach($children as $child)
                                        <div class="col-md-3">
                                            <a href="{{ route('parent.resources.index', ['child_id' => $child->id]) }}" 
                                               class="child-option {{ $selectedChild && $selectedChild->id == $child->id ? 'active' : '' }}"
                                               style="display: block; text-decoration: none; color: inherit;">
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    @if($child->profile_photo)
                                                        <img src="{{ asset('storage/' . $child->profile_photo) }}" 
                                                             alt="{{ $child->full_name }}" 
                                                             class="student-avatar">
                                                    @else
                                                        <div class="student-initial">
                                                            {{ strtoupper(substr($child->first_name, 0, 1)) }}{{ strtoupper(substr($child->last_name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $child->full_name }}</strong>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($selectedChild && $stats)
                            <!-- Statistics Cards -->
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="card">
                                        <div class="stat-widget-one">
                                            <div class="stat-icon dib"><i class="ti-folder color-primary border-primary"></i></div>
                                            <div class="stat-content dib">
                                                <div class="stat-text">Total Resources</div>
                                                <div class="stat-digit">{{ $stats['total'] }}</div>
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
                                                <div class="stat-digit">{{ $stats['pdf'] }}</div>
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
                                                <div class="stat-digit">{{ $stats['video'] }}</div>
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
                                                <div class="stat-digit">{{ $stats['general'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- By Class & Subject Stats -->
                            <div class="row">
                                @if(count($stats['by_class']) > 0)
                                <div class="col-lg-6">
                                    <div class="card alert">
                                        <div class="card-header">
                                            <h4><i class="ti-book"></i> Resources by Class</h4>
                                        </div>
                                        <div class="card-body">
                                            @foreach($stats['by_class'] as $classStat)
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #e9ecef;">
                                                <div>
                                                    <strong>{{ $classStat['class']->name }}</strong><br>
                                                    <small class="text-muted">{{ $classStat['class']->teacher->name ?? 'N/A' }}</small>
                                                </div>
                                                <span class="badge badge-primary">{{ $classStat['count'] }} resources</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if(count($stats['by_subject']) > 0)
                                <div class="col-lg-6">
                                    <div class="card alert">
                                        <div class="card-header">
                                            <h4><i class="ti-bookmark"></i> Resources by Subject</h4>
                                        </div>
                                        <div class="card-body">
                                            @foreach(array_slice($stats['by_subject'], 0, 5) as $subjectStat)
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #e9ecef;">
                                                <strong>{{ $subjectStat['subject'] }}</strong>
                                                <span class="badge badge-info">{{ $subjectStat['count'] }} resources</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Filters -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="filter-card">
                                        <form method="GET" action="{{ route('parent.resources.index') }}">
                                            <input type="hidden" name="child_id" value="{{ $selectedChild->id }}">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label style="font-weight: 500;">Search</label>
                                                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search resources..." class="form-control">
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
                                                            @foreach($availableSubjects as $subject)
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
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <div style="display: flex; gap: 5px;">
                                                            <button type="submit" class="btn btn-primary flex-fill">
                                                                <i class="ti-filter"></i>
                                                            </button>
                                                            <a href="{{ route('parent.resources.index', ['child_id' => $selectedChild->id]) }}" class="btn btn-secondary">
                                                                <i class="ti-reload"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
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
                                                'pdf' => '#ffe5e5',
                                                'video' => '#f3e5f5',
                                                'link' => '#e3f2fd',
                                                'image' => '#e8f5e9',
                                                'document' => '#fff3e0'
                                            ];
                                            $bgColor = $bgColors[$resource->resource_type] ?? '#f8f9fa';
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
                                                    'pdf' => '#dc3545',
                                                    'video' => '#9c27b0',
                                                    'link' => '#007bff',
                                                    'image' => '#28a745',
                                                    'document' => '#ff9800'
                                                ];
                                                $iconColor = $iconColors[$resource->resource_type] ?? '#6c757d';
                                            @endphp
                                            <i class="{{ $typeInfo['icon'] }}" style="font-size: 4rem; color: {{ $iconColor }};"></i>
                                            @endif
                                        </div>

                                        <!-- Content -->
                                        <div style="padding: 15px;">
                                            <h5 style="margin: 0 0 10px 0; font-weight: 600; color: #212529;">
                                                {{ Str::limit($resource->title, 50) }}
                                            </h5>

                                            @if($resource->description)
                                            <p style="margin: 0 0 12px 0; color: #6c757d; line-height: 1.5; font-size: 0.875rem;">
                                                {{ Str::limit($resource->description, 80) }}
                                            </p>
                                            @endif

                                            <!-- Meta -->
                                            <div style="display: flex; justify-content: space-between; color: #6c757d; margin-bottom: 12px; font-size: 0.875rem;">
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
                                                @else
                                                <span class="badge badge-success">General</span>
                                                @endif
                                                @if($resource->subject)
                                                <span class="badge badge-secondary">{{ $resource->subject }}</span>
                                                @endif
                                            </div>

                                            <!-- Actions -->
                                            <div style="display: flex; gap: 8px; padding-top: 12px; border-top: 1px solid #e9ecef;">
                                                <a href="{{ route('parent.resources.show', $resource) }}" class="btn btn-primary btn-sm" style="flex: 1;">
                                                    <i class="ti-eye"></i> View
                                                </a>
                                                @if($resource->resource_type !== 'video' && $resource->resource_type !== 'link')
                                                <a href="{{ route('parent.resources.download', $resource) }}" class="btn btn-success btn-sm">
                                                    <i class="ti-download"></i>
                                                </a>
                                                @else
                                                <a href="{{ route('parent.resources.download', $resource) }}" target="_blank" class="btn btn-info btn-sm">
                                                    <i class="ti-link"></i>
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
                                        <i class="ti-folder" style="font-size: 4rem;"></i>
                                        <h3 class="mb-3">No Learning Resources Found</h3>
                                        <p class="text-muted mb-4">No resources are available for {{ $selectedChild->full_name }}'s classes yet.</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endif
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
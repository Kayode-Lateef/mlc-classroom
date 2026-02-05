@extends('layouts.app')

@section('title', 'Edit Homework Topic')

@push('styles')
    <style>
        .page-header {
            margin-bottom: 30px;
        }

        .form-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .form-card .card-header {
            background: linear-gradient(135deg, #3386f7 0%, #e06829 100%);
            color: white;
            padding: 20px;
            border: none;
        }

        .form-card .card-header h2 {
            margin: 0;
            color: white;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .form-card .card-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            font-weight: 600;
            font-size: 0.9375rem;
            color: #495057;
            margin-bottom: 8px;
            display: block;
        }

        .form-group label .required {
            color: #dc3545;
            margin-left: 3px;
        }

        .form-group label i {
            color: #3386f7;
            margin-right: 5px;
        }

        .form-control {
            font-size: 0.9375rem;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
        }

        .form-control:focus {
            border-color: #3386f7;
            box-shadow: 0 0 0 0.2rem rgba(51, 134, 247, 0.25);
        }

        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 8px;
        }

        .custom-control-label {
            font-size: 0.9375rem;
            color: #495057;
            cursor: pointer;
        }

        .custom-switch .custom-control-label::before {
            background-color: #6c757d;
        }

        .custom-switch .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #28a745;
        }

        .btn-group-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }

        .btn {
            padding: 12px 25px;
            font-size: 0.9375rem;
            font-weight: 500;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #3386f7;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        .info-box h4 {
            color: #2c3e50;
            font-size: 1.125rem;
            margin-bottom: 15px;
        }

        .info-box h4 i {
            color: #3386f7;
            margin-right: 8px;
        }

        .info-box .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(51, 134, 247, 0.1);
        }

        .info-box .info-item:last-child {
            border-bottom: none;
        }

        .info-box .info-item .label {
            color: #6c757d;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .info-box .info-item .value {
            color: #2c3e50;
            font-size: 0.9375rem;
            font-weight: 500;
        }

        .usage-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        .usage-warning i {
            color: #856404;
            font-size: 1.25rem;
            margin-right: 10px;
        }

        .usage-warning p {
            margin: 0;
            color: #856404;
            font-size: 0.9375rem;
        }

        .danger-zone {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 20px;
            border-radius: 6px;
            margin-top: 25px;
        }

        .danger-zone h4 {
            color: #721c24;
            font-size: 1.125rem;
            margin-bottom: 15px;
        }

        .danger-zone h4 i {
            margin-right: 8px;
        }

        .danger-zone p {
            color: #721c24;
            margin-bottom: 15px;
            font-size: 0.9375rem;
        }

        .danger-zone .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        @media (max-width: 768px) {
            .btn-group-actions {
                flex-direction: column;
            }

            .btn-group-actions .btn {
                width: 100%;
            }
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
                                <h1><i class="ti-pencil"></i> Edit Homework Topic</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('superadmin.homework-topics.index') }}">Topics</a></li>
                                    <li class="active">Edit</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="ti-alert"></i> Validation Error!</strong>
                        <ul style="margin-bottom: 0; margin-top: 10px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="row">
                    <!-- Main Form -->
                    <div class="col-lg-8">
                        <div class="form-card card">
                            <div class="card-header">
                                <h2><i class="ti-bookmark-alt"></i> Topic Information</h2>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('superadmin.homework-topics.update', $homeworkTopic->id) }}" id="topic-form">
                                    @csrf
                                    @method('PUT')

                                    <!-- Topic Name -->
                                    <div class="form-group">
                                        <label for="name">
                                            <i class="ti-bookmark-alt"></i> Topic Name<span class="required">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               placeholder="e.g., Algebra, Reading Comprehension, Forces & Motion"
                                               value="{{ old('name', $homeworkTopic->name) }}"
                                               required
                                               maxlength="255">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text">
                                            Enter a clear, descriptive name for the topic (max 255 characters)
                                        </small>
                                    </div>

                                    <!-- Subject -->
                                    <div class="form-group">
                                        <label for="subject">
                                            <i class="ti-book"></i> Subject
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('subject') is-invalid @enderror" 
                                               id="subject" 
                                               name="subject" 
                                               placeholder="e.g., Mathematics, English, Science"
                                               value="{{ old('subject', $homeworkTopic->subject) }}"
                                               maxlength="100"
                                               list="subject-suggestions">
                                        <datalist id="subject-suggestions">
                                            <option value="Mathematics">
                                            <option value="English">
                                            <option value="Science">
                                            <option value="History">
                                            <option value="Geography">
                                            <option value="ICT">
                                            <option value="Art">
                                            <option value="Music">
                                            <option value="Physical Education">
                                        </datalist>
                                        @error('subject')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text">
                                            Optional: Specify which subject this topic belongs to
                                        </small>
                                    </div>

                                    <!-- Description -->
                                    <div class="form-group">
                                        <label for="description">
                                            <i class="ti-align-left"></i> Description
                                        </label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" 
                                                  name="description" 
                                                  rows="4"
                                                  placeholder="Provide a brief description of what this topic covers..."
                                                  maxlength="1000">{{ old('description', $homeworkTopic->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text">
                                            Optional: Add more context about the topic (max 1000 characters)
                                        </small>
                                    </div>

                                    <!-- Status -->
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" 
                                                   class="custom-control-input" 
                                                   id="is_active" 
                                                   name="is_active"
                                                   {{ old('is_active', $homeworkTopic->is_active) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="is_active">
                                                <strong>Active Topic</strong>
                                            </label>
                                        </div>
                                        <small class="form-text">
                                            Only active topics will be available for selection when creating homework
                                        </small>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="btn-group-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti-save"></i> Update Topic
                                        </button>
                                        <a href="{{ route('superadmin.homework-topics.index') }}" class="btn btn-secondary">
                                            <i class="ti-close"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Danger Zone -->
                        @if($homeworkTopic->homeworkAssignments->count() == 0)
                            <div class="danger-zone">
                                <h4><i class="ti-trash"></i> Danger Zone</h4>
                                <p>
                                    Once you delete this topic, there is no going back. Please be certain.
                                </p>
                                <form action="{{ route('superadmin.homework-topics.destroy', $homeworkTopic->id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you absolutely sure? This action cannot be undone and will permanently delete this topic.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="ti-trash"></i> Delete This Topic
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="danger-zone">
                                <h4><i class="ti-lock"></i> Cannot Delete</h4>
                                <p>
                                    This topic cannot be deleted because it is currently assigned to 
                                    <strong>{{ $homeworkTopic->homeworkAssignments->count() }}</strong> homework assignment(s).
                                </p>
                                <p style="margin-bottom: 0;">
                                    To delete this topic, first remove it from all homework assignments or mark it as inactive instead.
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Topic Info -->
                        <div class="info-box">
                            <h4><i class="ti-info-alt"></i> Topic Details</h4>
                            
                            <div class="info-item">
                                <span class="label">Created</span>
                                <span class="value">{{ $homeworkTopic->created_at->format('d/m/Y') }}</span>
                            </div>

                            <div class="info-item">
                                <span class="label">Last Updated</span>
                                <span class="value">{{ $homeworkTopic->updated_at->format('d/m/Y H:i') }}</span>
                            </div>

                            <div class="info-item">
                                <span class="label">Status</span>
                                <span class="value">
                                    @if($homeworkTopic->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </span>
                            </div>

                            <div class="info-item">
                                <span class="label">Used in Homework</span>
                                <span class="value">
                                    <span class="badge badge-info">
                                        {{ $homeworkTopic->homeworkAssignments->count() }} assignments
                                    </span>
                                </span>
                            </div>
                        </div>

                        <!-- Usage Warning -->
                        @if($homeworkTopic->homeworkAssignments->count() > 0)
                            <div class="usage-warning">
                                <div style="display: flex; align-items: flex-start;">
                                    <i class="ti-alert"></i>
                                    <div>
                                        <p>
                                            <strong>This topic is in use!</strong><br>
                                            Changes to this topic will affect {{ $homeworkTopic->homeworkAssignments->count() }} homework assignment(s).
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Character counter for name
    $('#name').on('input', function() {
        const maxLength = 255;
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;
        
        let helpText = $(this).siblings('.form-text');
        if (remaining < 50) {
            helpText.html(`${remaining} characters remaining`);
            helpText.css('color', remaining < 20 ? '#dc3545' : '#e06829');
        } else {
            helpText.html('Enter a clear, descriptive name for the topic (max 255 characters)');
            helpText.css('color', '#6c757d');
        }
    });

    // Character counter for description
    $('#description').on('input', function() {
        const maxLength = 1000;
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;
        
        let helpText = $(this).siblings('.form-text');
        if (remaining < 100) {
            helpText.html(`${remaining} characters remaining`);
            helpText.css('color', remaining < 50 ? '#dc3545' : '#e06829');
        } else {
            helpText.html('Optional: Add more context about the topic (max 1000 characters)');
            helpText.css('color', '#6c757d');
        }
    });

    // Form validation
    $('#topic-form').on('submit', function(e) {
        const name = $('#name').val().trim();
        
        if (!name) {
            e.preventDefault();
            alert('Please enter a topic name.');
            $('#name').focus();
            return false;
        }

        // Confirm submission
        return confirm('Update this homework topic?');
    });

    // Warn about deactivating topic with assignments
    $('#is_active').on('change', function() {
        const isActive = $(this).is(':checked');
        const assignmentCount = {{ $homeworkTopic->homeworkAssignments->count() }};
        
        if (!isActive && assignmentCount > 0) {
            if (!confirm(`This topic is used in ${assignmentCount} homework assignment(s). Deactivating it will hide it from new homework creation, but existing assignments will not be affected. Continue?`)) {
                $(this).prop('checked', true);
            }
        }
    });
});
</script>
@endpush
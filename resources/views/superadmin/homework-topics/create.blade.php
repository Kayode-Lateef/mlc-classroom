@extends('layouts.app')

@section('title', 'Create Homework Topic')

@push('styles')
    <style>

        .form-group label .required {
            color: #dc3545;
            margin-left: 3px;
        }

        .form-group label i {
            color: #3386f7;
            margin-right: 5px;
        }

        .form-control:focus {
            border-color: #3386f7;
            box-shadow: 0 0 0 0.2rem rgba(51, 134, 247, 0.25);
        }


        .custom-control-label {
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

        .guidelines-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        .guidelines-box h4 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .guidelines-box h4 i {
            color: #3386f7;
            margin-right: 8px;
        }

        .guidelines-box ul {
            margin: 0;
            padding-left: 20px;
        }

        .guidelines-box ul li {
            color: #495057;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .examples-box {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 6px;
        }

        .examples-box h4 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .examples-box h4 i {
            color: #e06829;
            margin-right: 8px;
        }

        .examples-box .example-item {
            background: white;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .examples-box .example-item strong {
            color: #3386f7;
            display: block;
            margin-bottom: 5px;
        }

        .examples-box .example-item span {
            color: #6c757d;
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
                                <h1><i class="ti-plus"></i> Create Homework Topic</h1>
                            </div>
                        </div>
                        <span>Add a new homework topic to the system</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('superadmin.homework-topics.index') }}">Topics</a></li>
                                    <li class="active">Create</li>
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
                        <div class="card">
                            <div class="card-header mb-4">
                                <h4><i class="ti-bookmark-alt"></i> Topic Information</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('superadmin.homework-topics.store') }}" id="topic-form">
                                    @csrf

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
                                               value="{{ old('name') }}"
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
                                               value="{{ old('subject') }}"
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
                                                  maxlength="1000">{{ old('description') }}</textarea>
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
                                                   {{ old('is_active', true) ? 'checked' : '' }}>
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
                                            <i class="ti-save"></i> Create Topic
                                        </button>
                                        <a href="{{ route('superadmin.homework-topics.index') }}" class="btn btn-secondary">
                                            <i class="ti-close"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Guidelines -->
                        <div class="guidelines-box">
                            <h4><i class="ti-light-bulb"></i> Guidelines</h4>
                            <ul>
                                <li><strong>Be Specific:</strong> Use clear, descriptive names that teachers will easily recognize</li>
                                <li><strong>Subject Organization:</strong> Group topics by subject for better organization</li>
                                <li><strong>Avoid Duplicates:</strong> Check existing topics before creating new ones</li>
                                <li><strong>Active Status:</strong> Only active topics appear in homework creation forms</li>
                                <li><strong>Descriptions Help:</strong> Add descriptions to help teachers understand topic scope</li>
                            </ul>
                        </div>

                        <!-- Examples -->
                        <div class="examples-box">
                            <h4><i class="ti-info-alt"></i> Examples</h4>
                            
                            <div class="example-item">
                                <strong>Mathematics</strong>
                                <span>Algebra, Geometry, Fractions & Decimals, Statistics & Probability</span>
                            </div>

                            <div class="example-item">
                                <strong>English</strong>
                                <span>Reading Comprehension, Creative Writing, Grammar & Punctuation, Poetry Analysis</span>
                            </div>

                            <div class="example-item">
                                <strong>Science</strong>
                                <span>Forces & Motion, Chemical Reactions, Living Organisms, Energy & Electricity</span>
                            </div>

                            <div class="example-item">
                                <strong>History</strong>
                                <span>World War II, Ancient Civilizations, British Monarchy, Industrial Revolution</span>
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
        return confirm('Create this homework topic?');
    });

    // Auto-capitalize first letter of topic name
    $('#name').on('blur', function() {
        const value = $(this).val();
        if (value) {
            $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
        }
    });
});
</script>
@endpush
@extends('layouts.app')

@section('title', 'Create Permission')

@push('styles')
    <style>
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .required-field::after {
            content: " *";
            color: #dc3545;
        }


        .guidelines-box {
            background-color: #e7f3ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .guidelines-box ul {
            list-style: none;
            padding: 0;
            margin: 10px 0 0 0;
        }

        .guidelines-box li {
            display: flex;
            align-items: flex-start;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .guidelines-box li i {
            color: #0066cc;
            margin-right: 10px;
            margin-top: 2px;
        }

        .sidebar-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            position: sticky;
            top: 20px;
        }

        .module-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 8px;
        }

        .module-icon {
            width: 32px;
            height: 32px;
            background-color: #e7f3ff;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: #0066cc;
        }

        .module-name {
            font-size: 1rem;
            font-weight: 500;
            color: #212529;
            text-transform: capitalize;
        }

        .example-box {
            background-color: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .example-box code {
            color: #495057;
            font-size: 1rem;
        }

        code {
            background-color: #f8f9fa;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 1rem;
            color: #0066cc;
        }

        .sidebar-section {
            margin-top: 25px;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e9ecef;
        }

        .sidebar-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .sidebar-title {
            font-size: 1rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 15px;
        }

        textarea {
            resize: vertical;
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
                                <h1>Create New Permission</h1>
                            </div>
                        </div>
                        <span>Define a new permission for the system</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.permissions.index') }}">Permissions</a></li>
                                    <li class="active">Create</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">

                    <div class="row">
                        <!-- Main Form Column -->
                        <div class="col-md-8">
                            <div class="card alert">
                                 <form method="POST" action="{{ route('superadmin.permissions.store') }}">
                                        @csrf

                                        <!-- Permission Name -->
                                        <div class="form-group">
                                            <label for="name" class="required-field">Permission Name</label>
                                            <input 
                                                type="text" 
                                                name="name" 
                                                id="name" 
                                                value="{{ old('name') }}" 
                                                placeholder="e.g. create users, view reports, manage settings"
                                                required
                                                class="form-control @error('name') is-invalid @enderror"
                                            >
                                            <small class="form-text text-muted">
                                                <i class="ti-info-alt"></i> Use format: <code>action module</code> 
                                                (e.g., "create users", "edit students")
                                            </small>
                                            @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Description -->
                                        <div class="form-group">
                                            <label for="description">Description (Optional)</label>
                                            <textarea 
                                                name="description" 
                                                id="description" 
                                                rows="3"
                                                class="form-control @error('description') is-invalid @enderror"
                                                placeholder="Describe what this permission allows users to do..."
                                                maxlength="500"
                                            >{{ old('description') }}</textarea>
                                            <small class="form-text text-muted">
                                                <i class="ti-info-alt"></i> Maximum 500 characters
                                            </small>
                                            @error('description')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Naming Guidelines -->
                                        <div class="guidelines-box">
                                            <h4 style="margin: 0 0 10px 0; font-size: 1.2rem; font-weight: 600; color: #0066cc;">
                                                <i class="ti-info-alt"></i> Naming Guidelines
                                            </h4>
                                            <ul>
                                                <li>
                                                    <i class="ti-check"></i>
                                                    <span>Use lowercase with spaces between words</span>
                                                </li>
                                                <li>
                                                    <i class="ti-check"></i>
                                                    <span>Start with an action verb (create, view, edit, delete, manage)</span>
                                                </li>
                                                <li>
                                                    <i class="ti-check"></i>
                                                    <span>Follow with the resource/module name (users, students, reports)</span>
                                                </li>
                                                <li>
                                                    <i class="ti-check"></i>
                                                    <span>Be specific and descriptive (e.g., "view attendance reports" not just "view")</span>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('superadmin.permissions.index') }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti-check"></i> Create Permission
                                            </button>
                                        </div>
                                    </form>
                            </div>
                        </div>
                        <!-- Sidebar Column -->
                        <div class="col-lg-4">
                            <div class="card">
                                <!-- Existing Modules -->
                                <div class="card-header pr">
                                    <h4><i class="ti-folder"></i> Existing Modules</h4>                                                                     
                                </div>
                                <div class="card">
                                    <div class="recent-comment m-t-15">
                                    @if($categories->count() > 0)
                                        <div>
                                            @foreach($categories->sort() as $category)
                                            <div class="module-item">
                                                <div class="module-icon">
                                                    <i class="ti-folder"></i>
                                                </div>
                                                <span class="module-name">{{ str_replace('_', ' ', $category) }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                        <small class="text-muted d-block mt-3">
                                            <i class="ti-info-alt"></i> Use these existing modules or create a new one
                                        </small>
                                    @else
                                        <p class="text-muted mb-0">
                                            <i class="ti-info-alt"></i> No existing modules yet. You're creating the first permission!
                                        </p>
                                    @endif
                                    </div>

                             
                                    <!-- Common Examples -->
                                    <div class="sidebar-section">
                                        <h4 class="sidebar-title">
                                            <i class="ti-light-bulb"></i> Common Examples
                                        </h4>
                                        <div>
                                            <div class="example-box">
                                                <code>create users</code>
                                            </div>
                                            <div class="example-box">
                                                <code>view students</code>
                                            </div>
                                            <div class="example-box">
                                                <code>edit classes</code>
                                            </div>
                                            <div class="example-box">
                                                <code>delete attendance</code>
                                            </div>
                                            <div class="example-box">
                                                <code>manage settings</code>
                                            </div>
                                            <div class="example-box">
                                                <code>export reports</code>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Quick Tips -->
                                    <div class="sidebar-section">
                                        <h4 class="sidebar-title">
                                            <i class="ti-help-alt"></i> Quick Tips
                                        </h4>
                                        <ul style="list-style: none; padding: 0; margin: 0;">
                                            <li style="margin-bottom: 10px;">
                                                <i class="ti-angle-right text-primary"></i> Keep names short and clear
                                            </li>
                                            <li style="margin-bottom: 10px;">
                                                <i class="ti-angle-right text-primary"></i> Group related permissions by module
                                            </li>
                                            <li style="margin-bottom: 10px;">
                                                <i class="ti-angle-right text-primary"></i> Avoid duplicate permission names
                                            </li>
                                            <li style="margin-bottom: 10px;">
                                                <i class="ti-angle-right text-primary"></i> Think about who needs this permission
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- /# card -->
                            </div>
                            <!-- /# column -->

                        </div>
                        <!-- /# row -->

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Create Permission</p>
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
            // Auto-format permission name as user types
            $('#name').on('input', function() {
                let value = $(this).val();
                // Convert to lowercase and ensure spaces between words
                value = value.toLowerCase();
                $(this).val(value);
            });

            // Character counter for description
            $('#description').on('input', function() {
                const maxLength = 500;
                const currentLength = $(this).val().length;
                const remaining = maxLength - currentLength;
                
                // Update or create counter
                if (!$('#char-counter').length) {
                    $(this).after('<small id="char-counter" class="form-text text-muted"></small>');
                }
                
                $('#char-counter').text(`${currentLength} / ${maxLength} characters`);
                
                if (remaining < 50) {
                    $('#char-counter').removeClass('text-muted').addClass('text-warning');
                } else {
                    $('#char-counter').removeClass('text-warning').addClass('text-muted');
                }
            });

            // Form validation
            $('form').on('submit', function(e) {
                const permissionName = $('#name').val().trim();
                
                if (!permissionName) {
                    e.preventDefault();
                    alert('Please enter a permission name.');
                    $('#name').focus();
                    return false;
                }

                // Check if it follows the pattern (at least 2 words)
                const words = permissionName.split(' ').filter(word => word.length > 0);
                if (words.length < 2) {
                    e.preventDefault();
                    alert('Permission name should follow the format: "action module"\nFor example: "create users" or "view reports"');
                    $('#name').focus();
                    return false;
                }

                // Check if first word is an action verb
                const commonActions = ['create', 'view', 'edit', 'update', 'delete', 'manage', 'export', 'import', 'approve', 'reject'];
                if (!commonActions.includes(words[0])) {
                    const confirm = window.confirm(
                        'The permission name should typically start with an action verb like:\n' +
                        'create, view, edit, delete, manage, export, etc.\n\n' +
                        'Your permission starts with "' + words[0] + '".\n\n' +
                        'Do you want to proceed anyway?'
                    );
                    
                    if (!confirm) {
                        e.preventDefault();
                        $('#name').focus();
                        return false;
                    }
                }
            });

            // Click on example to fill form
            $('.example-box').on('click', function() {
                const exampleText = $(this).find('code').text();
                $('#name').val(exampleText).focus();
                
                // Smooth scroll to top of form
                $('html, body').animate({
                    scrollTop: $('#name').offset().top - 100
                }, 500);
            });

            // Click on module item to suggest in form
            $('.module-item').on('click', function() {
                const moduleName = $(this).find('.module-name').text().trim();
                const currentValue = $('#name').val().trim();
                
                // If field is empty or doesn't end with this module
                if (!currentValue || !currentValue.endsWith(moduleName)) {
                    // Extract action if exists, otherwise use 'view' as default
                    const words = currentValue.split(' ');
                    const action = words.length > 0 && words[0] ? words[0] : 'view';
                    
                    $('#name').val(`${action} ${moduleName}`).focus();
                    
                    // Smooth scroll to top of form
                    $('html, body').animate({
                        scrollTop: $('#name').offset().top - 100
                    }, 500);
                }
            });

            // Add tooltips to module items
            $('.module-item').attr('title', 'Click to use this module in your permission');
            $('.example-box').attr('title', 'Click to use this example');
            
            // Hover effects
            $('.module-item, .example-box').css('cursor', 'pointer');
        });
    </script>
@endpush
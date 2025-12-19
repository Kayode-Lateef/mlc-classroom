@extends('layouts.app')

@section('title', 'Edit Role')

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

        .info-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-box i {
            color: #ffc107;
            font-size: 1.2rem;
        }

        .warning-box {
            background-color: #f8d7da;
            border: 1px solid #dc3545;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .warning-box i {
            color: #dc3545;
            font-size: 1.2rem;
        }

        .permission-module {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #fff;
        }

        .permission-module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }

        .permission-module-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #212529;
            text-transform: capitalize;
        }

        .select-all-label {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: #6c757d;
            cursor: pointer;
        }

        .select-all-label input {
            margin-right: 8px;
        }

        .permission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
        }

        .permission-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .permission-item:hover {
            background-color: #e9ecef;
            transform: translateX(5px);
        }

        .permission-item input[type="checkbox"] {
            margin-right: 10px;
        }

        .permission-item label {
            margin-bottom: 0;
            cursor: pointer;
            font-size: 0.9rem;
            color: #495057;
        }

        .permission-item input[type="checkbox"]:checked + label {
            color: #007bff;
            font-weight: 500;
        }

        .no-permissions-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        .role-info-card {
            background-color: #e7f3ff;
            border: 1px solid #0066cc;
            border-radius: 8px;
            padding: 15px;
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
                                <h1>Edit Role: {{ ucwords(str_replace('_', ' ', $role->name)) }}</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.roles.index') }}">Roles</a></li>
                                    <li><a href="{{ route('superadmin.roles.show', $role) }}">{{ ucwords(str_replace('_', ' ', $role->name)) }}</a></li>
                                    <li class="active">Edit</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <!-- System Role Warning -->
                                    @if(in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent']))
                                    <div class="warning-box">
                                        <div class="d-flex">
                                            <i class="ti-alert mr-3"></i>
                                            <div>
                                                <strong>System Role</strong>
                                                <p class="mb-0 mt-2">This is a system role. The name cannot be changed, but you can modify its permissions.</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Role Info Card -->
                                    <div class="role-info-card">
                                        <div class="d-flex">
                                            <i class="ti-info-alt mr-3" style="color: #0066cc; font-size: 1.2rem;"></i>
                                            <div>
                                                <strong>Role Statistics</strong>
                                                <ul class="mb-0 mt-2">
                                                    <li>Created: {{ $role->created_at->format('d M Y, H:i') }}</li>
                                                    <li>Last Updated: {{ $role->updated_at->format('d M Y, H:i') }}</li>
                                                    <li>Assigned to {{ $role->users()->count() }} user(s)</li>
                                                    <li>Current Permissions: {{ $role->permissions()->count() }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('superadmin.roles.update', $role) }}">
                                        @csrf
                                        @method('PUT')

                                        <!-- Role Name -->
                                        <div class="form-group">
                                            <label for="name" class="required-field">Role Name</label>
                                            <input 
                                                type="text" 
                                                name="name" 
                                                id="name" 
                                                value="{{ old('name', $role->name) }}" 
                                                required
                                                @if(in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent'])) readonly @endif
                                                class="form-control @error('name') is-invalid @enderror"
                                            >
                                            <small class="form-text text-muted">
                                                <i class="ti-info-alt"></i> 
                                                @if(in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent']))
                                                    System roles cannot be renamed.
                                                @else
                                                    Use lowercase with underscores (e.g., content_manager)
                                                @endif
                                            </small>
                                            @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Permissions Section -->
                                        <div class="form-section">
                                            <h4 class="mb-3">
                                                <i class="ti-key"></i> Assign Permissions
                                            </h4>

                                            @if($permissions->count() > 0)
                                                <div id="permissions-container">
                                                    @foreach($permissions as $module => $modulePermissions)
                                                    <div class="permission-module">
                                                        <div class="permission-module-header">
                                                            <h5 class="permission-module-title">
                                                                <i class="ti-folder"></i> {{ str_replace('_', ' ', $module) }}
                                                            </h5>
                                                            <label class="select-all-label">
                                                                <input 
                                                                    type="checkbox" 
                                                                    class="select-all-module"
                                                                    data-module="{{ $module }}"
                                                                >
                                                                Select All
                                                            </label>
                                                        </div>

                                                        <div class="permission-grid">
                                                            @foreach($modulePermissions as $permission)
                                                            <div class="permission-item">
                                                                <input 
                                                                    type="checkbox" 
                                                                    name="permissions[]" 
                                                                    value="{{ $permission->id }}"
                                                                    id="permission-{{ $permission->id }}"
                                                                    class="module-permission"
                                                                    data-module="{{ $module }}"
                                                                    {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                                                >
                                                                <label for="permission-{{ $permission->id }}">
                                                                    {{ ucwords(str_replace(['.', '_'], ' ', $permission->name)) }}
                                                                </label>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="no-permissions-box">
                                                    <i class="ti-alert" style="font-size: 3rem; color: #ffc107;"></i>
                                                    <h5 class="mt-3">No Permissions Available</h5>
                                                    <p class="text-muted">Please create permissions first before assigning them to roles.</p>
                                                    <a href="{{ route('superadmin.permissions.create') }}" class="btn btn-primary mt-3">
                                                        <i class="ti-plus"></i> Create Permissions
                                                    </a>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('superadmin.roles.show', $role) }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti-check"></i> Update Role
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Edit Role</p>
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
            // Initialize select all checkboxes based on current state
            $('.select-all-module').each(function() {
                const module = $(this).data('module');
                const allCheckboxes = $(`.module-permission[data-module="${module}"]`);
                const checkedCheckboxes = $(`.module-permission[data-module="${module}"]:checked`);
                
                $(this).prop('checked', allCheckboxes.length === checkedCheckboxes.length && allCheckboxes.length > 0);
            });

            // Select all for module
            $('.select-all-module').on('change', function() {
                const module = $(this).data('module');
                const checked = $(this).is(':checked');
                
                $(`.module-permission[data-module="${module}"]`).prop('checked', checked);
                
                updatePermissionCount();
            });

            // Update select all when individual checkboxes change
            $('.module-permission').on('change', function() {
                const module = $(this).data('module');
                const allCheckboxes = $(`.module-permission[data-module="${module}"]`);
                const checkedCheckboxes = $(`.module-permission[data-module="${module}"]:checked`);
                const selectAll = $(`.select-all-module[data-module="${module}"]`);
                
                selectAll.prop('checked', allCheckboxes.length === checkedCheckboxes.length);
                
                updatePermissionCount();
            });

            // Click anywhere on permission item to toggle checkbox
            $('.permission-item').on('click', function(e) {
                if (e.target.tagName !== 'INPUT') {
                    const checkbox = $(this).find('input[type="checkbox"]');
                    checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
                }
            });

            // Show count of selected permissions
            function updatePermissionCount() {
                const totalSelected = $('.module-permission:checked').length;
                const totalPermissions = $('.module-permission').length;
                
                if (totalSelected > 0) {
                    if (!$('#permission-count').length) {
                        $('#permissions-container').prepend(
                            '<div id="permission-count" class="alert alert-info">' +
                            '<i class="ti-info-alt"></i> ' +
                            '<span id="count-text"></span>' +
                            '</div>'
                        );
                    }
                    $('#count-text').text(`${totalSelected} of ${totalPermissions} permissions selected`);
                } else {
                    $('#permission-count').remove();
                }
            }

            // Initial count
            updatePermissionCount();

            // Warn about unsaved changes
            let formChanged = false;
            $('input[type="checkbox"]').on('change', function() {
                formChanged = true;
            });

            $(window).on('beforeunload', function() {
                if (formChanged) {
                    return 'You have unsaved changes. Are you sure you want to leave?';
                }
            });

            $('form').on('submit', function() {
                formChanged = false; // Don't show warning on form submit
            });

            // Form validation
            $('form').on('submit', function(e) {
                const roleName = $('#name').val().trim();
                
                if (!roleName) {
                    e.preventDefault();
                    alert('Please enter a role name.');
                    $('#name').focus();
                    return false;
                }
            });

            // Highlight changes
            const originalSelections = {};
            $('.module-permission').each(function() {
                originalSelections[$(this).val()] = $(this).is(':checked');
            });

            $('.module-permission').on('change', function() {
                const id = $(this).val();
                const currentState = $(this).is(':checked');
                const originalState = originalSelections[id];
                
                if (currentState !== originalState) {
                    $(this).closest('.permission-item').addClass('border border-warning');
                } else {
                    $(this).closest('.permission-item').removeClass('border border-warning');
                }
            });
        });
    </script>
@endpush
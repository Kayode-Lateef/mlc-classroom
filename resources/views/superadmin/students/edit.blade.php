@extends('layouts.app')

@section('title', 'Edit Student')

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
            margin-top: 20px;
        }

        .info-box i {
            color: #ffc107;
            font-size: 1.2rem;
        }

        .profile-photo-preview {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            display: none;
        }

        .current-photo {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }

        .form-group label {
            font-size: 1rem;
            font-weight: 500;
        }

        .form-control {
            font-size: 1rem;
        }

        .form-text {
            font-size: 0.95rem;
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
                                <h1>Edit Student: {{ $student->full_name }}</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('superadmin.students.index') }}">Students</a></li>
                                    <li><a href="{{ route('superadmin.students.show', $student) }}">{{ $student->full_name }}</a></li>
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
                                    <form method="POST" action="{{ route('superadmin.students.update', $student) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')

                                        <!-- Basic Information Section -->
                                        <div class="form-section">
                                            <h4 class="mb-3"><i class="ti-user"></i> Basic Information</h4>
                                            <div class="row">
                                                <!-- First Name -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="first_name" class="required-field">First Name</label>
                                                        <input 
                                                            type="text" 
                                                            name="first_name" 
                                                            id="first_name" 
                                                            value="{{ old('first_name', $student->first_name) }}" 
                                                            placeholder="e.g. John"
                                                            required
                                                            class="form-control @error('first_name') is-invalid @enderror"
                                                        >
                                                        @error('first_name')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Last Name -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="last_name" class="required-field">Last Name</label>
                                                        <input 
                                                            type="text" 
                                                            name="last_name" 
                                                            id="last_name" 
                                                            value="{{ old('last_name', $student->last_name) }}" 
                                                            placeholder="e.g. Smith"
                                                            required
                                                            class="form-control @error('last_name') is-invalid @enderror"
                                                        >
                                                        @error('last_name')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Date of Birth -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="date_of_birth" class="required-field">Date of Birth</label>
                                                        <input 
                                                            type="date" 
                                                            name="date_of_birth" 
                                                            id="date_of_birth" 
                                                            value="{{ old('date_of_birth', $student->date_of_birth->format('Y-m-d')) }}" 
                                                            required
                                                            class="form-control @error('date_of_birth') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-calendar"></i> Current age: {{ $student->date_of_birth->age }} years
                                                        </small>
                                                        @error('date_of_birth')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Parent -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="parent_id" class="required-field">Parent/Guardian</label>
                                                        <select 
                                                            name="parent_id" 
                                                            id="parent_id" 
                                                            required
                                                            class="form-control @error('parent_id') is-invalid @enderror"
                                                        >
                                                            <option value="">Select Parent</option>
                                                            @foreach($parents as $parent)
                                                            <option value="{{ $parent->id }}" {{ old('parent_id', $student->parent_id) == $parent->id ? 'selected' : '' }}>
                                                                {{ $parent->name }} ({{ $parent->email }})
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                        <small class="form-text text-muted">
                                                            <i class="ti-user"></i> Current parent: {{ $student->parent->name }}
                                                        </small>
                                                        @error('parent_id')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Enrollment Information Section -->
                                        <div class="form-section">
                                            <h4 class="mb-3"><i class="ti-book"></i> Enrollment Information</h4>
                                            <div class="row">
                                                <!-- Enrollment Date -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="enrollment_date" class="required-field">Enrollment Date</label>
                                                        <input 
                                                            type="date" 
                                                            name="enrollment_date" 
                                                            id="enrollment_date" 
                                                            value="{{ old('enrollment_date', $student->enrollment_date->format('Y-m-d')) }}" 
                                                            required
                                                            class="form-control @error('enrollment_date') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-calendar"></i> Date when student enrolled
                                                        </small>
                                                        @error('enrollment_date')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Status -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="status" class="required-field">Status</label>
                                                        <select 
                                                            name="status" 
                                                            id="status" 
                                                            required
                                                            class="form-control @error('status') is-invalid @enderror"
                                                        >
                                                            <option value="active" {{ old('status', $student->status) === 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ old('status', $student->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                            <option value="graduated" {{ old('status', $student->status) === 'graduated' ? 'selected' : '' }}>Graduated</option>
                                                            <option value="withdrawn" {{ old('status', $student->status) === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                                                        </select>
                                                        <small class="form-text text-muted">
                                                            <i class="ti-flag"></i> Current enrollment status
                                                        </small>
                                                        @error('status')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Emergency Contact Section -->
                                        <div class="form-section">
                                            <h4 class="mb-3"><i class="ti-alarm-clock"></i> Emergency Contact</h4>
                                            <div class="row">
                                                <!-- Emergency Contact Name -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="emergency_contact">Emergency Contact Name</label>
                                                        <input 
                                                            type="text" 
                                                            name="emergency_contact" 
                                                            id="emergency_contact" 
                                                            value="{{ old('emergency_contact', $student->emergency_contact) }}"
                                                            placeholder="e.g. Jane Doe"
                                                            class="form-control @error('emergency_contact') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-user"></i> Name of emergency contact person
                                                        </small>
                                                        @error('emergency_contact')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Emergency Phone -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="emergency_phone">Emergency Phone</label>
                                                        <input 
                                                            type="text" 
                                                            name="emergency_phone" 
                                                            id="emergency_phone" 
                                                            value="{{ old('emergency_phone', $student->emergency_phone) }}"
                                                            placeholder="+234 800 000 0000"
                                                            class="form-control @error('emergency_phone') is-invalid @enderror"
                                                        >
                                                        <small class="form-text text-muted">
                                                            <i class="ti-mobile"></i> Emergency contact phone number
                                                        </small>
                                                        @error('emergency_phone')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Medical Information -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="medical_info"><i class="ti-heart"></i> Medical Information</label>
                                                    <textarea 
                                                        name="medical_info" 
                                                        id="medical_info" 
                                                        rows="4"
                                                        placeholder="Any allergies, medical conditions, or special needs..."
                                                        class="form-control @error('medical_info') is-invalid @enderror"
                                                    >{{ old('medical_info', $student->medical_info) }}</textarea>
                                                    <small class="form-text text-muted">
                                                        <i class="ti-info-alt"></i> Include allergies, medications, conditions, or special requirements
                                                    </small>
                                                    @error('medical_info')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Current Profile Photo -->
                                        @if($student->profile_photo)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Current Profile Photo</label>
                                                    <div>
                                                        <img src="{{ asset('storage/' . $student->profile_photo) }}" alt="{{ $student->full_name }}" class="current-photo img-thumbnail">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Profile Photo -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="profile_photo">
                                                        <i class="ti-image"></i> {{ $student->profile_photo ? 'Change Profile Photo' : 'Profile Photo' }}
                                                    </label>
                                                    <input 
                                                        type="file" 
                                                        name="profile_photo" 
                                                        id="profile_photo" 
                                                        accept="image/*"
                                                        class="form-control-file @error('profile_photo') is-invalid @enderror"
                                                    >
                                                    <small class="form-text text-muted">
                                                        <i class="ti-info-alt"></i> Max size: 2MB. Formats: JPG, PNG, GIF
                                                    </small>
                                                    @error('profile_photo')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                    
                                                    <!-- Image Preview -->
                                                    <img id="photo-preview" class="profile-photo-preview img-thumbnail" alt="Photo preview">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Info Box -->
                                        <div class="info-box">
                                            <div class="d-flex">
                                                <i class="ti-info-alt mr-3"></i>
                                                <div>
                                                    <strong>Student Information</strong>
                                                    <ul class="mb-0 mt-2">
                                                        <li>Student ID: {{ $student->id }}</li>
                                                        <li>Enrolled: {{ $student->enrollment_date->format('d M Y') }} ({{ $student->enrollment_date->diffForHumans() }})</li>
                                                        <li>Classes Enrolled: {{ $student->classes->count() }}</li>
                                                        <li>Last Updated: {{ $student->updated_at->format('d M Y, H:i') }}</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group mt-4 pt-3 border-top text-right">
                                            <a href="{{ route('superadmin.students.show', $student) }}" class="btn btn-secondary">
                                                <i class="ti-close"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti-check"></i> Update Student
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
                                <p>MLC Classroom - Edit Student</p>
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
            // Image preview for new photo
            $('#profile_photo').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#photo-preview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#photo-preview').hide();
                }
            });

            // Auto-format phone number (optional)
            $('#emergency_phone').on('blur', function() {
                let value = $(this).val().trim();
                // Simple formatting example - you can customize this
                if (value && !value.startsWith('+')) {
                    if (value.startsWith('0')) {
                        $(this).val('+234' + value.substring(1));
                    }
                }
            });

            // Set max date for date of birth (must be in the past)
            const today = new Date().toISOString().split('T')[0];
            $('#date_of_birth').attr('max', today);

            // Status change warning
            $('#status').on('change', function() {
                const newStatus = $(this).val();
                const originalStatus = '{{ $student->status }}';
                
                if (newStatus !== originalStatus && (newStatus === 'graduated' || newStatus === 'withdrawn')) {
                    if (!confirm('Are you sure you want to change the status to ' + newStatus + '? This may affect the student\'s class enrollments.')) {
                        $(this).val(originalStatus);
                    }
                }
            });
        });
    </script>
@endpush
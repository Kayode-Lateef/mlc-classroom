<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PATCH')

    <h4 style="margin-bottom: 20px;">
        <i class="ti-id-badge"></i> Personal Information
    </h4>

    <div class="row">
        <!-- Profile Photo Section -->
        <div class="col-md-12 mb-4">
            <div class="form-group">
                <label style="font-size: 0.875rem; font-weight: 500;">Profile Photo</label>
                <div class="row">
                    <div class="col-md-3 text-center">
                        @if($user->profile_photo)
                            <img id="current-photo" 
                                 src="{{ Storage::url($user->profile_photo) }}" 
                                 alt="{{ $user->name }}" 
                                 class="img-thumbnail"
                                 style="width: 200px; height: 200px; object-fit: cover;">
                        @else
                            <div id="current-photo" 
                                 class="bg-secondary text-white d-flex align-items-center justify-content-center mx-auto"
                                 style="width: 200px; height: 200px; font-size: 4rem;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        
                        @if($user->profile_photo)
                        <form method="POST" action="{{ route('profile.photo.delete') }}" style="margin-top: 10px;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete your profile photo?')">
                                <i class="ti-trash"></i> Remove Photo
                            </button>
                        </form>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <input type="file" 
                               name="profile_photo" 
                               id="profile_photo" 
                               accept="image/*"
                               class="form-control-file @error('profile_photo') is-invalid @enderror">
                        <small class="form-text text-muted">
                            <i class="ti-image"></i> Maximum file size: 2MB. Supported formats: JPG, PNG, GIF
                        </small>
                        @error('profile_photo')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                        
                        <!-- Image Preview -->
                        <div id="photo-preview-container" style="margin-top: 15px; display: none;">
                            <p style="font-size: 0.875rem; font-weight: 500;">Preview:</p>
                            <img id="photo-preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;" alt="Photo preview">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Name -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="name" style="font-size: 0.875rem; font-weight: 500;">
                    Full Name <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $user->name) }}"
                       class="form-control @error('name') is-invalid @enderror"
                       required>
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Email -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="email" style="font-size: 0.875rem; font-weight: 500;">
                    Email Address <span class="text-danger">*</span>
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="{{ old('email', $user->email) }}"
                       class="form-control @error('email') is-invalid @enderror"
                       required>
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                
                @if ($user->email_verified_at === null)
                    <small class="form-text text-warning">
                        <i class="ti-alert"></i> Your email address is unverified.
                    </small>
                @endif
            </div>
        </div>

        <!-- Phone -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="phone" style="font-size: 0.875rem; font-weight: 500;">Phone Number</label>
                <input type="text" 
                       name="phone" 
                       id="phone" 
                       value="{{ old('phone', $user->phone) }}"
                       class="form-control @error('phone') is-invalid @enderror"
                       placeholder="+44 1234 567890">
                @error('phone')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Role (Read-only) -->
        <div class="col-md-6">
            <div class="form-group">
                <label style="font-size: 0.875rem; font-weight: 500;">Role</label>
                <input type="text" 
                       value="{{ ucfirst($user->role) }}" 
                       class="form-control" 
                       readonly>
                <small class="form-text text-muted">
                    <i class="ti-info-alt"></i> Contact administrator to change your role
                </small>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="form-group mt-4 pt-3 border-top">
        <button type="submit" class="btn btn-primary">
            <i class="ti-check"></i> Save Changes
        </button>
        <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="btn btn-secondary">
            <i class="ti-close"></i> Cancel
        </a>
    </div>
</form>

@push('scripts')
<script>
$(document).ready(function() {
    // Image preview for new photo
    $('#profile_photo').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Check file size (2MB max)
            if (file.size > 2048000) {
                alert('File size must not exceed 2MB');
                $(this).val('');
                $('#photo-preview-container').hide();
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#photo-preview').attr('src', e.target.result);
                $('#photo-preview-container').show();
            }
            reader.readAsDataURL(file);
        } else {
            $('#photo-preview-container').hide();
        }
    });
});
</script>
@endpush
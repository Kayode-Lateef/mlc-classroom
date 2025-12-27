<h4 style="margin-bottom: 20px;">
    <i class="ti-shield"></i> Account Security
</h4>

<p style="color: #6c757d; margin-bottom: 30px;">
    Permanently delete your account and all associated data
</p>

<!-- Warning Card -->
<div class="card" style="border-left: 4px solid #dc3545;">
    <div class="card-body">
        <h5 class="text-danger">
            <i class="ti-alert"></i> Danger Zone
        </h5>
        <p style="font-size: 0.9375rem; color: #6c757d; margin-bottom: 20px;">
            Once your account is deleted, all of its resources and data will be permanently deleted. 
            Before deleting your account, please download any data or information that you wish to retain.
        </p>

        <!-- Delete Account Form -->
        <form method="POST" action="{{ route('profile.destroy') }}" id="delete-account-form">
            @csrf
            @method('DELETE')

            <div class="form-group">
                <label for="password_delete" style="font-size: 0.875rem; font-weight: 500;">
                    Confirm Password <span class="text-danger">*</span>
                </label>
                <input type="password" 
                       name="password" 
                       id="password_delete"
                       class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                       placeholder="Enter your password to confirm deletion"
                       required>
                <small class="form-text text-muted">
                    <i class="ti-info-alt"></i> You must enter your password to delete your account
                </small>
                @error('password', 'userDeletion')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="confirm-delete" required>
                    <label class="custom-control-label" for="confirm-delete" style="font-size: 0.9375rem;">
                        I understand that this action is permanent and cannot be undone
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-danger" id="delete-btn" disabled>
                <i class="ti-trash"></i> Delete My Account
            </button>
        </form>

        <!-- What gets deleted -->
        <div class="mt-4 pt-4 border-top">
            <h6 style="font-size: 0.9375rem; font-weight: 600; margin-bottom: 10px;">
                What will be deleted:
            </h6>
            <ul style="font-size: 0.875rem; color: #6c757d;">
                <li>Your profile information and settings</li>
                <li>Your activity logs and history</li>
                @if(auth()->user()->isTeacher())
                <li>Your uploaded learning resources</li>
                <li>Your class assignments (if not graded)</li>
                @endif
                @if(auth()->user()->isParent())
                <li>Your access to children's information</li>
                @endif
                <li>All associated data and preferences</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Enable delete button only when checkbox is checked
    $('#confirm-delete').on('change', function() {
        $('#delete-btn').prop('disabled', !this.checked);
    });
    
    // Confirm before deleting
    $('#delete-account-form').on('submit', function(e) {
        if (!confirm('Are you absolutely sure you want to delete your account? This action cannot be undone!')) {
            e.preventDefault();
            return false;
        }
        
        if (!confirm('This is your last chance. Are you really sure?')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush
<!-- Validation Errors for Account Deletion -->
@if ($errors->userDeletion->any())
<div class="alert alert-danger alert-dismissable">
    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
    <strong><i class="ti-alert"></i> Account Deletion Failed!</strong>
    <p style="margin-top: 10px;">Please correct the following error(s):</p>
    <ul style="margin-top: 10px; margin-bottom: 0; padding-left: 20px;">
        @foreach ($errors->userDeletion->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

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
        <p style="color: #6c757d; margin-bottom: 20px;">
            Once your account is deleted, all of its resources and data will be permanently deleted. 
            Before deleting your account, please download any data or information that you wish to retain.
        </p>

        <!-- Delete Account Form -->
        <form method="POST" action="{{ route('profile.destroy') }}" id="delete-account-form">
            @csrf
            @method('DELETE')

            <div class="form-group">
                <label for="password_delete" style="font-weight: 500;">
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
                    <label class="custom-control-label" for="confirm-delete">
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
            <h6 style="font-weight: 600; margin-bottom: 10px;">
                What will be deleted:
            </h6>
            <ul style="color: #6c757d;">
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
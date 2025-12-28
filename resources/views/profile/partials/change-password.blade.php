<!-- Validation Errors for Change Password -->
@if ($errors->updatePassword->any())
<div class="alert alert-danger alert-dismissable">
    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
    <strong><i class="ti-alert"></i> Password Change Failed!</strong>
    <p style="margin-top: 10px;">Please correct the following error(s):</p>
    <ul style="margin-top: 10px; margin-bottom: 0; padding-left: 20px;">
        @foreach ($errors->updatePassword->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('profile.password.update') }}">
    @csrf
    @method('PATCH')

    <h4 style="margin-bottom: 20px;">
        <i class="ti-lock"></i> Change Password
    </h4>

    <p style="color: #6c757d; margin-bottom: 20px;">
        Ensure your account is using a long, random password to stay secure.
    </p>

    <div class="row">
        <!-- Current Password -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="current_password" style="font-weight: 500;">
                    Current Password <span class="text-danger">*</span>
                </label>
                <div class="password-input-wrapper" style="position: relative;">
                    <input type="password" 
                           name="current_password" 
                           id="current_password"
                           class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                           required
                           autocomplete="current-password"
                           style="padding-right: 40px;">
                    <button type="button" class="toggle-password" data-target="current_password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6c757d; cursor: pointer; padding: 5px 10px;">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
                @error('current_password', 'updatePassword')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-6"></div>

        <!-- New Password -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="password" style="font-weight: 500;">
                    New Password <span class="text-danger">*</span>
                </label>
                <div class="password-input-wrapper" style="position: relative;">
                    <input type="password" 
                           name="password" 
                           id="password"
                           class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                           required
                           autocomplete="new-password"
                           style="padding-right: 40px;">
                    <button type="button" class="toggle-password" data-target="password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6c757d; cursor: pointer; padding: 5px 10px;">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
                <small class="form-text text-muted">
                    <i class="ti-info-alt"></i> Minimum 8 characters
                </small>
                @error('password', 'updatePassword')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                
                <!-- Password Strength Indicator -->
                <div id="password-strength" style="margin-top: 5px; display: none;">
                    <small id="strength-text"></small>
                    <div class="progress" style="height: 5px;">
                        <div id="strength-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="password_confirmation" style="font-weight: 500;">
                    Confirm New Password <span class="text-danger">*</span>
                </label>
                <div class="password-input-wrapper" style="position: relative;">
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation"
                           class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                           required
                           autocomplete="new-password"
                           style="padding-right: 40px;">
                    <button type="button" class="toggle-password" data-target="password_confirmation" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6c757d; cursor: pointer; padding: 5px 10px;">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
                @error('password_confirmation', 'updatePassword')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <!-- Alert -->
    <div class="alert alert-warning" style="margin-top: 20px;">
        <i class="ti-alert"></i> <strong>Important:</strong> After changing your password, you will remain logged in on this device, but you may be logged out from other devices.
    </div>

    <!-- Save Button -->
    <div class="form-group mt-4 pt-3 border-top">
        <button type="submit" class="btn btn-success">
            <i class="ti-lock"></i> Change Password
        </button>
        <button type="reset" class="btn btn-secondary">
            <i class="ti-reload"></i> Reset
        </button>
    </div>
</form>
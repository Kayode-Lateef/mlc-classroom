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
                <label for="current_password" style="font-size: 0.875rem; font-weight: 500;">
                    Current Password <span class="text-danger">*</span>
                </label>
                <input type="password" 
                       name="current_password" 
                       id="current_password"
                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                       required
                       autocomplete="current-password">
                @error('current_password', 'updatePassword')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-6"></div>

        <!-- New Password -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="password" style="font-size: 0.875rem; font-weight: 500;">
                    New Password <span class="text-danger">*</span>
                </label>
                <input type="password" 
                       name="password" 
                       id="password"
                       class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                       required
                       autocomplete="new-password">
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
                <label for="password_confirmation" style="font-size: 0.875rem; font-weight: 500;">
                    Confirm New Password <span class="text-danger">*</span>
                </label>
                <input type="password" 
                       name="password_confirmation" 
                       id="password_confirmation"
                       class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                       required
                       autocomplete="new-password">
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

@push('scripts')
<script>
$(document).ready(function() {
    // Password strength checker
    $('#password').on('keyup', function() {
        const password = $(this).val();
        
        if (password.length === 0) {
            $('#password-strength').hide();
            return;
        }
        
        $('#password-strength').show();
        
        let strength = 0;
        let strengthText = '';
        let strengthColor = '';
        
        // Length check
        if (password.length >= 8) strength += 25;
        if (password.length >= 12) strength += 25;
        
        // Character variety checks
        if (/[a-z]/.test(password)) strength += 15;
        if (/[A-Z]/.test(password)) strength += 15;
        if (/[0-9]/.test(password)) strength += 10;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 10;
        
        // Set strength text and color
        if (strength < 40) {
            strengthText = 'Weak';
            strengthColor = 'danger';
        } else if (strength < 70) {
            strengthText = 'Fair';
            strengthColor = 'warning';
        } else if (strength < 90) {
            strengthText = 'Good';
            strengthColor = 'info';
        } else {
            strengthText = 'Strong';
            strengthColor = 'success';
        }
        
        $('#strength-text').text('Password strength: ' + strengthText)
            .removeClass('text-danger text-warning text-info text-success')
            .addClass('text-' + strengthColor);
        
        $('#strength-bar')
            .css('width', strength + '%')
            .removeClass('bg-danger bg-warning bg-info bg-success')
            .addClass('bg-' + strengthColor);
    });
    
    // Password confirmation validation
    $('#password_confirmation').on('keyup', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword && password !== confirmPassword) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<span class="invalid-feedback d-block">Passwords do not match</span>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });
});
</script>
@endpush
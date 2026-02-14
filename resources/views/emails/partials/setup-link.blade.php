{{-- Password Setup Link partial --}}
{{-- Required: $data array with setup_url, optional requires_verification --}}
@if(isset($data['setup_url']))
    <div class="alert-box success" style="background-color: #e8f5e9; border-left: 4px solid #28a745;">
        <strong style="color: #155724;">🔑 Set Your Password</strong>

        @if(isset($data['requires_verification']) && $data['requires_verification'])
            <p style="margin: 10px 0 5px 0; color: #155724; font-size: 14px;">
                <strong>Step 1:</strong> Verify your email using the separate verification email you received.<br>
                <strong>Step 2:</strong> Click the button below to set your password.
            </p>
        @else
            <p style="margin: 10px 0 5px 0; color: #155724;">
                Click the button below to set your password and access your account.
            </p>
        @endif

        <div style="text-align: center; margin: 15px 0;">
            <a href="{{ $data['setup_url'] }}" style="display: inline-block; padding: 14px 35px; background: #28a745; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">Set My Password</a>
        </div>
        <p style="margin: 5px 0 0 0; color: #155724; font-size: 13px;">
            This link expires in 60 minutes. If it expires, use "Forgot Password" on the login page.
        </p>
    </div>
@endif
{{-- Type-based Alert Boxes partial --}}
{{-- Required: $type variable --}}
@if(isset($type))
    @if($type === 'emergency' || $type === 'absence')
        <div class="alert-box danger">
            <strong>⚠️ Urgent Notice</strong>
            <p style="margin: 5px 0 0 0; color: #721c24;">
                This is an urgent notification. Please take immediate action if required.
            </p>
        </div>
    @elseif($type === 'homework_overdue')
        <div class="alert-box warning">
            <strong>⏰ Overdue Alert</strong>
            <p style="margin: 5px 0 0 0; color: #856404;">
                This homework is now overdue. Please submit as soon as possible.
            </p>
        </div>
    @elseif($type === 'account_activated' || $type === 'student_enrolled')
        <div class="alert-box success">
            <strong>✅ Success</strong>
            <p style="margin: 5px 0 0 0; color: #155724;">
                Action completed successfully. You can now proceed.
            </p>
        </div>
    @endif
@endif
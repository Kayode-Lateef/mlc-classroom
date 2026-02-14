{{-- Additional Details partial --}}
{{-- Required: $data array --}}
{{-- M-4 FIX: Canonical exclusion list in one place --}}
@if(isset($data) && is_array($data))
    @php
        $excludeKeys = [
            // System/routing keys
            'url', 'type', 'title', 'message', 'icon',
            // Already rendered in cards
            'student_name', 'student_id', 'user_name', 'user_email', 'user_role', 'user_id',
            'homework_title', 'homework_id', 'class_name', 'class_id',
            'due_date', 'grade', 'weekly_hours',
            // Security: never display
            'temporary_password', 'setup_url', 'password', 'token',
            // Meta
            'requires_verification', 'sent_by', 'sent_at',
        ];

        $displayData = array_filter($data, function($value, $key) use ($excludeKeys) {
            return !in_array($key, $excludeKeys) && (is_string($value) || is_numeric($value));
        }, ARRAY_FILTER_USE_BOTH);
    @endphp

    @if(count($displayData) > 0)
        <div class="info-box {{ $data['type'] ?? 'general' }}">
            <strong>📋 Additional Details:</strong>
            <ul>
                @foreach($displayData as $key => $value)
                    <li>
                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                        {{ $value }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endif
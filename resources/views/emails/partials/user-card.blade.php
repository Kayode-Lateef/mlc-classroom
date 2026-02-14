{{-- User Account Card partial --}}
{{-- Required: $data array with user_name, optional user_role, user_email --}}
@if(isset($data['user_name']) && in_array($data['type'] ?? '', ['user_created', 'user_deleted', 'account_created']))
    <div class="user-card">
        <div class="name">👤 {{ $data['user_name'] }}</div>
        @if(isset($data['user_role']))
            <div class="role">{{ ucfirst($data['user_role']) }}</div>
        @endif
        @if(isset($data['user_email']))
            <div class="role" style="margin-top: 8px;">{{ $data['user_email'] }}</div>
        @endif
    </div>
@endif
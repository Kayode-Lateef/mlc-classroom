{{-- Student Enrollment Card partial --}}
{{-- Required: $data array with student_name, optional class_name, weekly_hours --}}
@if(isset($data['student_name']) && in_array($data['type'] ?? '', ['enrollment', 'student_enrolled', 'student_unenrolled']))
    <div class="user-card">
        <div class="name">👨‍🎓 {{ $data['student_name'] }}</div>
        @if(isset($data['class_name']))
            <div class="role">{{ $data['class_name'] }}</div>
        @endif
        @if(isset($data['weekly_hours']))
            <div class="role" style="margin-top: 8px;">{{ $data['weekly_hours'] }} hours per week</div>
        @endif
    </div>
@endif
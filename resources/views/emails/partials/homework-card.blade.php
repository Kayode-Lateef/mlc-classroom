{{-- Homework Details Card partial --}}
{{-- Required: $data array with homework_title, optional class_name, due_date, grade --}}
@if(isset($data['homework_title']) && in_array($data['type'] ?? '', ['homework_assigned', 'homework_graded', 'homework_reminder', 'homework_overdue']))
    <div class="homework-card">
        <div class="title">📝 {{ $data['homework_title'] }}</div>
        @if(isset($data['class_name']))
            <div class="due-date">Class: {{ $data['class_name'] }}</div>
        @endif
        @if(isset($data['due_date']))
            <div class="due-date" style="margin-top: 5px;">Due: {{ \Carbon\Carbon::parse($data['due_date'])->format('d M Y') }}</div>
        @endif
        @if(isset($data['grade']))
            <div class="due-date" style="margin-top: 5px; font-size: 18px; font-weight: 700;">Grade: {{ $data['grade'] }}</div>
        @endif
    </div>
@endif
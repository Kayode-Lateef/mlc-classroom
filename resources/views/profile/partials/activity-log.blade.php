<h4 style="margin-bottom: 20px;">
    <i class="ti-time"></i> Recent Activity
</h4>

<p style="color: #6c757d; margin-bottom: 20px;">
    Your recent account activity and actions
</p>

@if($recentActivity->count() > 0)
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th style="font-size: 0.875rem;">Action</th>
                <th style="font-size: 0.875rem;">Description</th>
                <th style="font-size: 0.875rem;">IP Address</th>
                <th style="font-size: 0.875rem;">Date & Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentActivity as $activity)
            <tr>
                <td style="font-size: 0.9375rem;">
                    @php
                        $actionIcons = [
                            'login' => 'ti-unlock',
                            'logout' => 'ti-lock',
                            'updated_profile' => 'ti-user',
                            'changed_password' => 'ti-key',
                            'created_user' => 'ti-plus',
                            'updated_user' => 'ti-pencil',
                            'deleted_user' => 'ti-trash',
                            'uploaded_resource' => 'ti-upload',
                            'marked_attendance' => 'ti-check',
                            'created_homework' => 'ti-write',
                            'graded_homework' => 'ti-marker-alt',
                        ];
                        $icon = $actionIcons[$activity->action] ?? 'ti-settings';
                    @endphp
                    <i class="{{ $icon }}"></i> {{ ucwords(str_replace('_', ' ', $activity->action)) }}
                </td>
                <td style="font-size: 0.9375rem;">{{ $activity->description }}</td>
                <td style="font-size: 0.9375rem;">
                    <span class="badge badge-secondary">{{ $activity->ip_address }}</span>
                </td>
                <td style="font-size: 0.9375rem;">{{ $activity->created_at->format('d M Y, H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div style="text-align: center; padding: 40px;">
    <i class="ti-time" style="font-size: 4rem; color: #cbd5e0;"></i>
    <p style="margin: 10px 0 0 0; color: #6c757d;">No recent activity</p>
</div>
@endif

<div class="alert alert-info mt-4">
    <i class="ti-info-alt"></i> <strong>Note:</strong> Activity logs are kept for security and audit purposes. Only your most recent 10 activities are displayed here.
</div>
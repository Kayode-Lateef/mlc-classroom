<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;

class GeneralNotification extends Notification
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toDatabase($notifiable): array
    {
        // Extract main fields
        $type = $this->data['type'] ?? 'general';
        $title = $this->data['title'] ?? '';
        $message = $this->data['message'] ?? '';
        $sentBy = $this->data['sent_by'] ?? 'System';
        $sentAt = $this->data['sent_at'] ?? now()->format('d M Y, H:i');
        
        // Extract additional data (nested 'data' array from NotificationService)
        $additionalData = $this->data['data'] ?? [];
        
        // Build complete notification data
        return array_merge([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'sent_by' => $sentBy,
            'sent_at' => $sentAt,
            'icon' => $this->getIconForType($type),
        ], $additionalData); // Merge additional data like URLs, IDs, etc.
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        // Extract main fields
        $type = $this->data['type'] ?? 'general';
        $title = $this->data['title'] ?? '';
        $message = $this->data['message'] ?? '';
        $sentBy = $this->data['sent_by'] ?? 'System';
        $sentAt = $this->data['sent_at'] ?? now()->format('d M Y, H:i');
        
        // Extract additional data
        $additionalData = $this->data['data'] ?? [];
        
        // Build complete array
        return array_merge([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'sent_by' => $sentBy,
            'sent_at' => $sentAt,
            'icon' => $this->getIconForType($type),
        ], $additionalData);
    }

    /**
     * Get icon based on notification type
     */
    protected function getIconForType($type): string
    {
        $icons = [
            'general' => 'ti-bell',
            'emergency' => 'ti-alert',
            'homework' => 'ti-pencil-alt',
            'homework_assigned' => 'ti-pencil-alt',
            'homework_graded' => 'ti-check',
            'progress_report' => 'ti-stats-up',
            'schedule_change' => 'ti-calendar',
            'absence' => 'ti-info-alt',
        ];

        return $icons[$type] ?? 'ti-bell';
    }
}
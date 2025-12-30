<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationBellController extends Controller
{
    /**
     * Get unread notifications for the bell dropdown
     */
    public function getUnread(Request $request)
    {
        $notifications = Auth::user()
            ->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'general',
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'icon' => $notification->data['icon'] ?? 'ti-bell',
                    'sent_by' => $notification->data['sent_by'] ?? 'System',
                    'sent_at' => $notification->data['sent_at'] ?? $notification->created_at->format('d M Y, H:i'),
                    'time_ago' => $notification->created_at->diffForHumans(),
                    'url' => $notification->data['url'] ?? null,
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Get unread notification count only
     */
    public function getCount(Request $request)
    {
        $count = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $notification = Auth::user()
                ->unreadNotifications()
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'unread_count' => Auth::user()->unreadNotifications()->count(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error marking notification as read: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        try {
            Auth::user()->unreadNotifications()->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'unread_count' => 0,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error marking all notifications as read: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read',
            ], 500);
        }
    }

    /**
     * Get all notifications (paginated) for the notifications page
     */
    public function getAll(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $filter = $request->get('filter', 'all'); // all, unread, read

        $query = Auth::user()->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'general',
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'icon' => $notification->data['icon'] ?? 'ti-bell',
                    'sent_by' => $notification->data['sent_by'] ?? 'System',
                    'sent_at' => $notification->data['sent_at'] ?? $notification->created_at->format('d M Y, H:i'),
                    'time_ago' => $notification->created_at->diffForHumans(),
                    'url' => $notification->data['url'] ?? null,
                    'read' => $notification->read_at !== null,
                    'read_at' => $notification->read_at ? $notification->read_at->format('d M Y, H:i') : null,
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->limit((int) $request->input('limit', 12))
            ->get()
            ->map(fn ($notification) => $this->formatNotification($notification));

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $notificationId)
    {
        $notification = $request->user()->notifications()->whereKey($notificationId)->firstOrFail();
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    public function clearRead(Request $request)
    {
        $request->user()->readNotifications()->delete();

        return response()->json([
            'success' => true,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    private function formatNotification($notification): array
    {
        $data = $notification->data ?? [];

        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? 'Notification',
            'message' => $data['message'] ?? '',
            'module' => $data['module'] ?? '',
            'action' => $data['action'] ?? '',
            'icon' => $data['icon'] ?? 'bi-bell',
            'url' => $data['url'] ?? null,
            'read' => ! is_null($notification->read_at),
            'time' => optional($notification->created_at)->diffForHumans(),
        ];
    }
}

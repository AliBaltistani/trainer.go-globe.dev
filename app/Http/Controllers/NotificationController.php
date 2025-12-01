<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark all notifications as read/clear them.
     * Depending on requirements, "clear" might mean delete or mark as read.
     * Here we will mark as read.
     */
    public function clearAll()
    {
        $user = Auth::user();
        if ($user) {
            $user->notifications()->where('status', '!=', 'read')->update(['status' => 'read']);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Fetch latest notifications for polling.
     */
    public function getNotifications()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['count' => 0, 'html' => '']);
        }

        // Count all non-read notifications (pending, unread, etc.)
        $unreadCount = $user->notifications()->where('status', '!=', 'read')->count();
        
        // Fetch only non-read notifications for the list
        $notifications = $user->notifications()->where('status', '!=', 'read')->latest()->take(10)->get();

        // Render the list HTML
        $html = view('layouts.components.notification-items', compact('notifications'))->render();

        return response()->json([
            'count' => $unreadCount,
            'html' => $html
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        if ($user) {
            $notification = $user->notifications()->find($id);
            if ($notification) {
                $notification->update(['status' => 'read']);
            }
        }

        return back();
    }

    /**
     * Show notification details.
     */
    public function show($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        // Mark as read if not already
        if ($notification->status === 'unread') {
            $notification->update(['status' => 'read']);
        }

        return view('notifications.show', compact('notification'));
    }
}

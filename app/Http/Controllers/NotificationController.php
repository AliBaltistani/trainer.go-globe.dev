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
            $user->notifications()->update(['status' => 'read']);
        }

        return back()->with('success', 'All notifications marked as read.');
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
}

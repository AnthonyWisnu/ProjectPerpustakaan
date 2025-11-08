
<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display all notifications.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get all notifications paginated
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Mark all as read when viewing
        $user->unreadNotifications->markAsRead();

        return view('member.notifications.index', compact('notifications'));
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back()
            ->with('success', 'Semua notifikasi telah ditandai sebagai sudah dibaca.');
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->delete();

        return redirect()->back()
            ->with('success', 'Notifikasi berhasil dihapus.');
    }

    /**
     * Get unread notification count (for AJAX).
     */
    public function unreadCount()
    {
        $count = auth()->user()->unreadNotifications->count();

        return response()->json(['count' => $count]);
    }
}

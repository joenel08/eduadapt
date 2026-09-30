<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Notification::visible();

        if ($user->role === 'student') {
            $profile = $user->studentProfile;
            if (!$profile) {
                return response()->json(['notifications' => [], 'unread_count' => 0]);
            }
            $query->where('user_id', $profile->id);   // ← profile ID
        } else {
            $query->where('user_id', $user->id);      // ← user ID (teachers)
        }

        $notifications = (clone $query)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(function ($n) {
                return [
                    'id'      => $n->id,
                    'title'   => $n->title,
                    'message' => $n->message,
                    'link'    => $n->link,
                    'icon'    => $n->icon,
                    'color'   => $n->color,
                    'read'    => !is_null($n->read_at),
                    'time'    => $n->created_at->diffForHumans(),
                ];
            });

        $unreadCount = (clone $query)->unread()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    public function markAsRead($id)
    {
        $user = auth()->user();
        $query = Notification::where('id', $id);

        if ($user->role === 'student') {
            $profile = $user->studentProfile;
            if (!$profile) return response()->json(['success' => false], 403);
            $query->where('user_id', $profile->id);
        } else {
            $query->where('user_id', $user->id);
        }

        $query->firstOrFail()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $user = auth()->user();
        $query = Notification::visible()->unread();

        if ($user->role === 'student') {
            $profile = $user->studentProfile;
            if (!$profile) return response()->json(['success' => false], 403);
            $query->where('user_id', $profile->id);
        } else {
            $query->where('user_id', $user->id);
        }

        $query->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
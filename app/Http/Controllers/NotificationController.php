<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $notifications = $user->notifications()
            ->with([
                'actor:id,name,username,avatar',
                'post:id,image',
            ])
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $count = $user->notifications()->unread()->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->notifications()->unread()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}

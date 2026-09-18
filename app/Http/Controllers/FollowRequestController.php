<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FollowRequestController extends Controller
{
    public function index(): View
    {
        $requests = Follow::query()
            ->where('following_id', auth()->id())
            ->where('status', 'pending')
            ->with('follower')
            ->latest()
            ->paginate(20);

        return view('follow-requests.index', compact('requests'));
    }

    public function accept(Follow $follow): RedirectResponse
    {
        Gate::authorize('respond', $follow);
        $follow->update(['status' => 'accepted']);

        $follow->follower->notifications()->create([
            'actor_id' => auth()->id(),
            'type' => 'follow_accepted',
        ]);

        return back()->with('success', "Follow request from {$follow->follower->username} accepted.");
    }

    public function reject(Follow $follow): RedirectResponse
    {
        Gate::authorize('respond', $follow);
        $follow->delete();

        return back()->with('success', 'Follow request rejected.');
    }
}

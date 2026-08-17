<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class FollowController extends Controller
{
    public function store(User $user): RedirectResponse
    {
        Gate::authorize('follow', $user);

        $status = $user->is_private ? 'pending' : 'accepted';
        $this->user()->following()->syncWithoutDetaching([$user->id => ['status' => $status]]);

        $user->notifications()->create([
            'actor_id' => $this->user()->id,
            'type' => $status === 'pending' ? 'follow_request' : 'follow',
        ]);

        return back()->with('success', $status === 'pending'
            ? "Your follow request to {$user->username} was sent."
            : "You are now following {$user->username}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('unfollow', $user);

        $this->user()->following()->detach($user->id);

        return back()->with('success', "You unfollowed {$user->username}.");
    }

    public function storeAjax(User $user): JsonResponse
    {
        Gate::authorize('follow', $user);

        $status = $user->is_private ? 'pending' : 'accepted';
        $this->user()->following()->syncWithoutDetaching([$user->id => ['status' => $status]]);

        $user->notifications()->create([
            'actor_id' => $this->user()->id,
            'type' => $status === 'pending' ? 'follow_request' : 'follow',
        ]);

        return response()->json([
            'followed' => $status === 'accepted',
            'status' => $status,
            'follower_id' => $user->id,
        ]);
    }

    public function destroyAjax(User $user): JsonResponse
    {
        Gate::authorize('unfollow', $user);

        $this->user()->following()->detach($user->id);

        return response()->json([
            'followed' => false,
            'follower_id' => $user->id,
        ]);
    }

    private function user(): User
    {
        /** @var User $user */
        $user = request()->user();

        return $user;
    }
}

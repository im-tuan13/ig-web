<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class ExploreController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $acceptedFollowing = $user->following()
            ->wherePivot('status', 'accepted')
            ->select('users.id');

        $posts = Post::query()
            ->select(['id', 'user_id', 'image', 'caption', 'created_at'])
            ->with('user:id,name,username,avatar')
            ->with(['images' => fn ($query) => $query->orderBy('position')->orderBy('id')])
            ->withCount(['likedByUsers', 'comments'])
            ->where(function ($query) use ($user, $acceptedFollowing): void {
                $query->where('user_id', $user->id)
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('is_private', false))
                    ->orWhereIn('user_id', $acceptedFollowing);
            })
            ->notArchived()
            ->latest()
            ->paginate(18);

        return view('explore.index', compact('posts'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Story;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function __invoke(Request $request): View|Response
    {
        $user = auth()->user();

        $posts = Post::query()
            ->select([
                'id',
                'user_id',
                'image',
                'caption',
                'created_at',
            ])
            ->with([
                'user:id,name,username,avatar',
                'images' => fn ($query) => $query->orderBy('position')->orderBy('id'),
                'comments' => fn ($query) => $query
                    ->with('user:id,name,username,avatar')
                    ->oldest(),
            ])
            ->withCount([
                'likedByUsers',
                'comments',
            ])
            ->withExists([
                'likedByUsers as is_liked_by_current_user' => fn ($query) => $query->whereKey($user->id),
                'savedByUsers as is_saved_by_current_user' => fn ($query) => $query->whereKey($user->id),
            ])
            ->notArchived()
            ->whereIn(
                'user_id',
                $user->following()
                    ->wherePivot('status', 'accepted')
                    ->select('users.id')
                    ->union(
                        $user->newQuery()
                            ->select('id')
                            ->whereKey($user)
                    )
            )
            ->latest()
            ->paginate(10);

        if ($request->ajax()) {
            return response()
                ->view('feed.posts', compact('posts'))
                ->header('X-Next-Page', $posts->nextPageUrl() ?? '');
        }

        $stories = Story::query()
            ->active()
            ->whereIn(
                'user_id',
                $user->following()
                    ->wherePivot('status', 'accepted')
                    ->select('users.id')
                    ->union(
                        $user->newQuery()
                            ->select('id')
                            ->whereKey($user)
                    )
            )
            ->with('user:id,name,username,avatar')
            ->withExists([
                'viewers as viewed_by_current_user' => fn ($query) => $query->whereKey($user->id),
            ])
            ->latest()
            ->get()
            ->groupBy('user_id')
            ->map(function ($stories) {

                return [
                    'user' => $stories->first()->user,
                    'stories' => $stories->values(),
                    'has_unseen' => $stories->contains(
                        fn (Story $story) => ! $story->viewed_by_current_user
                    ),
                ];

            })
            ->values();

        $suggestions = User::query()
            ->whereKeyNot($user->id)
            ->whereDoesntHave('followers', fn ($query) => $query->whereKey($user->id))
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $searchUsers = User::query()
            ->select('id', 'name', 'username', 'avatar')
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', [
            'posts' => $posts,
            'stories' => $stories,
            'suggestions' => $suggestions,
            'searchUsers' => $searchUsers,
        ]);
    }
}

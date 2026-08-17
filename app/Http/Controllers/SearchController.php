<?php

namespace App\Http\Controllers;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function index()
    {
        return view('search.index');
    }

    public function users(Request $request)
    {
        $query = trim($request->get('q', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $users = User::query()
            ->where(function ($builder) use ($query) {
                $builder
                    ->where('username', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%");
            })
            ->orderBy('username')
            ->limit(15)
            ->get([
                'id',
                'username',
                'name',
                'avatar',
            ])
            ->map(function ($user) {
                return [
                    'username' => $user->username,
                    'name' => $user->name,
                    'avatar' => $user->avatar
                        ? asset($user->avatar)
                        : null,
                    'profile_url' => route('users.show', $user),
                ];
            });

        return response()->json($users);
    }

    public function posts(Request $request)
    {
        $query = trim($request->get('q', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $posts = Post::query()
            ->visibleTo($request->user())
            ->notArchived()
            ->where('caption', 'like', "%{$query}%")
            ->latest()
            ->limit(12)
            ->get(['id', 'image', 'caption'])
            ->map(fn (Post $post) => [
                'id' => $post->id,
                'thumbnail' => $post->image_url,
                'caption' => Str::limit((string) $post->caption, 40),
                'post_url' => route('posts.show', $post),
            ]);

        return response()->json($posts);
    }

    public function hashtags(Request $request)
    {
        $query = trim($request->get('q', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $hashtags = Hashtag::query()
            ->where('name', 'like', "%{$query}%")
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->limit(10)
            ->get()
            ->map(fn (Hashtag $tag) => [
                'name' => $tag->name,
                'posts_count' => $tag->posts_count,
                'url' => route('hashtags.show', $tag->name),
            ]);

        return response()->json($hashtags);
    }
}

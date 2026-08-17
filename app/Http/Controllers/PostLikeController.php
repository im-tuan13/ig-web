<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PostLikeController extends Controller
{
    public function store(Post $post): JsonResponse
    {
        $post->likedByUsers()->syncWithoutDetaching([request()->user()->id]);

        if ($post->user_id !== request()->user()->id) {
            $post->user->notifications()->create([
                'actor_id' => request()->user()->id,
                'type' => 'like',
                'post_id' => $post->id,
            ]);
        }

        return response()->json([
            'liked' => true,
            'likes_count' => $post->likedByUsers()->count(),
        ]);
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->likedByUsers()->detach(request()->user()->id);

        return response()->json([
            'liked' => false,
            'likes_count' => $post->likedByUsers()->count(),
        ]);
    }
}

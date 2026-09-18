<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PostCommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $request->validated('comment'),
        ]);

        if ($post->user_id !== $request->user()->id) {
            $post->user->notifications()->create([
                'actor_id' => $request->user()->id,
                'type' => 'comment',
                'post_id' => $post->id,
                'data' => ['comment' => Str::limit($comment->comment, 100)],
            ]);
        }

        $mentionedUsernames = Post::parseMentions($comment->comment);

        if ($mentionedUsernames !== []) {
            User::whereIn('username', $mentionedUsernames)
                ->where('id', '!=', $request->user()->id)
                ->get()
                ->each(function (User $mentioned) use ($request, $post, $comment): void {
                    $mentioned->notifications()->create([
                        'actor_id' => $request->user()->id,
                        'type' => 'mention',
                        'post_id' => $post->id,
                        'data' => ['context' => 'comment', 'comment' => Str::limit($comment->comment, 100)],
                    ]);
                });
        }

        $comment->load('user:id,name,username,avatar');

        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'body' => $comment->comment,
                'canDelete' => true,
                'user' => [
                    'username' => $comment->user->username,
                    'avatar' => $comment->user->avatar,
                ],
            ],
            'comments_count' => $post->comments()->count(),
        ], 201);
    }

    public function destroy(Post $post, Comment $comment): JsonResponse
    {
        abort_if($comment->post_id !== $post->id, 404);
        Gate::authorize('delete', $comment);
        $comment->delete();

        return response()->json([
            'deleted' => true,
            'comments_count' => $post->comments()->count(),
        ]);
    }
}

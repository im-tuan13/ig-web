<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostShareController extends Controller
{
    public function conversations(Request $request): JsonResponse
    {
        $conversations = $request->user()->conversations()->with('participants:id,name,username,avatar')->get()->map(fn ($conversation) => [
            'id' => $conversation->id,
            'name' => $conversation->displayNameFor($request->user()),
        ]);

        return response()->json($conversations);
    }

    public function store(Request $request, Post $post): JsonResponse
    {
        Gate::authorize('view', $post);
        $validated = $request->validate(['conversation_ids' => ['required', 'array', 'min:1'], 'conversation_ids.*' => ['integer']]);
        $user = $request->user();
        $conversationIds = $user->conversations()->whereIn('conversations.id', $validated['conversation_ids'])->pluck('conversations.id');

        foreach ($conversationIds as $conversationId) {
            $user->conversations()->findOrFail($conversationId)->messages()->create([
                'user_id' => $user->id,
                'message' => '',
                'post_id' => $post->id,
            ]);
        }

        return response()->json(['sent' => true, 'count' => $conversationIds->count()]);
    }
}

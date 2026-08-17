<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PostSaveController extends Controller
{
    public function store(Post $post): JsonResponse
    {
        $post->savedByUsers()->syncWithoutDetaching([request()->user()->id]);

        return response()->json(['saved' => true]);
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->savedByUsers()->detach(request()->user()->id);

        return response()->json(['saved' => false]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PostArchiveController extends Controller
{
    public function index(Request $request): View
    {
        $posts = $request->user()->posts()
            ->whereNotNull('archived_at')
            ->select(['id', 'user_id', 'image', 'caption', 'archived_at'])
            ->with(['images' => fn ($q) => $q->orderBy('position')->orderBy('id')])
            ->withCount(['likedByUsers', 'comments'])
            ->latest('archived_at')
            ->paginate(18);

        return view('posts.archive', compact('posts'));
    }

    public function store(Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);
        $post->update(['archived_at' => now()]);

        return back()->with('success', 'Post archived.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);
        $post->update(['archived_at' => null]);

        return back()->with('success', 'Post unarchived.');
    }
}

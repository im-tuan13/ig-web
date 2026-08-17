<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function index(Request $request): View
    {
        $collections = $request->user()->collections()
            ->withCount('posts')
            ->with(['posts' => fn ($q) => $q->latest('collection_post.created_at')->limit(1)])
            ->latest()
            ->get();

        return view('collections.index', compact('collections'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
        ]);

        $collection = $request->user()->collections()->create($data);

        return to_route('collections.show', $collection)->with('success', 'Collection created.');
    }

    public function show(Collection $collection): View
    {
        Gate::authorize('view', $collection);

        $posts = $collection->posts()
            ->select(['posts.id', 'posts.user_id', 'posts.image', 'posts.caption'])
            ->with('user:id,name,username,avatar')
            ->paginate(18);

        return view('collections.show', compact('collection', 'posts'));
    }

    public function add(Collection $collection): View
    {
        Gate::authorize('update', $collection);

        $addedPostIds = $collection->posts()->pluck('posts.id');

        $availablePosts = auth()->user()->savedPosts()
            ->whereNotIn('posts.id', $addedPostIds)
            ->select(['posts.id', 'posts.image'])
            ->latest('post_saves.created_at')
            ->paginate(24);

        return view('collections.add', compact('collection', 'availablePosts'));
    }

    public function destroy(Collection $collection): RedirectResponse
    {
        Gate::authorize('delete', $collection);

        $collection->delete();

        return to_route('collections.index')->with('success', 'Collection deleted.');
    }
}

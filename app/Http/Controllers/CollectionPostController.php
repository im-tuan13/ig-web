<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CollectionPostController extends Controller
{
    public function store(Collection $collection, Post $post): RedirectResponse
    {
        Gate::authorize('update', $collection);
        Gate::authorize('view', $post);

        // Adding a post to a collection implies saving it.
        $post->savedByUsers()->syncWithoutDetaching([auth()->id()]);

        $collection->posts()->syncWithoutDetaching([$post->id]);

        return back()->with('success', 'Added to '.$collection->name.'.');
    }

    public function destroy(Collection $collection, Post $post): RedirectResponse
    {
        Gate::authorize('update', $collection);

        $collection->posts()->detach($post->id);

        return back()->with('success', 'Removed from '.$collection->name.'.');
    }
}

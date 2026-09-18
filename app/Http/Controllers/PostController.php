<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PostController extends Controller
{
    public function create(): View
    {
        return view('posts.create');
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $files = [];

        if ($request->hasFile('images')) {
            $files = $request->file('images');
        } elseif ($request->hasFile('image')) {
            $files = [$request->file('image')];
        }

        $post = DB::transaction(function () use ($request, $files) {
            $post = $request->user()->posts()->create([
                'image' => '',
                'caption' => $request->validated('caption'),
            ]);

            if ($files !== []) {
                foreach ($files as $index => $file) {
                    $path = $file->store('posts', 'public');

                    $post->images()->create([
                        'path' => $path,
                        'position' => $index,
                    ]);

                    if ($index === 0) {
                        $post->forceFill(['image' => $path])->save();
                    }
                }
            }

            // Parse and attach hashtags from caption
            $tagNames = Post::parseHashtags($post->caption);

            if ($tagNames !== []) {
                $tagIds = [];

                foreach ($tagNames as $name) {
                    $hashtag = Hashtag::firstOrCreate(['name' => $name]);
                    $tagIds[] = $hashtag->id;
                }

                $post->hashtags()->sync($tagIds);
            }

            // Notify mentioned users
            $mentionedUsernames = Post::parseMentions($post->caption);

            if ($mentionedUsernames !== []) {
                User::whereIn('username', $mentionedUsernames)
                    ->where('id', '!=', $post->user_id)
                    ->get()
                    ->each(function (User $mentioned) use ($post): void {
                        $mentioned->notifications()->create([
                            'actor_id' => $post->user_id,
                            'type' => 'mention',
                            'post_id' => $post->id,
                            'data' => ['context' => 'caption'],
                        ]);
                    });
            }

            return $post;
        });

        return to_route('dashboard')->with('success', 'Your post has been shared.');
    }

    public function show(Post $post, Request $request): View|Response
    {
        $user = request()->user();
        Gate::authorize('view', $post);

        $post->load([
            'user:id,name,username,avatar',
            'images' => fn ($query) => $query->orderBy('position')->orderBy('id'),
            'comments' => fn ($query) => $query->with('user:id,name,username,avatar')->oldest(),
            'likedByUsers:id,name,username,avatar',
            'savedByUsers:id,name,username,avatar',
        ])->loadCount(['likedByUsers', 'comments'])
            ->loadExists([
                'likedByUsers as is_liked_by_current_user' => fn ($query) => $query->whereKey($user->id),
                'savedByUsers as is_saved_by_current_user' => fn ($query) => $query->whereKey($user->id),
            ]);

        if ($request->ajax()) {
            return response()->view('posts.modal', compact('post'));
        }

        return view('posts.show', compact('post'));
    }

    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $data = $request->validate([
            'caption' => ['nullable', 'string', 'max:2200'],
        ]);

        $post->update(['caption' => $data['caption'] ?? null]);

        $tagIds = [];
        foreach (Post::parseHashtags($post->caption) as $name) {
            $tagIds[] = Hashtag::firstOrCreate(['name' => $name])->id;
        }
        $post->hashtags()->sync($tagIds);

        return to_route('posts.show', $post)->with('success', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);
        $post->delete();

        return to_route('dashboard')->with('success', 'Post deleted.');
    }
}

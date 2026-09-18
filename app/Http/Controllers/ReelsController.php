<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReelRequest;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReelsController extends Controller
{
    public function index(): View
    {
        $reels = Post::query()
            ->where('is_video', true)
            ->notArchived()
            ->visibleTo(auth()->user())
            ->select(['id', 'user_id', 'image', 'caption', 'created_at', 'is_video'])
            ->with([
                'user:id,name,username,avatar',
                'images' => fn ($query) => $query->orderBy('position')->orderBy('id'),
                'comments' => fn ($query) => $query->with('user:id,name,username,avatar')->oldest(),
            ])
            ->withCount(['likedByUsers', 'comments'])
            ->withExists([
                'likedByUsers as is_liked_by_current_user' => fn ($query) => $query->whereKey(auth()->id()),
                'savedByUsers as is_saved_by_current_user' => fn ($query) => $query->whereKey(auth()->id()),
            ])
            ->latest()
            ->limit(30)
            ->get();

        return view('reels.index', compact('reels'));
    }

    public function create(): View
    {
        return view('reels.create');
    }

    public function store(StoreReelRequest $request): RedirectResponse
    {
        $path = $request->file('video')->store('reels', 'public');

        DB::transaction(function () use ($request, $path): void {
            $reel = $request->user()->posts()->create([
                'image' => $path,
                'caption' => $request->validated('caption'),
                'is_video' => true,
            ]);

            $tagNames = Post::parseHashtags($reel->caption);

            if ($tagNames !== []) {
                $tagIds = [];

                foreach ($tagNames as $name) {
                    $hashtag = Hashtag::firstOrCreate(['name' => $name]);
                    $tagIds[] = $hashtag->id;
                }

                $reel->hashtags()->sync($tagIds);
            }

            $mentionedUsernames = Post::parseMentions($reel->caption);

            if ($mentionedUsernames !== []) {
                User::whereIn('username', $mentionedUsernames)
                    ->where('id', '!=', $reel->user_id)
                    ->get()
                    ->each(function (User $mentioned) use ($reel): void {
                        $mentioned->notifications()->create([
                            'actor_id' => $reel->user_id,
                            'type' => 'mention',
                            'post_id' => $reel->id,
                            'data' => ['context' => 'caption'],
                        ]);
                    });
            }
        });

        return to_route('reels.index')->with('success', 'Your reel has been shared.');
    }
}

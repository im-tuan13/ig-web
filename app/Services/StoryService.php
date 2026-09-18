<?php

namespace App\Services;

use App\Models\Story;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StoryService
{
    public function activeTimeline(User $viewer): Collection
    {
        return Story::query()
            ->active()
            ->visibleTo($viewer)
            ->with('user:id,name,username,avatar')
            ->orderBy('user_id')
            ->orderBy('created_at')
            ->get()
            ->values();
    }

    public function create(User $user, UploadedFile $image): Story
    {
        return $user->stories()->create([
            'image' => $image->store('stories', 'public'),
            'expires_at' => now()->addDay(),
        ]);
    }

    public function markAsViewed(Story $story, User $viewer): void
    {
        $viewedAt = now();

        DB::table('story_views')->insertOrIgnore([
            'story_id' => $story->id,
            'user_id' => $viewer->id,
            'viewed_at' => $viewedAt,
            'created_at' => $viewedAt,
            'updated_at' => $viewedAt,
        ]);
    }

    public function viewersFor(Story $story): Collection
    {
        return $story->viewers()
            ->select('users.id', 'users.name', 'users.username', 'users.avatar')
            ->orderByPivot('viewed_at', 'desc')
            ->get()
            ->each(function (User $viewer): void {
                $viewer->setAttribute(
                    'story_viewed_at_human',
                    Carbon::parse($viewer->pivot->viewed_at)->diffForHumans()
                );
            });
    }

    public function delete(Story $story): void
    {
        DB::transaction(function () use ($story): void {
            $story->viewers()->detach();
            $story->delete();
        });
    }
}

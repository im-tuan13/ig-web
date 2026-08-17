<?php

namespace App\Services;

use App\Models\Highlight;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HighlightService
{
    public function create(User $user, string $title, array $storyIds, ?UploadedFile $cover): Highlight
    {
        $storyIds = array_values(array_unique(array_map('intval', $storyIds)));
        $this->assertStoriesBelongTo($user, $storyIds);

        return DB::transaction(function () use ($user, $title, $storyIds, $cover): Highlight {
            $highlight = $user->highlights()->create([
                'title' => $title,
                'cover_image' => $cover?->store('highlights', 'public'),
            ]);
            $this->addStories($highlight, $storyIds);

            return $highlight;
        });
    }

    public function addStories(Highlight $highlight, array $storyIds): void
    {
        $storyIds = array_values(array_unique(array_map('intval', $storyIds)));
        $highlight->user->stories()->whereKey($storyIds)->update(['highlight_id' => $highlight->id]);
    }

    public function removeStory(Highlight $highlight, int $storyId): void
    {
        $highlight->stories()->whereKey($storyId)->update(['highlight_id' => null]);
    }

    public function update(Highlight $highlight, string $title, array $storyIds, ?UploadedFile $cover = null): Highlight
    {
        $storyIds = array_values(array_unique(array_map('intval', $storyIds)));
        $this->assertStoriesBelongTo($highlight->user, $storyIds);

        return DB::transaction(function () use ($highlight, $title, $storyIds, $cover): Highlight {
            $data = ['title' => $title];
            if ($cover) {
                $oldCover = $highlight->cover_image;
                $data['cover_image'] = $cover->store('highlights', 'public');
                if ($oldCover) {
                    Storage::disk('public')->delete($oldCover);
                }
            }
            $highlight->update($data);
            if ($storyIds === []) {
                $highlight->stories()->update(['highlight_id' => null]);
            } else {
                $highlight->stories()->whereNotIn('id', $storyIds)->update(['highlight_id' => null]);
            }
            $this->addStories($highlight, $storyIds);

            return $highlight->fresh();
        });
    }

    public function delete(Highlight $highlight): void
    {
        DB::transaction(function () use ($highlight): void {
            $cover = $highlight->cover_image;
            $highlight->stories()->update(['highlight_id' => null]);
            $highlight->delete();
            if ($cover) {
                Storage::disk('public')->delete($cover);
            }
        });
    }

    private function assertStoriesBelongTo(User $user, array $storyIds): void
    {
        $owned = $user->stories()->whereKey($storyIds)->pluck('id')->all();
        abort_unless(count($owned) === count($storyIds), 403);
    }
}

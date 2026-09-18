<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Story;
use App\Services\StoryService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StoryViewerController extends Controller
{
    public function reply(Request $request, Story $story): RedirectResponse
    {
        abort_if($story->expires_at->isPast(), 404);
        Gate::authorize('view', $story);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $conversation = Conversation::findOrCreateBetween($request->user(), $story->user);
        $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return redirect()->route('messages.show', $conversation)
            ->with('success', 'Story reply sent.');
    }

    public function show(Request $request, Story $story, StoryService $storyService): View
    {
        abort_if($story->expires_at->isPast(), 404);
        Gate::authorize('view', $story);

        $stories = $storyService->activeTimeline($request->user());

        $currentIndex = $stories->search(
            fn (Story $item) => $item->is($story)
        );

        abort_if($currentIndex === false, 404);

        $story = $stories[$currentIndex];
        $ownerStories = $stories->where('user_id', $story->user_id)->values();
        $currentOwnerStoryIndex = $ownerStories->search(
            fn (Story $item) => $item->is($story)
        );
        $isOwner = $story->user_id === $request->user()->id;

        $storyService->markAsViewed($story, $request->user());

        $previousStory = $stories->get($currentIndex - 1);
        $nextStory = $stories->get($currentIndex + 1);

        return view('stories.show', [
            'story' => $story,
            'stories' => $stories,
            'previousStory' => $previousStory,
            'nextStory' => $nextStory,
            'ownerStories' => $ownerStories,
            'currentOwnerStoryIndex' => $currentOwnerStoryIndex,
            'isOwner' => $isOwner,
            'viewers' => $isOwner ? $storyService->viewersFor($story) : collect(),
        ]);
    }
}

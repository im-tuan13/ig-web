<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Services\StoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoryController extends Controller
{
    public function create(): View
    {
        return view('stories.create');
    }

    public function store(Request $request, StoryService $stories): RedirectResponse
    {
        $request->validate([
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $stories->create($request->user(), $request->file('image'));

        return redirect()
            ->route('dashboard')
            ->with('success', 'Story uploaded successfully.');
    }

    public function destroy(Request $request, Story $story, StoryService $stories): RedirectResponse
    {
        abort_unless($story->user_id === $request->user()->id, 403);

        $stories->delete($story);

        return to_route('dashboard')->with('success', 'Story deleted successfully.');
    }
}

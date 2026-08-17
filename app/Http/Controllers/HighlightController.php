<?php

namespace App\Http\Controllers;

use App\Models\Highlight;
use App\Services\HighlightService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class HighlightController extends Controller
{
    public function create(Request $request): View
    {
        $stories = $request->user()->stories()->latest()->get();
        return view('highlights.create', compact('stories'));
    }

    public function store(Request $request, HighlightService $highlights): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'story_ids' => ['required', 'array', 'min:1'],
            'story_ids.*' => ['integer', 'exists:stories,id'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $highlight = $highlights->create($request->user(), $data['title'], $data['story_ids'], $request->file('cover_image'));
        return to_route('highlights.show', $highlight);
    }

    public function show(Highlight $highlight): View
    {
        Gate::authorize('view', $highlight);
        $highlight->load(['user', 'stories']);
        return view('highlights.show', compact('highlight'));
    }

    public function edit(Highlight $highlight): View
    {
        Gate::authorize('update', $highlight);
        $highlight->load('stories');
        $stories = $highlight->user->stories()->latest()->get();
        return view('highlights.edit', compact('highlight', 'stories'));
    }

    public function update(Request $request, Highlight $highlight, HighlightService $highlights): RedirectResponse
    {
        Gate::authorize('update', $highlight);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'story_ids' => ['required', 'array', 'min:1'],
            'story_ids.*' => ['integer'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $highlights->update($highlight, $data['title'], $data['story_ids'] ?? [], $request->file('cover_image'));
        return to_route('highlights.show', $highlight)->with('success', 'Highlight updated.');
    }

    public function destroy(Highlight $highlight, HighlightService $highlights): RedirectResponse
    {
        Gate::authorize('delete', $highlight);
        $highlights->delete($highlight);
        return to_route('profile.show')->with('success', 'Highlight deleted.');
    }
}

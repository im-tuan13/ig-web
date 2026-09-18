<?php

namespace App\Http\Controllers;

use App\Models\Hashtag;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HashtagController extends Controller
{
    public function __invoke(string $hashtag): View
    {
        $tag = Hashtag::where('name', strtolower($hashtag))->first();

        if (! $tag) {
            throw new NotFoundHttpException();
        }

        $viewer = auth()->user();

        $posts = $tag->posts()
            ->visibleTo($viewer)
            ->notArchived()
            ->select(['id', 'user_id', 'image', 'caption', 'created_at'])
            ->with('user:id,name,username,avatar')
            ->with(['images' => fn ($q) => $q->orderBy('position')->orderBy('id')])
            ->withCount(['likedByUsers', 'comments'])
            ->latest()
            ->paginate(18);

        return view('tags.index', [
            'hashtag' => $tag->name,
            'postsCount' => $tag->posts()->visibleTo($viewer)->notArchived()->count(),
            'posts' => $posts,
        ]);
    }
}

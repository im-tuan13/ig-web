<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(?User $user = null)
    {
        $viewer = Auth::user();
        $user ??= Auth::user();
        $follow = $viewer->following()->whereKey($user->id)->first();
        $followStatus = $follow?->pivot->status;
        $isLocked = $user->is_private
            && ! $user->is($viewer)
            && $followStatus !== 'accepted';

        $user->loadCount([
            'posts' => fn ($query) => $query->whereNull('archived_at'),
            'followers' => fn ($query) => $query->where('follows.status', 'accepted'),
            'following' => fn ($query) => $query->where('follows.status', 'accepted'),
        ]);
        $user->load(['highlights' => fn ($query) => $query->with('stories')->latest()]);
        if (! $isLocked) {
            $user->load(['posts' => fn ($query) => $query
                ->whereNull('archived_at')
                ->latest()
                ->with(['images' => fn ($query) => $query->orderBy('position')->orderBy('id')])
                ->withCount(['likedByUsers', 'comments'])]);
        }

        return view('profile.show', compact('user', 'isLocked', 'followStatus'));
    }

    public function edit()
    {
        return view('profile.edit');
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar
                && ! in_array($user->avatar, ['default.png', 'avatars/default.png'], true)
                && str_starts_with($user->avatar, 'avatars/')) {
                $oldAvatar = public_path($user->avatar);
                if (is_file($oldAvatar)) {
                    unlink($oldAvatar);
                }
            }

            $filename = time().'.'.$request->avatar->extension();
            $request->avatar->move(public_path('avatars'), $filename);
            $user->avatar = 'avatars/'.$filename;
        }

        $data = $request->validated();
        $user->fill(collect($data)->only(['name', 'username', 'email', 'bio', 'is_private'])->all());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile berhasil diperbarui.');
    }

    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function followers(Request $request, User $user): JsonResponse
    {
        $query = trim($request->get('q', ''));

        $followers = $user->followers()
            ->wherePivot('status', 'accepted')
            ->select(['users.id', 'users.name', 'users.username', 'users.avatar'])
            ->when($query !== '', fn ($q) => $q->where(function ($sub) use ($query) {
                $sub->where('users.username', 'like', "%{$query}%")
                    ->orWhere('users.name', 'like', "%{$query}%");
            }))
            ->withExists([
                'followers as is_followed_by_current_user' => fn ($q) => $q
                    ->where('follows.status', 'accepted')
                    ->whereKey(auth()->id()),
            ])
            ->orderBy('users.username')
            ->paginate(20);

        return response()->json($followers);
    }

    public function following(Request $request, User $user): JsonResponse
    {
        $query = trim($request->get('q', ''));

        $following = $user->following()
            ->wherePivot('status', 'accepted')
            ->select(['users.id', 'users.name', 'users.username', 'users.avatar'])
            ->when($query !== '', fn ($q) => $q->where(function ($sub) use ($query) {
                $sub->where('users.username', 'like', "%{$query}%")
                    ->orWhere('users.name', 'like', "%{$query}%");
            }))
            ->withExists([
                'followers as is_followed_by_current_user' => fn ($q) => $q
                    ->where('follows.status', 'accepted')
                    ->whereKey(auth()->id()),
            ])
            ->orderBy('users.username')
            ->paginate(20);

        return response()->json($following);
    }

    public function saved(Request $request): JsonResponse
    {
        $posts = auth()->user()->savedPosts()
            ->select([
                'posts.id',
                'posts.user_id',
                'posts.image',
                'posts.caption',
                'posts.created_at',
            ])
            ->with([
                'user:id,name,username,avatar',
                'images' => fn ($q) => $q->orderBy('position')->orderBy('id'),
            ])
            ->withCount(['likedByUsers', 'comments'])
            ->latest('post_saves.created_at')
            ->paginate(12);

        if ($request->ajax()) {
            $html = view('profile.saved-grid', compact('posts'))->render();

            return response()->json([
                'html' => $html,
                'next_page_url' => $posts->nextPageUrl(),
                'has_more' => $posts->hasMorePages(),
            ]);
        }

        return response()->json($posts);
    }
}

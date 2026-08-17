@props(['notification'])
@php($actor = $notification->actor)
<div class="flex items-center gap-3 rounded-xl px-2 py-3 {{ is_null($notification->read_at) ? 'bg-sky-50/60' : '' }}">
    @if ($actor?->avatar)<img src="{{ asset($actor->avatar) }}" alt="{{ $actor->username }}" class="h-11 w-11 rounded-full object-cover"/>@else<div class="flex h-11 w-11 items-center justify-center rounded-full bg-neutral-200 font-semibold">{{ substr($actor?->name ?? '?', 0, 1) }}</div>@endif
    <p class="min-w-0 flex-1 text-sm text-neutral-700"><a href="{{ $actor ? route('users.show', $actor) : '#' }}" class="font-semibold text-neutral-950">{{ $actor?->username ?? 'Someone' }}</a> {{ $notification->type === 'like' ? 'liked your post.' : ($notification->type === 'comment' ? 'commented on your post.' : ($notification->type === 'follow' ? 'started following you.' : 'interacted with your post.')) }}<span class="ml-1 text-xs text-neutral-400">{{ $notification->created_at->diffForHumans() }}</span></p>
    @if (is_null($notification->read_at))<span class="h-2 w-2 rounded-full bg-sky-500"></span>@endif
</div>

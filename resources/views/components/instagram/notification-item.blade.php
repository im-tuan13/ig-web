@props(['notification'])

@php
    $actor = $notification->actor;
    $post = $notification->post;
    $isUnread = is_null($notification->read_at);
@endphp

<div class="flex items-start gap-3 rounded-xl px-2 py-3 {{ $isUnread ? 'bg-sky-50/60' : '' }}">
    <div class="shrink-0">
        @if ($actor?->avatar)
            <img src="{{ asset($actor->avatar) }}" alt="{{ $actor->username }}" class="h-11 w-11 rounded-full object-cover" />
        @else
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-neutral-200 text-sm font-bold uppercase text-neutral-600">{{ substr($actor?->name ?? '?', 0, 1) }}</div>
        @endif
    </div>

    <div class="min-w-0 flex-1">
        <p class="text-sm text-neutral-700">
            <a href="{{ $actor ? route('users.show', $actor) : '#' }}" class="font-semibold text-neutral-950 hover:underline">{{ $actor?->username ?? 'Someone' }}</a>
            @if ($notification->type === 'like')
                liked your <a href="{{ $post ? route('posts.show', $post) : '#' }}" class="font-semibold text-neutral-950 hover:underline" data-post-modal>post</a>.
            @elseif ($notification->type === 'comment')
                commented on your <a href="{{ $post ? route('posts.show', $post) : '#' }}" class="font-semibold text-neutral-950 hover:underline" data-post-modal>post</a>:
                <span class="mt-1 block italic text-neutral-500">"{{ $notification->data['comment'] ?? '' }}"</span>
            @elseif ($notification->type === 'follow')
                started following you.
            @elseif ($notification->type === 'mention')
                mentioned you in a <a href="{{ $post ? route('posts.show', $post) : '#' }}" class="font-semibold text-neutral-950 hover:underline" data-post-modal>{{ ($notification->data['context'] ?? null) === 'comment' ? 'comment' : 'post' }}</a>.
                @if (($notification->data['context'] ?? null) === 'comment' && !empty($notification->data['comment']))
                    <span class="mt-1 block italic text-neutral-500">"{{ $notification->data['comment'] }}"</span>
                @endif
            @else
                interacted with your post.
            @endif
        </p>
        <p class="mt-0.5 text-xs text-neutral-400">{{ $notification->created_at->diffForHumans() }}</p>
    </div>

    @if ($isUnread)
        <div class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-sky-500"></div>
    @endif

    @if ($post && $post->image)
        <a href="{{ route('posts.show', $post) }}" data-post-modal class="shrink-0">
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->image) }}" alt="Post" class="h-11 w-11 rounded-lg object-cover" />
        </a>
    @endif
</div>

@props(['message', 'currentUser', 'group' => false])

@php
    $mine = $message->user_id === $currentUser->id;
    $stickers = ['heart' => '💖', 'laugh' => '😂', 'party' => '🎉', 'fire' => '🔥', 'cat' => '😺'];
@endphp

<div class="mb-3 flex items-end gap-2 {{ $mine ? 'justify-end' : 'justify-start' }}">
    @if (! $mine)
        @if ($message->user->avatar)
            <img src="{{ asset($message->user->avatar) }}" alt="" class="mb-5 h-7 w-7 shrink-0 rounded-full object-cover" />
        @else
            <div class="mb-5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-neutral-200 text-[10px] font-bold uppercase text-neutral-600">{{ substr($message->user->name, 0, 1) }}</div>
        @endif
    @endif
    <div @class([
        'max-w-[78%] rounded-[22px] px-4 py-2.5 text-sm leading-relaxed sm:max-w-[70%]',
        'bg-[#0095f6] text-white' => $mine && $message->type !== 'sticker',
        'border border-neutral-200 bg-white text-neutral-900' => ! $mine && $message->type !== 'sticker',
        'bg-transparent px-1 py-0 text-5xl' => $message->type === 'sticker',
    ])>
        @if ($group && ! $mine)<p class="mb-0.5 text-xs font-semibold text-sky-600">{{ $message->user->username }}</p>@endif
        @if ($message->type === 'sticker')
            <span aria-label="Sticker">{{ $stickers[$message->sticker_key] ?? '✨' }}</span>
        @else
            @if ($message->post_id)
                @if ($message->post)
                    <a href="{{ route('posts.show', $message->post) }}" data-post-modal class="mb-1.5 block w-56 max-w-full overflow-hidden rounded-xl border {{ $mine ? 'border-white/30' : 'border-neutral-200' }}">
                        <img src="{{ $message->post->image_url }}" alt="Shared post" class="aspect-square w-full object-cover">
                        @if ($message->post->caption)<span class="block px-2.5 py-1.5 text-xs {{ $mine ? 'text-white/90' : 'text-neutral-700' }}">{{ \Illuminate\Support\Str::limit($message->post->caption, 60) }}</span>@endif
                    </a>
                @else
                    <p class="mb-1 text-xs italic {{ $mine ? 'text-white/70' : 'text-neutral-500' }}">This post is no longer available.</p>
                @endif
            @endif
            @if ($message->story_id)<p class="mb-1 text-xs italic {{ $mine ? 'text-white/70' : 'text-neutral-500' }}">{{ $mine ? 'You replied to a story' : 'Replied to your story' }}</p>@endif
            @if (! $message->message)
                <p class="sr-only">Shared post</p>
            @else
            <p>{{ $message->message }}</p>
            @endif
            <p class="mt-0.5 text-right text-[10px] {{ $mine ? 'text-white/70' : 'text-neutral-400' }}">{{ $message->created_at->format('g:i A') }}</p>
        @endif
    </div>
</div>

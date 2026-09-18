@props(['post'])

@php
    $fallback = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600"><rect width="100%" height="100%" fill="#f1f1f1"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#777" font-family="Arial" font-size="24">Photo unavailable</text></svg>');
    $thumbnail = $post->images->isNotEmpty()
        ? asset('storage/'.$post->images->first()->path)
        : (filled($post->image) ? (str_starts_with($post->image, 'http') || str_starts_with($post->image, 'uploads/') ? $post->image_url : asset('storage/'.$post->image)) : $fallback);
@endphp

<a href="{{ route('posts.show', $post) }}" data-post-id="{{ $post->id }}" data-post-modal class="group relative block aspect-square overflow-hidden bg-neutral-100">
    <img src="{{ $thumbnail }}" alt="Post by {{ $post->user->username }}" class="h-full w-full object-cover transition duration-200 group-hover:scale-105" onerror="this.onerror=null;this.src='{{ $fallback }}'" />

    @if ($post->images->count() > 1)
        <div class="absolute right-2 top-2 flex items-center gap-1 rounded-full bg-black/70 px-2 py-1 text-[11px] font-semibold text-white">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <rect x="3" y="4" width="14" height="14" rx="2" />
                <path d="M7 20h10a2 2 0 0 0 2-2V8" />
            </svg>
            <span>{{ $post->images->count() }}</span>
        </div>
    @endif

    <div class="absolute inset-0 hidden items-center justify-center gap-6 bg-black/30 text-sm font-semibold text-white group-hover:flex">
        <span class="flex items-center gap-1.5">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M20.8 4.6c-1.8-1.8-4.8-1.8-6.6 0L12 6.8 9.8 4.6C8 2.8 5 2.8 3.2 4.6s-1.8 4.8 0 6.6L12 20l8.8-8.8c1.8-1.8 1.8-4.8 0-6.6z" />
            </svg>
            {{ $post->liked_by_users_count }}
        </span>
        <span class="flex items-center gap-1.5">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.8 8.8 0 0 1-3.5-.7L3 21l1.8-5.1a8 8 0 0 1-1-4.4 8.4 8.4 0 0 1 17.2 0z" />
            </svg>
            {{ $post->comments_count }}
        </span>
    </div>
</a>

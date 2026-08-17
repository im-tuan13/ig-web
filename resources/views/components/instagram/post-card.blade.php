@props(['post', 'modal' => false])

@php
    $fallbackImage = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="1080" height="1080"><rect width="100%" height="100%" fill="#f1f1f1"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#777" font-family="Arial" font-size="44">Photo unavailable</text></svg>');
    $singleImageUrl = filled($post->image)
        ? (str_starts_with($post->image, 'http') || str_starts_with($post->image, 'uploads/') ? $post->image_url : asset('storage/'.$post->image))
        : $fallbackImage;
    $carouselImages = $post->images->isNotEmpty()
        ? $post->images->map(fn ($image) => ['url' => asset('storage/'.$image->path)])->values()->all()
        : [['url' => $singleImageUrl]];
@endphp

<article x-data="{ ...postLike({{ $post->id }}, {{ $post->liked_by_users_count }}, {{ $post->is_liked_by_current_user ? 'true' : 'false' }}), ...postComment({{ $post->id }}, {{ Js::from($post->comments->map(fn ($comment) => ['id' => $comment->id, 'body' => $comment->comment, 'canDelete' => auth()->id() === $comment->user_id || auth()->id() === $post->user_id, 'user' => ['username' => $comment->user->username, 'avatar' => $comment->user->avatar]])) }}, {{ $post->comments_count }}), ...postSave({{ $post->id }}, {{ $post->is_saved_by_current_user ? 'true' : 'false' }}), ...postShare({{ $post->id }}) }" @class([
    'overflow-hidden border-y border-outline-variant bg-white pb-4 sm:rounded-lg sm:border',
    'flex h-full min-h-0 flex-col rounded-none border-0 shadow-none md:grid md:grid-cols-[minmax(0,65%)_minmax(320px,35%)] md:grid-rows-[auto_minmax(0,1fr)] md:rounded-3xl md:border' => $modal,
])>
    {{-- Header --}}
    <div @class([
        'flex items-center gap-3 px-4 py-3',
        'order-2 shrink-0 border-b border-neutral-200 px-4 py-3 md:col-start-2 md:row-start-1 md:border-t-0' => $modal,
    ])>
        <a href="{{ route('users.show', $post->user) }}" class="shrink-0">
            @if ($post->user->avatar)
                <img src="{{ asset($post->user->avatar) }}" alt="{{ $post->user->username }}" class="h-8 w-8 rounded-full object-cover" />
            @else
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-neutral-200 text-xs font-bold uppercase text-neutral-700">{{ substr($post->user->name, 0, 1) }}</div>
            @endif
        </a>
        <div class="min-w-0">
            <a href="{{ route('users.show', $post->user) }}" class="truncate text-label-md text-on-surface hover:underline">{{ $post->user->username }}</a>
            <div class="truncate text-body-sm text-on-surface-variant">{{ $post->created_at->diffForHumans() }}</div>
        </div>
        @if ($post->user_id === auth()->id())
            <a href="{{ route('posts.edit', $post) }}" class="ml-auto rounded px-2 py-1 text-xs font-semibold text-sky-600 hover:bg-sky-50">Edit</a>
            <form action="{{ route('posts.archive.store', $post) }}" method="POST">
                @csrf
                <button type="submit" class="rounded px-2 py-1 text-xs font-semibold text-neutral-600 hover:bg-neutral-100">Archive</button>
            </form>
            <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete this post?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Delete</button>
            </form>
        @else
        <button type="button" class="ml-auto rounded-full p-1.5 text-neutral-500 transition hover:bg-neutral-100" aria-label="More options">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <circle cx="5" cy="12" r="1.7" />
                <circle cx="12" cy="12" r="1.7" />
                <circle cx="19" cy="12" r="1.7" />
            </svg>
        </button>
        @endif
    </div>

    {{-- Image --}}
    @if (count($carouselImages) > 1)
        <div class="relative flex aspect-square items-center justify-center overflow-hidden bg-black md:col-start-1 md:row-span-2"
            x-data="{
                isModal: @js($modal),
                images: @js($carouselImages),
                current: 0,
                startX: 0,
                next() { if (this.images.length <= 1) return; this.current = (this.current + 1) % this.images.length; },
                prev() { if (this.images.length <= 1) return; this.current = (this.current - 1 + this.images.length) % this.images.length; },
                onKeydown(event) { if (event.key === 'ArrowRight') { event.preventDefault(); this.next(); } if (event.key === 'ArrowLeft') { event.preventDefault(); this.prev(); } },
                onTouchStart(event) { this.startX = event.touches[0].clientX; },
                onTouchEnd(event) { const delta = event.changedTouches[0].clientX - this.startX; if (delta > 50) this.prev(); if (delta < -50) this.next(); }
            }" @keydown.window="onKeydown($event)">
            <a href="{{ route('posts.show', $post) }}" data-post-modal class="block h-full w-full">
                <template x-for="(image, index) in images" :key="index">
                    <img x-show="current === index" :src="image.url" :alt="'Post by {{ $post->user->username }}'" :loading="index === 0 ? 'eager' : 'lazy'" class="h-full w-full object-cover" x-cloak />
                </template>
            </a>

            <button type="button" @click.stop.prevent="prev()" class="absolute left-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-black/45 text-white shadow-sm transition hover:bg-black/70" aria-label="Previous image">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M15 18 9 12l6-6" />
                </svg>
            </button>
            <button type="button" @click.stop.prevent="next()" class="absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-black/45 text-white shadow-sm transition hover:bg-black/70" aria-label="Next image">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M9 18l6-6-6-6" />
                </svg>
            </button>

            <div class="absolute inset-x-0 bottom-3 flex items-center justify-center gap-1.5">
                <template x-for="(image, index) in images" :key="index">
                    <button type="button" class="h-2.5 w-2.5 rounded-full transition" :class="current === index ? 'bg-white' : 'bg-white/50'" @click="current = index" :aria-label="`Go to image ${index + 1}`"></button>
                </template>
            </div>
        </div>
    @else
        <a href="{{ route('posts.show', $post) }}" data-post-modal>
            <img src="{{ $singleImageUrl }}" alt="Post by {{ $post->user->username }}" class="aspect-square w-full object-cover" onerror="this.onerror=null;this.src='{{ $fallbackImage }}'" />
        </a>
    @endif

    {{-- Actions --}}
    <div @class([
        'space-y-2 px-4 pt-3',
        'order-3 flex min-h-0 flex-1 flex-col gap-3 overflow-hidden px-4 py-3 md:col-start-2 md:row-start-2' => $modal,
    ])>
        <x-instagram.post-actions :modal="$modal" />
        <div @class([
            'hidden',
            'flex items-center gap-3 text-neutral-700',
            'order-2 shrink-0' => $modal,
        ])>
            <button type="button" @click="toggle" :aria-label="liked ? 'Unlike' : 'Like'" :disabled="loading" class="rounded-full p-1.5 transition hover:bg-neutral-100 disabled:opacity-50">
                <svg class="h-6 w-6" viewBox="0 0 24 24" :fill="liked ? 'currentColor' : 'none'" :class="liked ? 'text-red-500' : ''" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M20.8 4.6c-1.8-1.8-4.8-1.8-6.6 0L12 6.8 9.8 4.6C8 2.8 5 2.8 3.2 4.6s-1.8 4.8 0 6.6L12 20l8.8-8.8c1.8-1.8 1.8-4.8 0-6.6z" />
                </svg>
            </button>
            <button type="button" @click="$refs.commentInput.focus()" aria-label="Comment" class="rounded-full p-1.5 transition hover:bg-neutral-100">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.8 8.8 0 0 1-3.5-.7L3 21l1.8-5.1a8 8 0 0 1-1-4.4 8.4 8.4 0 0 1 17.2 0z" />
                </svg>
            </button>
            <button type="button" @click="sharePost" aria-label="Share" class="rounded-full p-1.5 transition hover:bg-neutral-100">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="m22 2-7 20-4-9-9-4z" />
                    <path d="M22 2 11 13" />
                </svg>
            </button>
            <button type="button" @click="toggleSave" :aria-label="saved ? 'Unsave' : 'Save'" :disabled="saveLoading" class="ml-auto rounded-full p-1.5 transition hover:bg-neutral-100 disabled:opacity-50">
                <svg class="h-6 w-6" viewBox="0 0 24 24" :fill="saved ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M6 3h12v18l-6-4-6 4z" />
                </svg>
            </button>
        </div>

        <div @class([
            'text-sm font-semibold text-neutral-950',
            'order-3 shrink-0' => $modal,
        ]) x-text="`${likesCount} ${likesCount === 1 ? 'like' : 'likes'}`">
            {{ $post->liked_by_users_count }} {{ \Illuminate\Support\Str::plural('like', $post->liked_by_users_count) }}
        </div>

        @if ($post->caption)
            <p @class([
                'text-sm text-neutral-700',
                'order-4 shrink-0' => $modal,
            ])>
                <span class="font-semibold text-neutral-950"><a href="{{ route('users.show', $post->user) }}" class="hover:underline">{{ $post->user->username }}</a></span>
                <x-instagram.hashtag-caption :caption="$post->caption" />
            </p>
        @endif

        <div x-show="comments.length" @class([
            'space-y-1 text-sm text-neutral-700',
            'order-1 min-h-0 flex-1 overflow-y-auto pr-2' => $modal,
        ])>
            <template x-for="commentItem in comments" :key="commentItem.id">
                <p class="flex items-start gap-2"><span class="min-w-0 flex-1"><span class="font-semibold text-neutral-950" x-text="commentItem.user.username"></span> <span x-text="commentItem.body"></span></span><button x-show="commentItem.canDelete" type="button" @click="deleteComment(commentItem.id)" class="shrink-0 text-[11px] text-neutral-400 hover:text-red-600" aria-label="Delete comment">Delete</button></p>
            </template>
        </div>

        <form @submit.prevent="submitComment" @class([
            'flex items-center gap-3 border-t border-neutral-100 pt-2',
            'order-6 shrink-0' => $modal,
        ])>
            <input x-ref="commentInput" x-model="comment" type="text" maxlength="2200" placeholder="Add a comment..." :disabled="commentLoading" class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-neutral-950 placeholder:text-neutral-400 focus:ring-0 disabled:opacity-50">
            <button type="submit" :disabled="!comment.trim() || commentLoading" class="text-sm font-semibold text-sky-500 disabled:opacity-50">Post</button>
        </form>

        <p x-show="commentsCount" @class([
            'text-xs text-neutral-500',
            'order-5 shrink-0' => $modal,
        ]) x-text="`${commentsCount} ${commentsCount === 1 ? 'comment' : 'comments'}`">
            {{ $post->comments_count }} {{ \Illuminate\Support\Str::plural('comment', $post->comments_count) }}
        </p>
    </div>
</article>

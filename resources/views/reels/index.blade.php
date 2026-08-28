@php
    /** @var \Illuminate\Support\Collection $reels */

@endphp

<x-app-layout>
    <div
        x-data="reelsPlayer({{ Js::from($reels->map(fn ($r) => [
            'id' => $r->id,
            'isVideo' => $r->is_video,
        ])->all()) }})"
        class="flex min-h-screen flex-col items-center bg-black">

        @if ($reels->isNotEmpty())

            @foreach ($reels as $index => $reel)
                @php
                    $mediaUrl = $reel->images->isNotEmpty()
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($reel->images->first()->path)
                        : $reel->image_url;

                    $isVideo = $reel->is_video;
                @endphp

                <section
                    data-reel-index="{{ $index }}"
                    x-data="{ ...postLike({{ $reel->id }}, {{ $reel->liked_by_users_count }}, {{ $reel->is_liked_by_current_user ? 'true' : 'false' }}), ...postComment({{ $reel->id }}, {{ Js::from($reel->comments->map(fn ($comment) => ['id' => $comment->id, 'body' => $comment->comment, 'user' => ['username' => $comment->user->username, 'avatar' => $comment->user->avatar]])) }}, {{ $reel->comments_count }}), ...postShare({{ $reel->id }}), commentOpen: false }"
                    x-ref="reel-{{ $index }}"
                    x-intersect="onReelVisible({{ $index }})"
                    x-intersect:leave="onReelHidden({{ $index }})"
                    class="relative flex h-screen w-full max-w-[1240px] items-center justify-center px-0 lg:px-16">

                    {{-- Media Container (9:16) --}}
                    <div class="relative flex h-full max-h-[90vh] w-full max-w-[506px] items-center justify-center overflow-hidden">
                        <a href="{{ route('posts.show', $reel) }}" data-post-modal class="block h-full w-full">
                            @if ($isVideo)
                                <div
                                    x-data="videoPlayer({{ $index }})"
                                    class="relative h-full w-full"
                                    x-cloak>
                                    {{-- Video element --}}
                                    <video
                                        x-ref="video"
                                        src="{{ $mediaUrl }}"
                                        class="h-full w-full object-contain opacity-0 transition-opacity duration-500"
                                        :class="{ 'opacity-100': loaded }"
                                        preload="metadata"
                                        autoplay
                                        playsinline
                                        muted
                                        @loadedmetadata="onMetadata()"
                                        @loadeddata="onLoaded()"
                                        x-on:error="onError($event)"
                                        @waiting="loading = true"
                                        @canplay="onLoaded()"
                                        @play="playing = true"
                                        @pause="playing = false"
                                        @click="togglePlay">
                                    </video>

                                    {{-- Loading spinner --}}
                                    <div
                                        x-show="loading"
                                        class="absolute inset-0 flex items-center justify-center">
                                        <div class="h-8 w-8 animate-spin rounded-full border-2 border-white/30 border-t-white"></div>
                                    </div>

                                    {{-- Play/Pause overlay --}}
                                    <div
                                        x-show="!playing && loaded"
                                        x-transition.opacity.duration.200ms
                                        class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-black/50">
                                                <svg class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                    <path d="M8 5.5v13l11-6.5z" />
                                                </svg>
                                            </div>
                                            <div
                                                x-show="mediaErrorCode !== null || loadTimedOut"
                                                x-cloak
                                                class="max-w-[90%] rounded bg-black/75 px-3 py-2 text-center text-xs text-red-200">
                                                <template x-if="mediaErrorCode !== null">
                                                    <span><span>Error: </span><span x-text="mediaErrorCode"></span><span> — </span><span x-text="mediaErrorMessage || 'Unknown media error'"></span></span>
                                                </template>
                                                <template x-if="loadTimedOut">
                                                    <span>Video failed to load. Please try again.</span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Mute/Unmute button --}}
                                    <button
                                        @click.stop="toggleMute"
                                        class="absolute bottom-3 right-3 flex h-8 w-8 items-center justify-center rounded-full bg-black/50 text-white transition hover:bg-black/70"
                                        aria-label="Toggle sound">
                                        <svg x-show="muted" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M11 5 6 9H2v6h4l5 4zM23 9l-6 6M17 9l6 6" />
                                        </svg>
                                        <svg x-show="!muted" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M11 5 6 9H2v6h4l5 4zM19.1 4.9a10 10 0 0 1 0 14.2M15.5 8.5a5 5 0 0 1 0 7" />
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <img
                                    src="{{ $mediaUrl }}"
                                    alt="Reel by {{ $reel->user->username }}"
                                    class="h-full w-full object-contain"
                                    loading="lazy" />
                            @endif
                        </a>

                        {{-- Bottom overlay: user info + caption --}}
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent p-5 pt-16">
                            <div class="pointer-events-auto flex items-center gap-3">
                                <a href="{{ route('users.show', $reel->user) }}" class="shrink-0">
                                    @if ($reel->user->avatar)
                                        <img src="{{ asset($reel->user->avatar) }}" alt="{{ $reel->user->username }}" class="h-8 w-8 rounded-full border-2 border-white object-cover" />
                                    @else
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-neutral-600 text-[10px] font-bold uppercase text-white">
                                            {{ substr($reel->user->name, 0, 1) }}
                                        </div>
                                    @endif
                                </a>
                                <a href="{{ route('users.show', $reel->user) }}" class="truncate text-sm font-semibold text-white hover:underline">
                                    {{ $reel->user->username }}
                                </a>

                                @if ($reel->caption)
                                    <span class="ml-1 truncate text-sm text-neutral-300">
                                        {{ \Illuminate\Support\Str::limit($reel->caption, 60) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Sticky vertical action bar (right side) --}}
                    <div class="hidden flex-col items-center gap-5 text-white lg:flex">
                        {{-- Like --}}
                        <button type="button" @click.stop.prevent="toggle" :disabled="loading" class="group flex flex-col items-center gap-1 disabled:opacity-50" :aria-label="liked ? 'Unlike' : 'Like'">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-neutral-800 transition group-hover:bg-neutral-700">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" :fill="liked ? 'currentColor' : 'none'" :class="liked ? 'text-red-500' : ''" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M20.8 4.6c-1.8-1.8-4.8-1.8-6.6 0L12 6.8 9.8 4.6C8 2.8 5 2.8 3.2 4.6s-1.8 4.8 0 6.6L12 20l8.8-8.8c1.8-1.8 1.8-4.8 0-6.6z" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-neutral-400" x-text="likesCount > 0 ? likesCount : ''">{{ $reel->liked_by_users_count > 0 ? $reel->liked_by_users_count : '' }}</span>
                        </button>
                        {{-- Comment --}}
                        <button type="button" @click.stop.prevent="commentOpen = !commentOpen" class="group flex flex-col items-center gap-1" :aria-label="commentOpen ? 'Close comments' : 'Comment'">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-neutral-800 transition group-hover:bg-neutral-700">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.8 8.8 0 0 1-3.5-.7L3 21l1.8-5.1a8 8 0 0 1-1-4.4 8.4 8.4 0 0 1 17.2 0z" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-neutral-400" x-text="commentsCount > 0 ? commentsCount : ''">{{ $reel->comments_count > 0 ? $reel->comments_count : '' }}</span>
                        </button>
                        {{-- Share --}}
                        <button type="button" @click.stop.prevent="sharePost" class="group flex flex-col items-center gap-1" aria-label="Share">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-neutral-800 transition group-hover:bg-neutral-700">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m22 2-7 20-4-9-9-4z" />
                                    <path d="M22 2 11 13" />
                                </svg>
                            </div>
                        </button>
                        {{-- Save --}}
                        <a href="{{ route('posts.show', $reel) }}" data-post-modal class="group flex flex-col items-center gap-1">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-neutral-800 transition group-hover:bg-neutral-700">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M6 3h12v18l-6-4-6 4z" />
                                </svg>
                            </div>
                        </a>
                    </div>

                    <div x-show="commentOpen" x-cloak @click.stop class="absolute right-4 top-1/2 z-10 w-[min(90vw,360px)] -translate-y-1/2 rounded-2xl bg-white p-4 text-neutral-900 shadow-2xl">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-semibold">Comments</h3>
                            <button type="button" @click="commentOpen = false" class="rounded-full px-2 py-1 text-neutral-500 hover:bg-neutral-100" aria-label="Close comments">&times;</button>
                        </div>
                        <div class="max-h-56 space-y-2 overflow-y-auto text-sm">
                            <template x-for="commentItem in comments" :key="commentItem.id">
                                <p><span class="font-semibold" x-text="commentItem.user.username"></span> <span x-text="commentItem.body"></span></p>
                            </template>
                            <p x-show="comments.length === 0" class="text-neutral-500">No comments yet.</p>
                        </div>
                        <form class="mt-3 flex gap-2 border-t border-neutral-200 pt-3" @submit.prevent="submitComment">
                            <input x-model="comment" type="text" maxlength="2200" placeholder="Add a comment..." :disabled="commentLoading" class="min-w-0 flex-1 rounded-lg border-neutral-300 text-sm focus:border-sky-500 focus:ring-sky-500 disabled:opacity-50">
                            <button type="submit" :disabled="!comment.trim() || commentLoading" class="rounded-lg bg-sky-500 px-3 py-2 text-sm font-semibold text-white disabled:opacity-50">Post</button>
                        </form>
                    </div>
                </section>
            @endforeach

        @else
            {{-- Empty State --}}
            <div class="flex min-h-screen w-full items-center justify-center px-4">
                <div class="flex w-full max-w-sm flex-col items-center rounded-2xl border border-neutral-800 bg-neutral-900 px-6 py-16 text-center">
                    <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-full border-2 border-neutral-600">
                        <svg class="h-7 w-7 text-neutral-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M8 5.5v13l11-6.5z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-white">No Reels Yet</h2>
                    <p class="mt-1.5 text-sm text-neutral-400">When posts are shared, reels will appear here.</p>
                    <a href="{{ route('reels.create') }}" class="mt-6 rounded-full bg-sky-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-600">
                        Share your first reel
                    </a>
                </div>
            </div>
        @endif

    </div>

    @once
        @push('scripts')
            <script>
                document.addEventListener('alpine:init', () => {
                    // ── Parent controller: IntersectionObserver per reel ──
                    Alpine.data('reelsPlayer', (reels) => ({
                        reels,
                        activeIndex: null,
                        observer: null,

                        init() {
                            this.observer = new IntersectionObserver(
                                (entries) => {
                                    entries.forEach((entry) => {
                                        const index = parseInt(entry.target.dataset.reelIndex);
                                        if (entry.isIntersecting) {
                                            this.onReelVisible(index);
                                        } else {
                                            this.onReelHidden(index);
                                        }
                                    });
                                },
                                { threshold: 0.7 }
                            );

                            this.$nextTick(() => {
                                this.$el.querySelectorAll('[data-reel-index]').forEach((el) => {
                                    this.observer.observe(el);
                                });
                            });
                        },

                        destroy() {
                            this.observer?.disconnect();
                        },

                        onReelVisible(index) {
                            this.activeIndex = index;
                        },

                        onReelHidden(index) {
                            if (this.activeIndex === index) {
                                this.activeIndex = null;
                            }
                        },
                    }));

                    // ── Video player per reel ──
                    Alpine.data('videoPlayer', (index) => ({
                        playing: false,
                        muted: true,
                        loaded: false,
                        loading: true,
                        mediaErrorCode: null,
                        mediaErrorMessage: '',
                        loadTimedOut: false,
                        loadTimeout: null,

                        init() {
                            this.loadTimeout = setTimeout(() => {
                                this.loading = false;
                                this.loaded = true;
                                this.loadTimedOut = true;
                                console.error('Reels video loading timed out:', {
                                    currentSrc: this.$refs.video?.currentSrc,
                                });
                            }, 8000);
                        },

                        destroy() {
                            clearTimeout(this.loadTimeout);
                        },

                        clearLoadTimeout() {
                            clearTimeout(this.loadTimeout);
                            this.loadTimeout = null;
                        },

                        onMetadata() {
                            this.loading = false;
                            this.loaded = true;
                        },

                        onLoaded() {
                            this.clearLoadTimeout();
                            this.loading = false;
                            this.loaded = true;
                            this.loadTimedOut = false;
                        },

                        onError(event) {
                            const mediaError = event.target.error;
                            this.clearLoadTimeout();
                            this.loading = false;
                            this.loaded = true;
                            this.mediaErrorCode = mediaError?.code ?? null;
                            this.mediaErrorMessage = mediaError?.message ?? '';
                            console.error('Reels video failed to load:', {
                                code: this.mediaErrorCode,
                                message: this.mediaErrorMessage,
                                currentSrc: event.target.currentSrc,
                            });
                        },

                        togglePlay() {
                            const video = this.$refs.video;
                            if (!video) return;

                            if (video.paused) {
                                video.play().catch((error) => {
                                    this.loading = false;
                                    this.loaded = true;
                                    this.playing = false;
                                    console.error('Reels video playback failed:', error);
                                });
                            } else {
                                video.pause();
                                this.playing = false;
                            }
                        },

                        toggleMute() {
                            const video = this.$refs.video;
                            if (!video) return;
                            video.muted = !video.muted;
                            this.muted = video.muted;
                        },
                    }));
                });
            </script>
        @endpush
    @endonce

</x-app-layout>

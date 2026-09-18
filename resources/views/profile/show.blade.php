<x-app-layout>
    <div class="ig-page">
        @if (session('success'))
            <div class="mb-6 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <x-instagram.profile-header :user="$user" :follow-status="$followStatus" />

        @if ($isLocked)
            <section class="border-y border-neutral-200 py-20 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full border-2 border-neutral-900">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" /><path d="M8 11h8M8 15h5" /></svg>
                </div>
                <h2 class="text-xl font-semibold text-neutral-900">This Account is Private</h2>
                <p class="mt-2 text-sm text-neutral-500">Follow this account to see their photos and videos.</p>
            </section>
        @else
        {{-- Story Highlights --}}
        <section class="mb-8 flex gap-5 overflow-x-auto py-7">
            @if ($user->is(auth()->user()))
                <a href="{{ route('highlights.create') }}" class="flex w-20 shrink-0 flex-col items-center gap-2 text-xs">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full border-2 border-dashed border-neutral-400 text-2xl">+</span>
                    <span>New</span>
                </a>
            @endif
            @foreach($user->highlights as $highlight)
                <a href="{{ route('highlights.show', $highlight) }}" class="flex w-20 shrink-0 flex-col items-center gap-2 text-xs">
                    @if($highlight->cover_url)<img src="{{ $highlight->cover_url }}" class="h-16 w-16 rounded-full border object-cover">@else<span class="h-16 w-16 rounded-full border bg-neutral-100"></span>@endif
                    <span class="max-w-20 truncate">{{ $highlight->title }}</span>
                </a>
            @endforeach
        </section>

        @endif

        @if (! $isLocked)
        {{-- Tabs --}}
        <div
            x-data="profileTabs"
            class="border-t border-outline-variant">

            <div class="flex justify-center">
                <button
                    type="button"
                    @click="switchTab('posts')"
                    class="flex h-12 items-center gap-1.5 px-4 text-xs font-semibold uppercase tracking-wider -mt-px transition"
                    :class="activeTab === 'posts' ? 'border-t border-neutral-900 text-neutral-900' : 'text-neutral-500'">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" :fill="activeTab === 'posts' ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="3" width="8" height="8" />
                        <rect x="13" y="3" width="8" height="8" />
                        <rect x="3" y="13" width="8" height="8" />
                        <rect x="13" y="13" width="8" height="8" />
                    </svg>
                    Posts
                </button>

                @if ($user->is(auth()->user()))
                <button
                    type="button"
                    @click="switchTab('saved')"
                    class="flex h-12 items-center gap-1.5 px-4 text-xs font-semibold uppercase tracking-wider -mt-px transition"
                    :class="activeTab === 'saved' ? 'border-t border-neutral-900 text-neutral-900' : 'text-neutral-500'">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" :fill="activeTab === 'saved' ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M6 3h12v18l-6-4-6 4z" />
                    </svg>
                    Saved
                </button>
                @endif

                <a href="#" class="flex h-12 items-center gap-1.5 px-4 text-xs font-semibold uppercase tracking-wider text-neutral-500 -mt-px">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M7 21a4 4 0 0 1-4-4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v12a4 4 0 0 1-4 4H7z" />
                        <path d="M15 3h2a2 2 0 0 1 2 2v12a4 4 0 0 1-4 4H7" />
                    </svg>
                    Tagged
                </a>
            </div>

            {{-- Posts Grid Tab --}}
            <section x-show="activeTab === 'posts'" class="grid grid-cols-3 gap-1 sm:gap-6">
                @forelse($user->posts as $post)
                    <x-instagram.post-grid-item :post="$post" />
                @empty
                    <div class="col-span-3 py-20 text-center">
                        <div class="mx-auto mb-6 flex h-[62px] w-[62px] items-center justify-center rounded-full border-2 border-neutral-900">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="5" />
                                <path d="M9 14.5 11 17l4-5" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-neutral-900">No Posts Yet</h2>
                        <p class="mt-2 text-sm text-neutral-500">When you share a photo, it will appear on your profile.</p>
                        @if ($user->is(auth()->user()))
                            <a href="{{ route('posts.create') }}" class="mt-4 inline-block text-sm font-semibold text-sky-500">Share your first photo</a>
                        @endif
                    </div>
                @endforelse
            </section>

            {{-- Saved Grid Tab --}}
            @if ($user->is(auth()->user()))
            <section x-show="activeTab === 'saved'" x-cloak>
                <div class="mb-4 flex justify-end">
                    <a href="{{ route('collections.index') }}" class="text-sm font-semibold text-sky-500 hover:underline">View collections</a>
                </div>
                {{-- Loading initial --}}
                <div x-show="savedLoading && savedPosts.length === 0" class="flex justify-center py-20">
                    <div class="h-6 w-6 animate-spin rounded-full border-2 border-neutral-200 border-t-neutral-900"></div>
                </div>

                {{-- Saved posts grid --}}
                <div
                    id="saved-grid"
                    x-show="!savedLoading || savedPosts.length > 0"
                    class="grid grid-cols-3 gap-[2px] sm:gap-[3px]">
                    <template x-for="post in savedPosts" :key="post.id">
                        <div x-html="post.html"></div>
                    </template>
                </div>

                {{-- Empty saved state --}}
                <div x-show="!savedLoading && savedPosts.length === 0" class="py-20 text-center">
                    <div class="mx-auto mb-6 flex h-[62px] w-[62px] items-center justify-center rounded-full border-2 border-neutral-900">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M6 3h12v18l-6-4-6 4z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-neutral-900">Save photos</h2>
                    <p class="mt-2 text-sm text-neutral-500">When you save photos, they'll appear here.</p>
                </div>

                {{-- Sentinel for infinite scroll --}}
                <div
                    x-ref="savedSentinel"
                    x-intersect="loadMoreSaved()"
                    class="h-px"
                    x-show="savedHasMore && !savedLoading"
                    aria-hidden="true">
                </div>

                {{-- Load more spinner --}}
                <div x-show="savedLoading && savedPosts.length > 0" class="flex justify-center py-6">
                    <div class="h-5 w-5 animate-spin rounded-full border-2 border-neutral-200 border-t-neutral-900"></div>
                </div>
            </section>
            @endif

        </div>
        @endif
    </div>

    <x-instagram.relationships-modal />
</x-app-layout>

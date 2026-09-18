<x-app-layout>
    <div class="ig-page px-0 py-5 sm:px-4">

        @if (session('success'))
            <div role="status"
                class="mx-auto mb-6 max-w-[470px] rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-center gap-10">

            {{-- Feed --}}
            <section class="min-w-0 w-full max-w-[470px]">

                @if ($stories->isNotEmpty())
                    <div class="mb-5 overflow-x-auto border-y border-outline-variant bg-white px-4 py-4 sm:rounded-lg sm:border [scrollbar-width:none]">
                        <div class="flex gap-4">
                            @foreach ($stories as $story)
                                <x-instagram.story-pill :story="$story" />
                            @endforeach
                        </div>
                    </div>
                @endif

                <div id="feed-container" class="space-y-5">
                     @forelse ($posts as $post)
                <x-instagram.post-card :post="$post" />
                     @empty
                <x-instagram.empty-feed />
                     @endforelse
                </div>

                <div
                    id="feed-loader"
                    class="hidden py-8"
                    role="status"
                    aria-live="polite">
                    <div class="mx-auto h-5 w-5 animate-spin rounded-full border-2 border-neutral-200 border-t-neutral-900"></div>
                    <span class="sr-only">Loading more posts...</span>
                </div>

                @if ($posts->hasMorePages())
                <div
                    id="feed-sentinel"
                    data-next-page="{{ $posts->nextPageUrl() }}"
                    aria-hidden="true"></div>
                @endif

                <div id="feed-retry" class="hidden py-6 text-center text-sm text-neutral-500" role="status">
                    Couldn't load more posts.
                    <button id="feed-retry-button" type="button" class="ml-2 font-semibold text-sky-500 hover:text-sky-600">
                        Retry
                    </button>
                </div>

                <div
                    id="feed-end"
                    class="hidden py-8 text-center text-sm text-neutral-500">
                    You're all caught up 🎉
                </div>

            </section>

            {{-- Suggestions --}}
            <aside class="hidden lg:block">
                <x-instagram.suggestions-panel :suggestions="$suggestions" />
            </aside>

        </div>

    </div>
</x-app-layout>

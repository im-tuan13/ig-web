<x-app-layout>
    <div class="mx-auto max-w-6xl px-0 py-0 sm:px-0 lg:px-0">

        {{-- Header --}}
        <div class="border-b border-neutral-200 px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-neutral-900">
                #{{ $hashtag }}
            </h1>
            <p class="mt-1 text-sm text-neutral-500">
                {{ number_format($postsCount) }} {{ Str::plural('post', $postsCount) }}
            </p>
        </div>

        @if ($posts->isNotEmpty())
            <section class="grid grid-cols-3 gap-[2px] sm:gap-[3px]">
                @foreach ($posts as $post)
                    <x-instagram.post-grid-item :post="$post" />
                @endforeach
            </section>

            @if ($posts->hasPages())
                <div class="mt-6 px-4 pb-6 sm:px-6 lg:px-8">{{ $posts->links() }}</div>
            @endif
        @else
            <div class="mx-auto max-w-6xl px-3 py-6 sm:px-6 lg:px-8">
                <section class="py-20 text-center">
                    <div class="mx-auto mb-6 flex h-[62px] w-[62px] items-center justify-center rounded-full border-2 border-neutral-900">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.5-3.5" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-neutral-900">No posts yet</h2>
                    <p class="mt-2 text-sm text-neutral-500">When someone posts with #{{ $hashtag }}, they'll appear here.</p>
                </section>
            </div>
        @endif
    </div>
</x-app-layout>


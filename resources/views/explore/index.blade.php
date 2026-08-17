<x-app-layout>
    <div class="ig-page px-0 py-0 sm:px-0">

        @if ($posts->isNotEmpty())
            <section class="grid grid-cols-3 gap-1 sm:gap-2">
                @foreach ($posts as $post)
                    <x-instagram.post-grid-item :post="$post" />
                @endforeach
            </section>

            @if ($posts->hasPages())
                <div class="mt-6 px-4 sm:px-6 lg:px-8">{{ $posts->links() }}</div>
            @endif
        @else
            <div class="px-4 py-6 sm:px-0">
                <section class="py-20 text-center">
                    <div class="mx-auto mb-6 flex h-[62px] w-[62px] items-center justify-center rounded-full border-2 border-neutral-900">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.5-3.5" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-neutral-900">No posts to explore yet</h2>
                    <p class="mt-2 text-sm text-neutral-500">Check back soon for new photos.</p>
                </section>
            </div>
        @endif
    </div>
</x-app-layout>

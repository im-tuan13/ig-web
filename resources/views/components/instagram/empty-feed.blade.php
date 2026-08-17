<section class="rounded-3xl border border-neutral-200 bg-white p-10 text-center shadow-sm">
    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full border-2 border-neutral-900">
        <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="5" />
            <path d="M12 8v8M8 12h8" />
        </svg>
    </div>
    <h1 class="text-2xl font-semibold text-neutral-950">Share your first photo</h1>
    <p class="mt-2 text-sm text-neutral-500">When you upload a post, it will show up in this feed.</p>
    <a href="{{ route('posts.create') }}" class="mt-5 inline-flex rounded-2xl bg-sky-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-600">Create post</a>
</section>

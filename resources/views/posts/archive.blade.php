<x-app-layout>
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 sm:py-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-neutral-950">Archive</h1>
                <p class="text-sm text-neutral-500">Only visible to you.</p>
            </div>
            <a href="{{ route('users.show', auth()->user()) }}" class="text-sm text-sky-500 hover:underline">Back to profile</a>
        </div>

        @if ($posts->isEmpty())
            <div class="py-16 text-center text-neutral-500">No archived posts.</div>
        @else
            <div class="grid grid-cols-3 gap-1">
                @foreach ($posts as $post)
                    <div class="group relative aspect-square overflow-hidden">
                        <img src="{{ $post->image_url }}" alt="Archived post" class="h-full w-full object-cover">
                        <form method="POST" action="{{ route('posts.archive.destroy', $post) }}" class="absolute inset-x-1 bottom-1 opacity-0 transition group-hover:opacity-100">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full rounded-lg bg-white/90 py-1 text-xs font-semibold text-neutral-900 hover:bg-white">Unarchive</button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $posts->links() }}</div>
        @endif
    </div>
</x-app-layout>

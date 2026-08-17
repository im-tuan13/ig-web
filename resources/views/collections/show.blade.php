<x-app-layout>
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 sm:py-8">
        <div class="mb-6 flex items-center justify-between">
            <div><h1 class="text-xl font-bold text-neutral-950">{{ $collection->name }}</h1><p class="text-sm text-neutral-500">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}</p></div>
            <div class="flex items-center gap-3"><a href="{{ route('collections.add', $collection) }}" class="text-sm text-sky-500 hover:underline">Add posts</a><form method="POST" action="{{ route('collections.destroy', $collection) }}" onsubmit="return confirm('Delete this collection?');">@csrf @method('DELETE')<button type="submit" class="text-sm text-red-500 hover:underline">Delete</button></form></div>
        </div>
        @if ($posts->isEmpty())
            <div class="py-16 text-center text-neutral-500">No posts in this collection yet. <a href="{{ route('collections.add', $collection) }}" class="text-sky-500 hover:underline">Add some</a>.</div>
        @else
            <div class="grid grid-cols-3 gap-1">
                @foreach ($posts as $post)
                    <div class="group relative aspect-square overflow-hidden"><a href="{{ route('posts.show', $post) }}" data-post-modal><img src="{{ $post->image_url }}" alt="Post" class="h-full w-full object-cover"></a><form method="POST" action="{{ route('collections.posts.destroy', [$collection, $post]) }}" class="absolute right-1 top-1 opacity-0 transition group-hover:opacity-100">@csrf @method('DELETE')<button type="submit" class="rounded-full bg-black/60 px-2 py-1 text-xs text-white">Remove</button></form></div>
                @endforeach
            </div>
            <div class="mt-6">{{ $posts->links() }}</div>
        @endif
    </div>
</x-app-layout>

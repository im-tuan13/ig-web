<x-app-layout>
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 sm:py-8">
        <div class="mb-6 flex items-center justify-between"><h1 class="text-xl font-bold text-neutral-950">Add posts to "{{ $collection->name }}"</h1><a href="{{ route('collections.show', $collection) }}" class="text-sm text-sky-500 hover:underline">Done</a></div>
        @if ($availablePosts->isEmpty())
            <div class="py-16 text-center text-neutral-500">No more saved posts to add. Save more posts first.</div>
        @else
            <div class="grid grid-cols-3 gap-1">
                @foreach ($availablePosts as $post)
                    <div class="relative aspect-square overflow-hidden"><img src="{{ $post->image_url }}" alt="Post" class="h-full w-full object-cover"><form method="POST" action="{{ route('collections.posts.store', [$collection, $post]) }}" class="absolute inset-x-1 bottom-1">@csrf<button type="submit" class="w-full rounded-lg bg-sky-500 py-1 text-xs font-semibold text-white hover:bg-sky-600">Add</button></form></div>
                @endforeach
            </div>
            <div class="mt-6">{{ $availablePosts->links() }}</div>
        @endif
    </div>
</x-app-layout>

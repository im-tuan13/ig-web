<x-app-layout>
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 sm:py-8">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-xl font-bold text-neutral-950">Collections</h1>
            <a href="{{ route('profile.saved') }}" class="text-sm text-sky-500 hover:underline">Back to Saved</a>
        </div>
        <form method="POST" action="{{ route('collections.store') }}" class="mb-8 flex gap-2">
            @csrf
            <input type="text" name="name" maxlength="60" required placeholder="New collection name" class="flex-1 rounded-lg border border-neutral-300 px-3 py-2 text-sm">
            <button type="submit" class="rounded-lg bg-sky-500 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-600">Create</button>
        </form>
        @if ($collections->isEmpty())
            <div class="py-16 text-center text-neutral-500">You haven't created any collections yet.</div>
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                @foreach ($collections as $collection)
                    <a href="{{ route('collections.show', $collection) }}" class="block">
                        <div class="aspect-square overflow-hidden rounded-lg bg-neutral-100">
                            @if ($collection->posts->first())
                                <img src="{{ $collection->posts->first()->image_url }}" alt="{{ $collection->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-neutral-400">Empty</div>
                            @endif
                        </div>
                        <div class="mt-2 text-sm font-semibold text-neutral-950">{{ $collection->name }}</div>
                        <div class="text-xs text-neutral-500">{{ $collection->posts_count }} {{ Str::plural('post', $collection->posts_count) }}</div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>

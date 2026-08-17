<x-app-layout>
    <div class="mx-auto max-w-5xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($highlight->cover_url)<img src="{{ $highlight->cover_url }}" class="h-14 w-14 rounded-full object-cover">@endif
                <div><h1 class="text-2xl font-semibold">{{ $highlight->title }}</h1><p class="text-sm text-neutral-500">{{ $highlight->user->username }}</p></div>
            </div>
            @can('update', $highlight)<a href="{{ route('highlights.edit', $highlight) }}" class="rounded border px-4 py-2 text-sm">Edit</a>@endcan
        </div>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            @foreach($highlight->stories as $story)
                <img src="{{ $story->image_url }}" alt="Story" class="aspect-square w-full rounded-lg object-cover">
            @endforeach
        </div>
    </div>
</x-app-layout>

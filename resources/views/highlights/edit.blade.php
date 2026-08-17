<x-app-layout>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <h1 class="mb-6 text-2xl font-semibold">Edit Highlight</h1>
        <form method="POST" action="{{ route('highlights.update', $highlight) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PUT')
            <input name="title" value="{{ old('title', $highlight->title) }}" required class="w-full rounded-lg border p-3">
            <input type="file" name="cover_image" accept="image/*" class="block w-full text-sm">
            <p class="text-sm text-neutral-500">Selected stories</p>
            <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                @foreach($stories as $story)
                    <label class="relative cursor-pointer">
                        <input type="checkbox" name="story_ids[]" value="{{ $story->id }}" class="absolute right-2 top-2 z-10" {{ $story->highlight_id === $highlight->id ? 'checked' : '' }}>
                        <img src="{{ $story->image_url }}" class="aspect-square w-full rounded object-cover">
                    </label>
                @endforeach
            </div>
            <button class="rounded-lg bg-neutral-900 px-5 py-2 font-semibold text-white">Save changes</button>
        </form>
        <form method="POST" action="{{ route('highlights.destroy', $highlight) }}" class="mt-4" onsubmit="return confirm('Delete this highlight?')">
            @csrf @method('DELETE') <button class="text-sm text-red-600">Delete highlight</button>
        </form>
    </div>
</x-app-layout>

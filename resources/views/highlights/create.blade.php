<x-app-layout>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <h1 class="mb-6 text-2xl font-semibold">New Highlight</h1>
        <form method="POST" action="{{ route('highlights.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input name="title" value="{{ old('title') }}" required placeholder="Title" class="w-full rounded-lg border p-3">
            <input type="file" name="cover_image" accept="image/*" class="block w-full text-sm">
            <h2 class="font-semibold">Choose stories</h2>
            <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                @foreach($stories as $story)
                    <label class="relative cursor-pointer">
                        <input type="checkbox" name="story_ids[]" value="{{ $story->id }}" class="absolute right-2 top-2 z-10" {{ in_array($story->id, old('story_ids', [])) ? 'checked' : '' }}>
                        <img src="{{ $story->image_url }}" class="aspect-square w-full rounded object-cover">
                    </label>
                @endforeach
            </div>
            @error('story_ids')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <button class="rounded-lg bg-neutral-900 px-5 py-2 font-semibold text-white">Create</button>
        </form>
    </div>
</x-app-layout>

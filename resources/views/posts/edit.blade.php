<x-app-layout>
    <div class="mx-auto max-w-2xl px-4 py-8">
        <section class="rounded border border-neutral-200 bg-white">
            <div class="flex items-center justify-between border-b border-neutral-200 px-4 py-3">
                <a href="{{ route('posts.show', $post) }}" class="text-sm text-neutral-500 hover:text-neutral-900">Cancel</a>
                <h1 class="text-base font-semibold">Edit post</h1>
                <div class="w-12"></div>
            </div>

            <form action="{{ route('posts.update', $post) }}" method="POST" class="p-4">
                @csrf
                @method('PUT')
                <label for="caption" class="mb-2 block text-sm font-semibold">Caption</label>
                <textarea id="caption" name="caption" rows="8" maxlength="2200" class="w-full resize-y rounded border-neutral-300 text-sm focus:border-sky-500 focus:ring-sky-500">{{ old('caption', $post->caption) }}</textarea>
                @error('caption')
                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit" class="mt-4 h-10 w-full rounded-lg bg-sky-500 text-sm font-semibold text-white hover:bg-sky-600">Save changes</button>
            </form>
        </section>
    </div>
</x-app-layout>

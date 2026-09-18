<x-app-layout>
    <div class="mx-auto max-w-lg px-4 py-8 sm:px-6">
        <h1 class="mb-6 text-xl font-bold text-neutral-950">Create Reel</h1>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('reels.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="mb-1 block text-sm font-semibold text-neutral-700">Video</label>
                <input type="file" name="video" accept="video/mp4,video/quicktime,video/webm,video/x-msvideo" required class="block w-full text-sm text-neutral-700 file:mr-4 file:rounded-lg file:border-0 file:bg-sky-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-sky-600">
                <p class="mt-1 text-xs text-neutral-500">MP4, MOV, or WebM. Max 50MB.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-neutral-700">Caption</label>
                <textarea name="caption" rows="3" maxlength="2200" placeholder="Write a caption... use #hashtags or @mentions" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm">{{ old('caption') }}</textarea>
            </div>

            <button type="submit" class="w-full rounded-lg bg-sky-500 py-2.5 text-sm font-semibold text-white hover:bg-sky-600">Share Reel</button>
        </form>
    </div>
</x-app-layout>

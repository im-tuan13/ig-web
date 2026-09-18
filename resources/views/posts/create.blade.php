<x-app-layout>
    <div class="mx-auto flex min-h-[calc(100vh-56px)] max-w-4xl items-center justify-center px-4 py-8 lg:min-h-screen">
        <section class="w-full overflow-hidden rounded border border-neutral-200 bg-white">
            <div class="flex h-12 items-center justify-between border-b border-neutral-200 px-4">
                <a href="{{ route('dashboard') }}" aria-label="Back to feed" class="rounded-full p-2 hover:bg-neutral-100">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M15 18 9 12l6-6" />
                    </svg>
                </a>
                <h1 class="text-base font-semibold">Create new post</h1>
                <div class="h-9 w-9"></div>
            </div>

            <form x-data="{
                previews: [],
                files: [],
                inputKey: Date.now(),
                handleFiles(files) {
                    const selectedFiles = Array.from(files || []);
                    this.files = selectedFiles;
                    this.previews = selectedFiles.map(file => ({ name: file.name, url: URL.createObjectURL(file) }));
                    this.syncInputFiles();
                },
                removePreview(index) {
                    this.previews.splice(index, 1);
                    this.files.splice(index, 1);
                    this.syncInputFiles();
                    if (this.files.length === 0) {
                        this.inputKey = Date.now();
                    }
                },
                syncInputFiles() {
                    const dataTransfer = new DataTransfer();
                    this.files.forEach(file => dataTransfer.items.add(file));
                    this.$refs.fileInput.files = dataTransfer.files;
                }
            }" action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="grid min-h-[560px] grid-cols-1 md:grid-cols-[minmax(0,1.4fr)_340px]">
                @csrf

                <div class="flex min-h-80 flex-col bg-neutral-50 p-4 sm:p-6">
                    <label for="images" class="relative flex min-h-[280px] cursor-pointer flex-col items-center justify-center gap-4 overflow-hidden rounded-2xl border border-dashed border-neutral-300 bg-white p-6 text-center transition hover:bg-neutral-100">
                        <div x-show="previews.length === 0" class="flex flex-col items-center gap-4">
                            <div class="flex h-24 w-24 items-center justify-center rounded-full border-2 border-neutral-900">
                                <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="16" rx="3" />
                                    <circle cx="9" cy="11" r="2" />
                                    <path d="m21 17-5.2-5.2a2 2 0 0 0-2.8 0L6 19" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xl font-light">Drag photos here</div>
                                <div class="mt-3 inline-flex rounded-lg bg-sky-500 px-4 py-2 text-sm font-semibold text-white">Select from computer</div>
                            </div>
                        </div>

                        <input x-ref="fileInput" :key="inputKey" id="images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="sr-only"
                            @change="handleFiles($event.target.files)">
                    </label>

                    <div x-show="previews.length > 0" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3" x-cloak>
                        <template x-for="(preview, index) in previews" :key="index">
                            <div class="group relative aspect-square overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm">
                                <img :src="preview.url" :alt="preview.name" class="h-full w-full object-cover">
                                <button type="button" class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-black/60 text-sm font-semibold text-white transition hover:bg-black/80"
                                    @click="removePreview(index)" aria-label="Remove preview">
                                    ×
                                </button>
                                <div class="absolute inset-x-0 bottom-0 bg-black/50 px-2 py-2 text-center text-[11px] font-medium text-white" x-text="preview.name"></div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 space-y-2">
                        @error('image')
                            <p class="rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                        @error('images')
                            <p class="rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                        @error('images.*')
                            <p class="rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="border-t border-neutral-200 md:border-l md:border-t-0">
                    <div class="flex items-center gap-3 border-b border-neutral-200 p-4">
                        @if (auth()->user()->avatar)
                            <img src="{{ asset(auth()->user()->avatar) }}" alt="{{ auth()->user()->username }}" class="h-9 w-9 rounded-full object-cover">
                        @else
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-neutral-200 text-sm font-bold uppercase">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                        <span class="text-sm font-semibold">{{ auth()->user()->username }}</span>
                    </div>

                    <div class="p-4">
                        <label for="caption" class="sr-only">Caption</label>
                        <textarea id="caption" name="caption" rows="10" maxlength="1000" placeholder="Write a caption..." class="w-full resize-none border-0 p-0 text-sm placeholder:text-neutral-400 focus:border-0 focus:ring-0">{{ old('caption') }}</textarea>
                        @error('caption')
                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="border-t border-neutral-200 p-4">
                        <button type="submit" class="flex h-10 w-full items-center justify-center rounded-lg bg-sky-500 text-sm font-semibold text-white hover:bg-sky-600">
                            Share
                        </button>
                        <p class="mt-3 text-xs leading-5 text-neutral-500">
                            JPG, PNG, and WEBP images up to 5 MB are supported.
                        </p>
                    </div>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>

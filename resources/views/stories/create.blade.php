<x-app-layout>
    <div class="mx-auto flex min-h-[calc(100vh-56px)] max-w-xl items-center justify-center px-4 py-8">

        <div class="w-full overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm">

            <div class="border-b border-neutral-200 px-6 py-4">
                <h1 class="text-center text-lg font-semibold">
                    Create Story
                </h1>
            </div>

            <form
                action="{{ route('stories.store') }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-6 p-6">

                @csrf

                <div>

                    <label class="mb-2 block text-sm font-medium">
                        Story Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept="image/*"
                        required
                        class="block w-full rounded-lg border border-neutral-300 p-3">

                    @error('image')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-sky-500 py-3 font-semibold text-white transition hover:bg-sky-600">
                    Share Story
                </button>

            </form>

        </div>

    </div>
</x-app-layout>
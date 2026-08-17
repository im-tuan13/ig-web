<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Profile
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-500 text-white rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="email" value="{{ old('email', auth()->user()->email) }}">
                    @error('email')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                            Nama
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', auth()->user()->name) }}"
                            class="w-full mt-1 rounded border-gray-300 dark:bg-gray-900 dark:text-white"
                        >
                        @error('name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                            Username
                        </label>

                        <input
                            type="text"
                            name="username"
                            value="{{ old('username', auth()->user()->username) }}"
                            class="w-full mt-1 rounded border-gray-300 dark:bg-gray-900 dark:text-white"
                        >
                        @error('username')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                            Bio
                        </label>

                        <textarea
                            name="bio"
                            rows="4"
                            class="w-full mt-1 rounded border-gray-300 dark:bg-gray-900 dark:text-white"
                        >{{ old('bio', auth()->user()->bio) }}</textarea>
                        @error('bio')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4 flex items-center gap-2">
                        <input type="hidden" name="is_private" value="0">
                        <input id="is_private" type="checkbox" name="is_private" value="1" @checked(old('is_private', auth()->user()->is_private)) class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_private" class="text-sm text-gray-700 dark:text-gray-300">Private account</label>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                            Avatar
                        </label>

                        @if(auth()->user()->avatar)
                            <img
                                src="{{ asset(auth()->user()->avatar) }}"
                                class="w-24 h-24 rounded-full object-cover mb-3"
                            >
                        @endif

                        <input
                            type="file"
                            name="avatar"
                            class="w-full text-white"
                        >
                        @error('avatar')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded"
                    >
                        Simpan Perubahan
                    </button>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <div class="mx-auto max-w-[720px] px-4 py-6 sm:px-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-xl font-bold text-neutral-950">New Group</h1>
            <a href="{{ route('messages.index') }}" class="text-sm text-sky-500 hover:underline">Cancel</a>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('messages.group.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-semibold text-neutral-700">Group name (optional)</label>
                <input type="text" name="name" maxlength="60" placeholder="e.g. Study Squad"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-neutral-700">Add people you follow</label>
                @if ($followingUsers->isEmpty())
                    <p class="text-sm text-neutral-500">You're not following anyone yet.</p>
                @else
                    <div class="max-h-80 divide-y divide-neutral-100 overflow-y-auto rounded-xl border border-neutral-200">
                        @foreach ($followingUsers as $person)
                            <label class="flex cursor-pointer items-center gap-3 px-3 py-2.5 hover:bg-neutral-50">
                                <input type="checkbox" name="participants[]" value="{{ $person->id }}" class="rounded border-neutral-300">
                                @if ($person->avatar)
                                    <img src="{{ asset($person->avatar) }}" alt="{{ $person->username }}" class="h-8 w-8 rounded-full object-cover">
                                @else
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-neutral-200 text-xs font-bold uppercase text-neutral-600">
                                        {{ substr($person->name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="text-sm text-neutral-900">{{ $person->username }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <button type="submit" class="rounded-lg bg-sky-500 px-5 py-2 text-sm font-semibold text-white hover:bg-sky-600">Create Group</button>
        </form>
    </div>
</x-app-layout>

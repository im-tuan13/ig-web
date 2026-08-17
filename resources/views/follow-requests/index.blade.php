<x-app-layout>
    <div class="mx-auto max-w-2xl px-4 py-8">
        @if (session('success'))
            <div class="mb-4 rounded bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        <section class="rounded border border-neutral-200 bg-white">
            <h1 class="border-b border-neutral-200 px-4 py-4 text-lg font-semibold">Follow requests</h1>
            <div class="divide-y divide-neutral-100">
                @forelse ($requests as $request)
                    <div class="flex items-center gap-3 px-4 py-4">
                        <a href="{{ route('users.show', $request->follower) }}" class="flex min-w-0 flex-1 items-center gap-3">
                            @if ($request->follower->avatar)
                                <img src="{{ asset($request->follower->avatar) }}" alt="{{ $request->follower->username }}" class="h-10 w-10 rounded-full object-cover">
                            @else
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-neutral-200 text-sm font-bold uppercase">{{ substr($request->follower->name, 0, 1) }}</span>
                            @endif
                            <span class="truncate text-sm font-semibold">{{ $request->follower->username }}</span>
                        </a>
                        <form method="POST" action="{{ route('follow-requests.accept', $request) }}">
                            @csrf
                            <button class="rounded bg-sky-500 px-3 py-1.5 text-sm font-semibold text-white">Confirm</button>
                        </form>
                        <form method="POST" action="{{ route('follow-requests.reject', $request) }}">
                            @csrf
                            @method('DELETE')
                            <button class="rounded border border-neutral-300 px-3 py-1.5 text-sm font-semibold">Delete</button>
                        </form>
                    </div>
                @empty
                    <p class="px-4 py-12 text-center text-sm text-neutral-500">No follow requests.</p>
                @endforelse
            </div>
            @if ($requests->hasPages())
                <div class="border-t border-neutral-100 px-4 py-3">{{ $requests->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>

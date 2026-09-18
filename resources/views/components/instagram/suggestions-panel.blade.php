@props(['suggestions'])

<div class="sticky top-6 space-y-4">
    <div class="flex items-center gap-3 px-1">
        @if (auth()->user()->avatar)
            <img src="{{ asset(auth()->user()->avatar) }}" alt="{{ auth()->user()->username }}" class="h-11 w-11 rounded-full object-cover" />
        @else
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-neutral-200 text-sm font-bold uppercase text-neutral-600">{{ substr(auth()->user()->name, 0, 1) }}</div>
        @endif
        <div class="min-w-0 flex-1">
            <div class="truncate text-sm font-semibold text-neutral-950">{{ auth()->user()->username }}</div>
            <div class="truncate text-xs text-neutral-500">{{ auth()->user()->name }}</div>
        </div>
        <a href="{{ route('profile.show') }}" class="text-xs font-semibold text-sky-500">Switch</a>
    </div>

    <div>
        <div class="flex items-center justify-between px-1">
            <span class="text-sm font-semibold text-neutral-500">Suggested for you</span>
            <a href="#" class="text-xs font-semibold text-neutral-900">See all</a>
        </div>
        <div class="mt-2 space-y-1">
            @forelse ($suggestions as $suggestion)
                <x-instagram.suggestion-card :user="$suggestion" />
            @empty
                <p class="p-2 text-sm text-neutral-500">No suggestions yet.</p>
            @endforelse
        </div>
    </div>
</div>

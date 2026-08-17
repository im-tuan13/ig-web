@props(['conversation', 'user', 'otherUser'])

<header class="flex min-h-16 items-center gap-3 border-b border-outline-variant px-4 py-3 sm:px-5">
    <a href="{{ route('messages.index') }}" class="flex h-9 w-9 items-center justify-center rounded-lg hover:bg-neutral-100 sm:hidden" aria-label="Back to messages"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg></a>
    @if ($conversation->is_group)
        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-neutral-200 text-sm font-bold">{{ substr($conversation->displayNameFor($user), 0, 1) }}</div>
        <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ $conversation->displayNameFor($user) }}</p><p class="text-xs text-neutral-500">{{ $conversation->participants->count() }} members</p></div>
    @else
        @if ($otherUser?->avatar)<img src="{{ asset($otherUser->avatar) }}" class="h-9 w-9 rounded-full object-cover" alt="{{ $otherUser->username }}"/>@else<div class="flex h-9 w-9 items-center justify-center rounded-full bg-neutral-200 text-sm font-bold">{{ $otherUser ? substr($otherUser->name, 0, 1) : '?' }}</div>@endif
        <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ $otherUser?->username ?? 'Unknown' }}</p><p class="truncate text-xs text-neutral-500">{{ $otherUser?->name ?? 'Active now' }}</p></div>
        @if ($otherUser)
            <button type="button" @click="window.dispatchEvent(new CustomEvent('start-call', { detail: { userId: {{ $otherUser->id }} } }))" class="flex h-9 w-9 items-center justify-center rounded-full hover:bg-neutral-100" aria-label="Start video call"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="12" height="14" rx="2"/><path d="m15 10 5-3v10l-5-3z"/></svg></button>
            <a href="{{ route('users.show', $otherUser) }}" class="flex h-9 w-9 items-center justify-center rounded-full hover:bg-neutral-100" aria-label="Conversation info"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 10v6m0-9h.01"/></svg></a>
        @endif
    @endif
</header>

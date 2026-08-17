@php($user = auth()->user())

<x-app-layout>
    <div class="ig-page px-0 py-0 sm:px-4">
        @foreach (['success' => 'emerald', 'error' => 'red'] as $key => $color)
            @if (session($key))<div role="status" class="mx-4 mb-4 rounded-lg border border-{{ $color }}-200 bg-{{ $color }}-50 px-4 py-3 text-sm font-medium text-{{ $color }}-700 sm:mx-0">{{ session($key) }}</div>@endif
        @endforeach
        <section class="grid min-h-[calc(100vh-7rem)] overflow-hidden border-y border-outline-variant bg-white sm:min-h-[680px] sm:grid-cols-[350px_minmax(0,1fr)] sm:rounded-lg sm:border">
            <div class="border-outline-variant sm:border-r">
                <header class="flex h-20 items-center justify-between border-b border-outline-variant px-5"><h1 class="text-xl font-bold tracking-tight text-neutral-950">{{ $user->username }}</h1><div class="flex items-center gap-1"><a href="{{ route('calls.index') }}" class="hidden rounded-lg px-2 py-2 text-xs font-semibold text-neutral-600 hover:bg-neutral-100 sm:block">Calls</a><a href="{{ route('messages.group.create') }}" class="flex h-9 w-9 items-center justify-center rounded-lg hover:bg-neutral-100" aria-label="New message"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m14.5 4.5 5 5M4 20l4.2-1 10.4-10.4a2.1 2.1 0 0 0-3-3L5.2 16 4 20z"/></svg></a></div></header>
                <div class="border-b border-outline-variant px-4 py-3"><a href="{{ route('search.index') }}" class="flex h-9 items-center gap-2 rounded-lg bg-neutral-100 px-3 text-sm text-neutral-600 hover:bg-neutral-200"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>Search messages</a></div>
                <div class="divide-y divide-neutral-100">@forelse ($conversations as $conversation)<x-instagram.message-item :conversation="$conversation" :current-user="$user" :unread="$unreadCounts[$conversation->id] ?? 0" />@empty<div class="px-6 py-12 text-center text-sm text-neutral-500">No messages yet.</div>@endforelse</div>
                @if ($conversations->hasPages())<div class="border-t border-neutral-200 p-4">{{ $conversations->links() }}</div>@endif
            </div>
            <div class="hidden flex-col items-center justify-center bg-[#fafafa] px-6 text-center sm:flex"><div class="flex h-24 w-24 items-center justify-center rounded-full border-2 border-neutral-950"><svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.8 8.8 0 0 1-3.5-.7L3 21l1.8-5.1a8 8 0 0 1-1-4.4 8.4 8.4 0 0 1 17.2 0z"/></svg></div><h2 class="mt-5 text-xl font-medium text-neutral-950">Your messages</h2><p class="mt-2 max-w-xs text-sm text-neutral-500">Select a conversation or start a new message to connect with people.</p><a href="{{ route('search.index') }}" class="mt-5 rounded-lg bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-black">Send message</a></div>
        </section>
    </div>
</x-app-layout>

@php
    $user = auth()->user();
    $sidebarConversations = $user->conversations()->with(['participants:id,name,username,avatar'])->orderByDesc(\App\Models\Message::select('created_at')->whereColumn('conversation_id', 'conversations.id')->latest()->take(1))->get();
    $sidebarConversations->load(['messages' => fn ($query) => $query->with('user:id,name,username,avatar')->latest()->take(1)]);
@endphp
<x-app-layout>
    <div class="ig-page flex h-[calc(100vh-4rem)] max-w-[935px] flex-col px-0 py-0 sm:h-[calc(100vh-2rem)] sm:px-4">
        <section class="grid flex-1 overflow-hidden border-y border-outline-variant bg-white sm:grid-cols-[350px_minmax(0,1fr)] sm:rounded-lg sm:border" x-data="dmChat({{ $conversation->id }}, '{{ $conversation->messages->last()?->created_at?->toIso8601String() ?? '' }}', '{{ $user->id }}')">
            <aside class="hidden overflow-y-auto border-r border-outline-variant sm:block"><header class="flex h-20 items-center justify-between border-b border-outline-variant px-5"><h1 class="text-xl font-bold">{{ $user->username }}</h1><a href="{{ route('messages.group.create') }}" class="flex h-9 w-9 items-center justify-center rounded-lg hover:bg-neutral-100" aria-label="New message"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m14.5 4.5 5 5M4 20l4.2-1 10.4-10.4a2.1 2.1 0 0 0-3-3L5.2 16 4 20z"/></svg></a></header><div class="divide-y divide-neutral-100">@foreach ($sidebarConversations as $item)<x-instagram.message-item :conversation="$item" :current-user="$user" :active="$item->id === $conversation->id" />@endforeach</div></aside>
            <div class="flex min-w-0 flex-col overflow-hidden">
                <x-instagram.chat-header :conversation="$conversation" :user="$user" :other-user="$otherUser" />
                <div class="flex-1 overflow-y-auto bg-[#fafafa] px-4 py-6 sm:px-7" x-ref="messagesContainer">@php($lastDate = null)@forelse ($conversation->messages as $message)@php($messageDate = $message->created_at->format('Y-m-d'))@if ($messageDate !== $lastDate)@php($lastDate = $messageDate)<div class="my-5 text-center text-xs font-semibold text-neutral-400"><span class="rounded-full bg-neutral-100 px-3 py-1">{{ $message->created_at->isToday() ? 'Today' : ($message->created_at->isYesterday() ? 'Yesterday' : $message->created_at->format('F j, Y')) }}</span></div>@endif<x-instagram.chat-bubble :message="$message" :current-user="$user" :group="$conversation->is_group" />@empty<div class="flex h-full items-center justify-center text-center"><div><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border-2 border-neutral-950">✉</div><p class="mt-4 text-sm font-medium">No messages yet</p><p class="mt-1 text-xs text-neutral-500">Send a message to start the conversation.</p></div></div>@endforelse</div>
                <x-instagram.chat-input />
            </div>
        </section>
    </div>
</x-app-layout>

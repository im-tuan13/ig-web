@php
    $user = auth()->user();
    $sidebarConversations = $user->conversations()
        ->with(['participants:id,name,username,avatar'])
        ->orderByDesc(\App\Models\Message::select('created_at')->whereColumn('conversation_id', 'conversations.id')->latest()->take(1))
        ->get();
    $sidebarConversations->load(['messages' => fn ($query) => $query->with('user:id,name,username,avatar')->latest()->take(1)]);
    $notes = \App\Models\Note::query()
        ->with('user:id,name,username,avatar')
        ->where('expires_at', '>', now())
        ->where(function ($query) use ($user): void {
            $query->where('user_id', $user->id)
                ->orWhereHas('user.conversations', function ($conversationQuery) use ($user): void {
                    $conversationQuery->whereHas('participants', fn ($participantQuery) => $participantQuery->whereKey($user->id));
                });
        })
        ->latest()
        ->get();
@endphp
<x-app-layout>
<div class="min-h-[calc(100vh-60px)] bg-black text-white md:-ml-[244px] md:pl-[244px]" x-data="dmChat({{ $conversation->id }}, '{{ $conversation->messages->last()?->created_at?->toIso8601String()??'' }}', '{{ $user->id }}')">
 <section class="mx-auto grid h-[calc(100vh-60px)] max-w-[1260px] grid-cols-[72px_380px_minmax(0,1fr)] border-x border-[#262626]">
  <nav class="hidden flex-col items-center gap-7 border-r border-[#262626] py-7 md:flex"><a href="{{ route('dashboard') }}" class="text-2xl font-bold">◎</a><a href="{{ route('dashboard') }}" class="text-xl">⌂</a><a href="{{ route('messages.index') }}" class="text-xl">✉</a><a href="{{ route('profile.show') }}" class="text-xl">◉</a></nav>
  <aside class="hidden overflow-y-auto border-r border-[#262626] md:block"><header class="flex h-20 items-center justify-between border-b border-[#262626] px-5"><h1 class="text-xl font-bold">{{ $user->username }}</h1><a href="{{ route('messages.group.create') }}" class="text-2xl">＋</a></header><x-instagram.dm-notes :notes="$notes" /><div class="flex gap-5 border-b border-[#262626] px-5 pt-4 text-sm font-semibold"><button class="border-b-2 border-white pb-3">Primary</button><button class="pb-3 text-[#737373]">General</button><button class="pb-3 text-[#737373]">Requests</button></div><div>@foreach($sidebarConversations as $item)<x-instagram.message-item :conversation="$item" :current-user="$user" :active="$item->id===$conversation->id" />@endforeach</div></aside>
  <main class="col-span-2 flex min-w-0 flex-col md:col-span-1"><x-instagram.chat-header :conversation="$conversation" :user="$user" :other-user="$otherUser" /><div class="flex-1 overflow-y-auto bg-black px-4 py-6 sm:px-8" x-ref="messagesContainer">@php($lastDate = null)@forelse($conversation->messages as $message)@php($messageDate = $message->created_at->format('Y-m-d'))@if($messageDate !== $lastDate)@php($lastDate = $messageDate)<div class="my-5 text-center text-xs font-semibold text-[#737373]" data-date-divider data-date="{{ $message->created_at->toIso8601String() }}"><span class="rounded-full bg-[#1c1c1c] px-3 py-1">{{ $message->created_at->isToday() ? 'Today' : ($message->created_at->isYesterday() ? 'Yesterday' : $message->created_at->format('F j, Y')) }}</span></div>@endif<x-instagram.chat-bubble :message="$message" :current-user="$user" :group="$conversation->is_group" />@empty<div class="flex h-full items-center justify-center text-center text-[#737373]">No messages yet.</div>@endforelse</div><x-instagram.chat-input /></main>
 </section>
</div>
</x-app-layout>

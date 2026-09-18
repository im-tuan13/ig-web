@props(['conversation', 'currentUser', 'unread' => 0, 'active' => false])

@php
    $otherUser = $conversation->otherParticipant($currentUser);
    $lastMessage = $conversation->messages->first();
    $displayName = $conversation->displayNameFor($currentUser);
@endphp

<a href="{{ route('messages.show', $conversation) }}" @class([
    'flex items-center gap-3 border-l-2 px-5 py-3.5 transition',
    'border-transparent hover:bg-[#fafafa]' => ! $active,
    'border-[#0095f6] bg-[#f5f5f5]' => $active,
])>
    @if ($conversation->is_group)
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-neutral-200 text-base font-semibold uppercase text-neutral-600">{{ substr($displayName, 0, 1) }}</div>
    @elseif ($otherUser?->avatar)
        <img src="{{ asset($otherUser->avatar) }}" alt="{{ $otherUser->username }}" class="h-14 w-14 shrink-0 rounded-full object-cover" />
    @else
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-neutral-200 text-base font-semibold uppercase text-neutral-600">{{ $otherUser ? substr($otherUser->name, 0, 1) : '?' }}</div>
    @endif
    <div class="min-w-0 flex-1">
        <p class="truncate text-sm {{ $unread > 0 ? 'font-bold text-neutral-950' : 'font-medium text-neutral-950' }}">{{ $displayName }}</p>
        <p class="mt-1 truncate text-sm {{ $unread > 0 ? 'font-semibold text-neutral-700' : 'text-neutral-500' }}">
            @if ($lastMessage)
                {{ $lastMessage->user_id === $currentUser->id ? 'You: ' : '' }}{{ $lastMessage->type === 'sticker' ? 'Sent a sticker' : $lastMessage->message }} <span class="text-neutral-400">· {{ $lastMessage->created_at->diffForHumans() }}</span>
            @else
                No messages yet
            @endif
        </p>
    </div>
    @if ($unread > 0)<span class="h-2.5 w-2.5 shrink-0 rounded-full bg-[#0095f6]"></span>@endif
</a>

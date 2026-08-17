@props(['notifications' => null])

@php
    $notifications ??= auth()->user()->notifications()
        ->with(['actor:id,name,username,avatar', 'post:id,image'])
        ->latest()
        ->paginate(20);
@endphp

<div class="flex h-full flex-col">

    {{-- Fixed Header with "Mark all as read" --}}
    <div class="flex shrink-0 items-center justify-between border-b border-neutral-200 pb-4">
        <h2 class="text-xl font-bold text-neutral-950">Notifications</h2>

        @if ($notifications->total() > 0)
            <form method="POST" action="{{ route('notifications.read') }}">
                @csrf
                <button type="submit" class="rounded-full bg-sky-500 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-sky-600">
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    {{-- Scrollable Body --}}
    <div class="-mx-6 flex-1 overflow-y-auto px-6 py-4">
        @if ($notifications->isNotEmpty())
            <div class="divide-y divide-neutral-100">
                @foreach ($notifications as $notification)
                    @php
                        $isUnread = is_null($notification->read_at);
                        $actor = $notification->actor;
                        $post = $notification->post;
                    @endphp

                    <x-instagram.notification-item :notification="$notification" />
                    <div class="hidden flex items-start gap-3 px-1 py-3.5 {{ $isUnread ? 'bg-sky-50/60 -mx-3 rounded-xl px-4' : '' }}">

                        <div class="shrink-0">
                            @if ($actor && $actor->avatar)
                                <img src="{{ asset($actor->avatar) }}" alt="{{ $actor->username }}" class="h-11 w-11 rounded-full object-cover" />
                            @else
                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-neutral-200 text-sm font-bold uppercase text-neutral-600">
                                    {{ $actor ? substr($actor->name, 0, 1) : '?' }}
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-neutral-700">
                                @if ($notification->type === 'like')
                                    <a href="{{ route('users.show', $actor->username) }}" class="font-semibold text-neutral-950 hover:underline">{{ $actor->username }}</a>
                                    liked your
                                    <a href="{{ route('posts.show', $post) }}" class="font-semibold text-neutral-950 hover:underline" data-post-modal>post</a>.
                                @elseif ($notification->type === 'comment')
                                    <a href="{{ route('users.show', $actor->username) }}" class="font-semibold text-neutral-950 hover:underline">{{ $actor->username }}</a>
                                    commented on your
                                    <a href="{{ route('posts.show', $post) }}" class="font-semibold text-neutral-950 hover:underline" data-post-modal>post</a>:
                                    <span class="mt-1 block italic text-neutral-500">
                                        "{{ $notification->data['comment'] ?? '' }}"
                                    </span>
                                @elseif ($notification->type === 'follow')
                                    <a href="{{ route('users.show', $actor->username) }}" class="font-semibold text-neutral-950 hover:underline">{{ $actor->username }}</a>
                                    started following you.
                                @elseif ($notification->type === 'mention')
                                    <a href="{{ route('users.show', $actor->username) }}" class="font-semibold text-neutral-950 hover:underline">{{ $actor->username }}</a>
                                    mentioned you in a
                                    <a href="{{ route('posts.show', $post) }}" class="font-semibold text-neutral-950 hover:underline" data-post-modal>{{ ($notification->data['context'] ?? null) === 'comment' ? 'comment' : 'post' }}</a>.
                                    @if (($notification->data['context'] ?? null) === 'comment' && !empty($notification->data['comment']))
                                        <span class="mt-1 block italic text-neutral-500">"{{ $notification->data['comment'] }}"</span>
                                    @endif
                                @endif
                            </p>
                            <p class="mt-0.5 text-xs text-neutral-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>

                            @if ($isUnread)
                                <div class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-sky-500"></div>
                            @endif

                        @if ($post && $post->image)
                            <a href="{{ route('posts.show', $post) }}" data-post-modal class="shrink-0">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->image) }}" alt="Post" class="h-11 w-11 rounded-lg object-cover" />
                            </a>
                        @endif

                    </div>
                @endforeach
            </div>

            @if ($notifications->hasPages())
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-neutral-100">
                    <svg class="h-8 w-8 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-neutral-950">No notifications yet</h2>
                <p class="mt-1 text-sm text-neutral-500">When people interact with you, you'll see it here.</p>
            </div>
        @endif
    </div>

</div>

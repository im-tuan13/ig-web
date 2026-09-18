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
                    <x-instagram.notification-item :notification="$notification" />
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

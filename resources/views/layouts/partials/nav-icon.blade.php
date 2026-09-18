@switch($icon)

    @case('home')
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M3 10.8 12 3l9 7.8V21a1 1 0 0 1-1 1h-5.5v-6h-5v6H4a1 1 0 0 1-1-1z" />
        </svg>
        @break

    @case('search')
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3.5-3.5" />
        </svg>
        @break

    @case('explore')
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="12" cy="12" r="8.5" />
            <path d="m15.5 8.5-2.1 4.8-4.8 2.1 2.1-4.8z" />
        </svg>
        @break

    @case('create')
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="5" />
            <path d="M12 8v8M8 12h8" />
        </svg>
        @break

    @case('profile')
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="12" cy="8" r="4" />
            <path d="M4 21c1.8-4 4.5-6 8-6s6.2 2 8 6" />
        </svg>
        @break

    @case('messages')
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.8 8.8 0 0 1-3.5-.7L3 21l1.8-5.1a8 8 0 0 1-1-4.4 8.4 8.4 0 0 1 17.2 0z" />
        </svg>
        @break

    @case('reels')
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M8 5.5v13l11-6.5z" />
            <rect x="2" y="3" width="20" height="18" rx="3" />
        </svg>
        @break

    @case('notifications')
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
        </svg>
        @break

    @case('logout')
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M10 17 15 12l-5-5" />
            <path d="M15 12H3" />
            <path d="M14 4h5a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-5" />
        </svg>
        @break

@endswitch

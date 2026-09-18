@props(['navItems', 'user', 'avatar', 'notificationsForDrawer' => null])

<div
    x-data="sidebar"
    @keydown.escape.window="searchOpen = false; notifOpen = false"
>

    {{-- Backdrop for Search --}}
    <div
        x-show="searchOpen"
        x-transition.opacity
        @click="searchOpen = false"
        class="fixed inset-0 z-40 bg-black/40"
        style="display:none">
    </div>

    {{-- Backdrop for Notifications --}}
    <div
        x-show="notifOpen"
        x-transition.opacity
        @click="notifOpen = false"
        class="fixed inset-0 z-40 bg-black/40"
        style="display:none">
    </div>

    <aside class="fixed inset-y-0 left-0 z-50 hidden h-screen w-[244px] border-r border-outline-variant bg-surface px-3 py-5 md:flex md:flex-col md:justify-between">

        {{-- Instagram Logo --}}
        <a href="{{ route('dashboard') }}"
           class="mb-4 block px-4 py-8">
            <svg class="h-8 w-32" viewBox="0 0 128 32" fill="currentColor" aria-label="Instagram">
                                                <text x="0" y="24" font-family="Inter, sans-serif" font-size="24" font-weight="600">Instagram</text>
            </svg>
        </a>

        {{-- Navigation --}}
        <nav class="space-y-1">

            @foreach ($navItems as $item)

                @if($item['label'] === 'Search')

                    <a
                        href="{{ route($item['route']) }}"
                        class="ig-nav-link {{ $item['active'] ? 'ig-nav-link-active' : '' }}">

                        @include('layouts.partials.nav-icon',[
                            'icon'=>$item['icon'],
                            'active'=>$item['active']
                        ])

                        <span>Search</span>

                    </a>

                @elseif($item['label'] === 'Notifications')

                    <a
                        href="{{ route($item['route']) }}"
                        class="ig-nav-link {{ $item['active'] ? 'ig-nav-link-active' : '' }}">

                        @include('layouts.partials.nav-icon',[
                            'icon'=>$item['icon'],
                            'active'=>$item['active']
                        ])

                        <span class="flex items-center gap-2">
                            <span>{{ $item['label'] }}</span>
                            @if (($item['badge'] ?? 0) > 0)
                                <span class="flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold leading-none text-white">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </span>

                    </a>

                @else
                    <a
                        href="{{ route($item['route']) }}"
                        class="ig-nav-link {{ $item['active'] ? 'ig-nav-link-active' : '' }}">

                        @include('layouts.partials.nav-icon',[
                            'icon'=>$item['icon'],
                            'active'=>$item['active']
                        ])

                        <span class="flex items-center gap-2">
                            <span>{{ $item['label'] }}</span>

                            @if ($item['label'] === 'Messages')
                                <span
                                    id="dm-badge-count"
                                    x-show="dmCountValue > 0"
                                    x-text="dmCountValue"
                                    class="flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold leading-none text-white">
                                    {{ $item['badge'] ?? 0 }}
                                </span>
                            @elseif (($item['badge'] ?? 0) > 0)
                                <span class="flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold leading-none text-white">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </span>

                    </a>

                @endif

            @endforeach

        </nav>

        {{-- Bottom Section --}}
        <div class="space-y-1">

            {{-- Profile --}}
            <a href="{{ route('profile.show') }}"
               class="ig-nav-link">

                @if($avatar)
                    <img src="{{ $avatar }}" class="h-7 w-7 rounded-full object-cover">
                @else
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-neutral-200 text-xs font-bold">
                        {{ strtoupper(substr($user->username,0,1)) }}
                    </div>
                @endif

                <span class="truncate text-sm font-medium">{{ $user->username }}</span>

            </a>

            {{-- Logout --}}
            <form action="{{ route('logout') }}" method="POST" class="block">
                @csrf
                <button class="ig-nav-link w-[calc(100%-0.5rem)]">
                    <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M10 17 15 12l-5-5" />
                        <path d="M15 12H3" />
                        <path d="M14 4h5a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-5" />
                    </svg>
                    <span>Log out</span>
                </button>
            </form>

        </div>

    </aside>

    {{-- Compact navigation keeps the primary Instagram actions reachable on mobile. --}}
    <nav class="fixed inset-x-0 bottom-0 z-50 flex h-12 items-center justify-around border-t border-outline-variant bg-surface px-6 md:hidden">
        @foreach (collect($navItems)->whereIn('label', ['Home', 'Search', 'Create', 'Reels', 'Profile']) as $item)
            <a href="{{ route($item['route']) }}" class="flex h-9 w-9 items-center justify-center rounded-lg text-on-surface-variant transition hover:bg-surface-container-high {{ $item['active'] ? 'font-bold text-on-surface' : '' }}" aria-label="{{ $item['label'] }}">
                @include('layouts.partials.nav-icon', ['icon' => $item['icon'], 'active' => $item['active']])
            </a>
        @endforeach
    </nav>

    {{-- Search Panel (left drawer) --}}
    <aside
        x-show="searchOpen"
        x-transition:enter="transition duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed left-[244px] top-0 z-50 flex h-screen w-[400px] flex-col border-r border-outline-variant bg-surface"
        style="display:none">

        <div class="flex-1 overflow-y-auto p-6">
            <x-instagram.search-panel />
        </div>

    </aside>

    {{-- Notifications Panel (right drawer) --}}
    <aside
        x-show="notifOpen"
        x-transition:enter="transition duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed right-0 top-0 z-50 flex h-screen w-[400px] flex-col border-l border-outline-variant bg-surface shadow-xl"
        style="display:none">

        {{-- Close button --}}
        <button
            @click="notifOpen = false"
            type="button"
            class="absolute left-4 top-4 z-10 rounded-full p-2 text-neutral-500 transition hover:bg-neutral-100"
            aria-label="Close notifications">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path d="m6 6 12 12M18 6 6 18" />
            </svg>
        </button>

        <div class="flex-1 overflow-y-auto px-6 py-6 pt-16">
            <x-instagram.notification-panel :notifications="$notificationsForDrawer" />
        </div>

    </aside>

</div>

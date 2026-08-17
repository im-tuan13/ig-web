@php
    $user = auth()->user();
    $avatar = $user->avatar ? asset($user->avatar) : null;
    $unreadNotificationsCount = $user->notifications()->unread()->count();
    $unreadMessagesCount = $user->conversations()
        ->withPivot('last_read_at')
        ->get()
        ->sum(fn ($c) => $c->messages()
            ->where('user_id', '!=', $user->id)
            ->when($c->pivot->last_read_at, fn ($q) => $q->where('created_at', '>', $c->pivot->last_read_at))
            ->count()
        );

    $navItems = [
        [
            'label' => 'Home',
            'route' => 'dashboard',
            'active' => request()->routeIs('dashboard'),
            'icon' => 'home',
        ],
        [
            'label' => 'Search',
            'route' => 'search.index',
            'active' => request()->routeIs('search.*'),
            'icon' => 'search',
        ],
        [
            'label' => 'Explore',
            'route' => 'explore.index',
            'active' => request()->routeIs('explore.*'),
            'icon' => 'explore',
        ],
        [
            'label' => 'Messages',
            'route' => 'messages.index',
            'active' => request()->routeIs('messages.*'),
            'icon' => 'messages',
            'badge' => $unreadMessagesCount,
        ],
        [
            'label' => 'Reels',
            'route' => 'reels.index',
            'active' => request()->routeIs('reels.*'),
            'icon' => 'reels',
        ],
        [
            'label' => 'Notifications',
            'route' => 'notifications.index',
            'active' => request()->routeIs('notifications.*'),
            'icon' => 'notifications',
            'badge' => $unreadNotificationsCount,
        ],
        [
            'label' => 'Create',
            'route' => 'posts.create',
            'active' => request()->routeIs('posts.create'),
            'icon' => 'create',
        ],
        [
            'label' => 'Profile',
            'route' => 'profile.show',
            'active' => request()->routeIs('profile.*'),
            'icon' => 'profile',
        ],
    ];
@endphp

<x-instagram.sidebar
    :nav-items="$navItems"
    :user="$user"
    :avatar="$avatar" />
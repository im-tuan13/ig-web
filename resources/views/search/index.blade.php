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
        ['label' => 'Home', 'route' => 'dashboard', 'active' => false, 'icon' => 'home'],
        ['label' => 'Search', 'route' => 'search.index', 'active' => true, 'icon' => 'search'],
        ['label' => 'Explore', 'route' => 'explore.index', 'active' => false, 'icon' => 'explore'],
        ['label' => 'Messages', 'route' => 'messages.index', 'active' => false, 'icon' => 'messages', 'badge' => $unreadMessagesCount],
        ['label' => 'Notifications', 'route' => 'notifications.index', 'active' => false, 'icon' => 'notifications', 'badge' => $unreadNotificationsCount],
        ['label' => 'Create', 'route' => 'posts.create', 'active' => false, 'icon' => 'create'],
        ['label' => 'Profile', 'route' => 'profile.show', 'active' => false, 'icon' => 'profile'],
    ];
@endphp

<x-app-layout>
    <div class="mx-auto h-[calc(100vh-4rem)] max-w-[400px] px-4 py-6">
        <x-instagram.search-panel :standalone="true" />
    </div>
</x-app-layout>

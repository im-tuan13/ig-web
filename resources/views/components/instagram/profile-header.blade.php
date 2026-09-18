@props(['user', 'followStatus' => null])

<section class="flex w-full flex-col gap-5 pb-8 sm:flex-row sm:items-center sm:gap-12 sm:pb-11">
    <div class="flex shrink-0 justify-center sm:ml-12 sm:w-40">
        @if ($user->avatar)
            <img src="{{ asset($user->avatar) }}" alt="{{ $user->username }}" class="h-24 w-24 rounded-full border border-neutral-200 object-cover sm:h-[150px] sm:w-[150px]" />
        @else
            <div class="flex h-24 w-24 items-center justify-center rounded-full bg-neutral-200 text-3xl font-bold uppercase text-neutral-600 sm:h-[150px] sm:w-[150px]">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</div>
        @endif
    </div>
    <div class="min-w-0 flex-1">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
            <h1 class="truncate text-2xl font-normal text-neutral-950">{{ $user->username }}</h1>
            <div class="flex flex-wrap items-center gap-2">
                @if ($user->is(auth()->user()))
                    <a href="{{ route('profile.edit') }}" class="ig-button">Edit profile</a>
                    <a href="{{ route('posts.archive.index') }}" class="ig-button">View archive</a>
                    <button type="button" class="ig-button !px-2" aria-label="Settings">•••</button>
                @else
                    <div x-data="postFollow('{{ $user->username }}', @js($followStatus))"><button type="button" @click="toggleFollow()" :disabled="followLoading" x-text="status === 'accepted' ? 'Following' : (status === 'pending' ? 'Requested' : 'Follow')" :class="status ? 'ig-button' : 'ig-button-primary'"></button></div>
                @endif
            </div>
        </div>
        <div x-data="{}" class="mt-5 hidden gap-9 text-base sm:flex">
            <div><span class="font-semibold text-neutral-950">{{ number_format($user->posts_count) }}</span> posts</div>
            <button type="button" @click="window.dispatchEvent(new CustomEvent('open-relationships-modal', { detail: { type: 'followers', username: '{{ $user->username }}' } }))"><span class="font-semibold text-neutral-950">{{ number_format($user->followers_count) }}</span> followers</button>
            <button type="button" @click="window.dispatchEvent(new CustomEvent('open-relationships-modal', { detail: { type: 'following', username: '{{ $user->username }}' } }))"><span class="font-semibold text-neutral-950">{{ number_format($user->following_count) }}</span> following</button>
        </div>
        <div class="mt-5"><p class="font-semibold text-neutral-950">{{ $user->name ?? 'User' }}</p>@if ($user->bio)<p class="mt-1 max-w-xl whitespace-pre-line text-sm leading-5 text-neutral-800">{{ $user->bio }}</p>@endif</div>
    </div>
</section>

<section x-data="{}" class="grid grid-cols-3 border-y border-neutral-200 py-3 text-center text-sm sm:hidden">
    <div><div class="font-semibold">{{ number_format($user->posts_count) }}</div><div class="text-neutral-500">posts</div></div>
    <button type="button" @click="window.dispatchEvent(new CustomEvent('open-relationships-modal', { detail: { type: 'followers', username: '{{ $user->username }}' } }))"><div class="font-semibold">{{ number_format($user->followers_count) }}</div><div class="text-neutral-500">followers</div></button>
    <button type="button" @click="window.dispatchEvent(new CustomEvent('open-relationships-modal', { detail: { type: 'following', username: '{{ $user->username }}' } }))"><div class="font-semibold">{{ number_format($user->following_count) }}</div><div class="text-neutral-500">following</div></button>
</section>

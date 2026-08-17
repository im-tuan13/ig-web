@props(['user'])

<div class="flex items-center gap-3 rounded-xl bg-white p-2" x-data="postFollow('{{ $user->username }}', null)">
    @if ($user->avatar)
        <img src="{{ asset($user->avatar) }}" alt="{{ $user->username }}" class="h-11 w-11 rounded-full object-cover" />
    @else
        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-neutral-200 text-sm font-bold uppercase text-neutral-600">{{ substr($user->name, 0, 1) }}</div>
    @endif
    <div class="min-w-0 flex-1">
        <div class="truncate text-sm font-semibold text-neutral-950">{{ $user->username }}</div>
        <div class="truncate text-xs text-neutral-500">Suggested for you</div>
    </div>
    <button
        type="button"
        @click="toggleFollow()"
        :disabled="followLoading"
        x-text="status === 'accepted' ? 'Following' : (status === 'pending' ? 'Requested' : 'Follow')"
        :class="status ? 'rounded-full border border-neutral-300 bg-white px-3 py-1 text-xs font-semibold text-neutral-900 transition hover:bg-neutral-50' : 'rounded-full bg-sky-500 px-3 py-1 text-xs font-semibold text-white transition hover:bg-sky-600'">
    </button>
</div>

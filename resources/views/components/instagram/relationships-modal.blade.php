@props([])

<div
    x-data="relationshipsModal"
    x-show="open"
    x-cloak
    @keydown.escape.window="close()"
    @open-relationships-modal.window="openModal($event.detail.type, $event.detail.username)"
    class="fixed inset-0 z-50 flex items-end justify-center sm:items-center"
    role="dialog"
    aria-modal="true"
    :aria-label="modalTitle">

    {{-- Backdrop --}}
    <div @click="close()" class="fixed inset-0 z-0 bg-black/50"></div>

    {{-- Modal Panel --}}
    <div
        class="relative z-10 flex w-full max-w-[400px] flex-col overflow-hidden rounded-t-2xl bg-white sm:max-h-[400px] sm:rounded-2xl sm:shadow-xl"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0 opacity-0"
        x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 sm:scale-100 opacity-100"
        x-transition:leave-end="translate-y-full sm:scale-95 sm:translate-y-0 opacity-0">

        {{-- Header --}}
        <div class="relative flex items-center justify-center border-b border-neutral-200 py-3">
            <h2 class="text-base font-semibold tracking-tight text-neutral-900" x-text="modalTitle"></h2>
            <button
                type="button"
                @click="close()"
                class="absolute right-2 rounded-full p-2 text-neutral-500 transition hover:bg-neutral-100 active:bg-neutral-200"
                aria-label="Close">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>
        </div>

        {{-- Search --}}
        <div class="shrink-0 border-b border-neutral-100 px-4 py-2">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <svg class="h-3.5 w-3.5 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-3.5-3.5" />
                    </svg>
                </div>
                <input
                    x-ref="searchInput"
                    x-model="query"
                    type="text"
                    placeholder="Search"
                    autocomplete="off"
                    class="w-full rounded-lg border-0 bg-neutral-100 py-[7px] pl-9 pr-3 text-sm outline-none placeholder:text-neutral-400 focus:bg-neutral-100 focus:ring-0">
            </div>
        </div>

        {{-- List Container (custom thin scrollbar) --}}
        <div class="min-h-0 flex-1 overflow-y-auto [scrollbar-width:thin] [&::-webkit-scrollbar]:w-[4px] [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-neutral-300">
            {{-- Loading Spinner --}}
            <div x-show="loading && users.length === 0" class="flex justify-center py-8">
                <div class="h-5 w-5 animate-spin rounded-full border-2 border-neutral-200 border-t-neutral-900"></div>
            </div>

            {{-- Users List --}}
            <template x-for="user in users" :key="user.id">
                <div
                    class="flex items-center gap-3 px-4 py-[10px] transition hover:bg-neutral-50 active:bg-neutral-100"
                    :class="{ 'opacity-60 pointer-events-none': user.followLoading }">
                    {{-- Avatar (32px like Instagram Web) --}}
                    <a :href="`/u/${user.username}`" class="shrink-0" @click="close()">
                        <template x-if="user.avatar">
                            <img :src="'/' + user.avatar" :alt="user.username" class="h-8 w-8 rounded-full object-cover" />
                        </template>
                        <template x-if="!user.avatar">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-neutral-200 text-[11px] font-bold uppercase text-neutral-600" x-text="user.name.charAt(0)"></div>
                        </template>
                    </a>

                    {{-- Info --}}
                    <div class="min-w-0 flex-1">
                        <a :href="`/u/${user.username}`" @click="close()" class="truncate text-sm font-semibold text-neutral-950 hover:underline" x-text="user.username"></a>
                        <div class="truncate text-[13px] leading-4 text-neutral-500" x-text="user.name"></div>
                    </div>

                    {{-- Follow/Following Button --}}
                    <template x-if="user.id !== {{ auth()->id() }}">
                        <button
                            type="button"
                            @click="toggleFollow(user)"
                            :disabled="user.followLoading"
                            class="shrink-0 rounded-lg px-[14px] py-[5px] text-[13px] font-semibold transition active:scale-95 disabled:opacity-50"
                            :class="user.is_followed_by_current_user
                                ? 'border border-solid border-neutral-300 bg-white text-neutral-900 hover:bg-neutral-50 active:bg-neutral-100'
                                : 'bg-sky-500 text-white hover:bg-sky-600 active:bg-sky-700'">
                            <span x-text="user.is_followed_by_current_user ? 'Following' : 'Follow'"></span>
                        </button>
                    </template>
                </div>
            </template>

            {{-- Sentinel for IntersectionObserver (lazy load) --}}
            <div
                x-ref="sentinel"
                x-intersect="loadMore()"
                class="h-px"
                x-show="hasMore && !loading"
                aria-hidden="true">
            </div>

            {{-- Loading more spinner (bottom) --}}
            <div x-show="loading && users.length > 0" class="flex justify-center py-4">
                <div class="h-4 w-4 animate-spin rounded-full border-2 border-neutral-200 border-t-neutral-900"></div>
            </div>

            {{-- Empty state --}}
            <div x-show="!loading && users.length === 0" class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <div class="mx-auto mb-4 flex h-[62px] w-[62px] items-center justify-center rounded-full border-2 border-neutral-300">
                    <svg class="h-6 w-6 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-neutral-900" x-text="query ? 'No results found' : 'No ' + type + ' yet'"></p>
                <p class="mt-1 text-xs text-neutral-500" x-text="query ? 'Try a different search term.' : 'When someone ' + (type === 'followers' ? 'follows this account' : 'this account follows someone') + ', it will show up here.'"></p>
            </div>
        </div>

    </div>
</div>

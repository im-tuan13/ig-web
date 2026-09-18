@props(['standalone' => false])

<div
    x-data="{
        standalone: {{ $standalone ? 'true' : 'false' }},
        query: '',
        results: { users: [], posts: [], hashtags: [] },
        loading: false,
        timer: null,

        init() {
            this.$watch('query', (value) => {
                if (value.trim() === '') {
                    this.results = { users: [], posts: [], hashtags: [] };
                    return;
                }
                this.search(value);
            });

            if (!this.standalone) {
                this.$nextTick(() => this.focusInput());
            }
        },

        async fetchJson(url) {
            try {
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                return response.ok ? await response.json() : [];
            } catch (e) {
                console.error(e);
                return [];
            }
        },

        async search(keyword) {
            clearTimeout(this.timer);

            this.timer = setTimeout(async () => {
                this.loading = true;

                const q = encodeURIComponent(keyword);
                const [users, posts, hashtags] = await Promise.all([
                    this.fetchJson(`/search/users?q=${q}`),
                    this.fetchJson(`/search/posts?q=${q}`),
                    this.fetchJson(`/search/hashtags?q=${q}`),
                ]);

                this.results = { users, posts, hashtags };
                this.loading = false;
            }, 250);
        },

        focusInput() {
            this.$nextTick(() => this.$refs.searchInput.focus());
        },

        hasResults() {
            return this.results.users.length + this.results.posts.length + this.results.hashtags.length > 0;
        },
    }"
    class="flex h-full flex-col">

    <div class="relative mb-6">
        <div class="pointer-events-none absolute inset-y-0 left-4 flex items-center">
            🔍
        </div>

        <input
            id="search-input"
            x-ref="searchInput"
            x-model="query"
            type="text"
            autocomplete="off"
            placeholder="Search"
            class="w-full rounded-xl border border-neutral-200 bg-neutral-100 py-3 pl-11 pr-4">
    </div>

    <div x-show="loading" class="py-8 text-center">
        Loading...
    </div>

    <div x-show="query.length == 0 && !loading" class="py-10 text-center">
        <p class="font-semibold text-neutral-950">Recent searches</p>
        <p class="mt-2 text-sm text-neutral-500">Search for users, posts, or tags</p>
    </div>

    <div x-show="query.length > 0 && !loading" class="space-y-6 overflow-y-auto">

        <template x-if="!hasResults()">
            <div class="text-center py-10">
                No results found
            </div>
        </template>

        <template x-if="results.users.length > 0">
            <div>
                <div class="mb-2 text-sm font-semibold text-neutral-500">Accounts</div>
                <template x-for="user in results.users" :key="user.username">
                    <a
                        :href="user.profile_url"
                        class="flex items-center gap-3 rounded-lg p-2 hover:bg-neutral-100">

                        <img
                            x-show="user.avatar"
                            :src="user.avatar"
                            class="h-11 w-11 rounded-full object-cover">

                        <div
                            x-show="!user.avatar"
                            class="flex h-11 w-11 items-center justify-center rounded-full bg-neutral-200"
                            x-text="user.name.charAt(0)">
                        </div>

                        <div>
                            <div class="font-semibold" x-text="user.username"></div>
                            <div class="text-sm text-neutral-500" x-text="user.name"></div>
                        </div>
                    </a>
                </template>
            </div>
        </template>

        <template x-if="results.hashtags.length > 0">
            <div>
                <div class="mb-2 text-sm font-semibold text-neutral-500">Tags</div>
                <template x-for="tag in results.hashtags" :key="tag.name">
                    <a :href="tag.url" class="flex items-center gap-3 rounded-lg p-2 hover:bg-neutral-100">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-neutral-200 text-lg">
                            #
                        </div>
                        <div>
                            <div class="font-semibold" x-text="'#' + tag.name"></div>
                            <div class="text-sm text-neutral-500" x-text="tag.posts_count + ' posts'"></div>
                        </div>
                    </a>
                </template>
            </div>
        </template>

        <template x-if="results.posts.length > 0">
            <div>
                <div class="mb-2 text-sm font-semibold text-neutral-500">Posts</div>
                <div class="grid grid-cols-3 gap-1">
                    <template x-for="post in results.posts" :key="post.id">
                        <a :href="post.post_url" class="aspect-square overflow-hidden rounded">
                            <img :src="post.thumbnail" :alt="post.caption" class="h-full w-full object-cover">
                        </a>
                    </template>
                </div>
            </div>
        </template>

    </div>

</div>

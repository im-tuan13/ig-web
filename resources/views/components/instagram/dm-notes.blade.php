@props(['notes'])

<section
    class="border-b border-[#262626] px-4 py-4"
    x-data="{
        open: false,
        content: '',
        submitting: false,
        error: '',

        openComposer() {
            this.error = '';
            this.open = true;
        },

        async submitNote() {
            if (!this.content.trim() || this.submitting) return;

            this.error = '';
            this.submitting = true;

            try {
                const response = await fetch('{{ route('notes.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                    },
                    body: JSON.stringify({ content: this.content.trim() }),
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.message || `Request failed (${response.status}).`);
                }

                this.open = false;
                this.content = '';
                window.location.reload();
            } catch (e) {
                this.error = e.message || 'Something went wrong. Please try again.';
            } finally {
                this.submitting = false;
            }
        },
    }"
>
    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-xs font-semibold text-white">Notes</h2>
        <button type="button" @click="openComposer()" class="text-xs text-[#a8a8a8] transition hover:text-white">
            Your note
        </button>
    </div>

    <div class="flex gap-4 overflow-x-auto pb-1 [scrollbar-width:none]">
        @forelse($notes as $note)
            <form method="POST" action="{{ route('messages.start', ['user' => $note->user->username]) }}" class="w-16 shrink-0 text-center">
                @csrf
                <input type="hidden" name="username" value="{{ $note->user->username }}">
                <button type="submit" class="group w-full text-center" aria-label="Open chat with {{ $note->user->username }}">
                    <div class="relative mx-auto h-14 w-14 rounded-full bg-gradient-to-tr from-[#feda75] via-[#d62976] to-[#4f5bd5] p-[2px]">
                        <div class="h-full w-full overflow-hidden rounded-full bg-[#121212] p-[2px]">
                            @if($note->user->avatar)
                                <img src="{{ asset($note->user->avatar) }}" alt="{{ $note->user->username }}" class="h-full w-full rounded-full object-cover">
                            @else
                                <div class="flex h-full items-center justify-center rounded-full bg-[#262626] text-sm text-white">
                                    {{ strtoupper(substr($note->user->username, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <span class="absolute -top-2 left-1/2 max-w-[8rem] -translate-x-1/2 truncate rounded-xl bg-white px-2 py-1 text-[10px] font-medium leading-tight text-black shadow-lg" title="{{ $note->content }}">
                            {{ $note->content }}
                        </span>
                        <span class="absolute -bottom-1 -right-1 rounded-full bg-white px-1 text-[10px] text-black">💬</span>
                    </div>
                    <p class="mt-2 truncate text-[11px] text-[#a8a8a8] group-hover:text-white">
                        {{ $note->user_id === auth()->id() ? 'Your note' : $note->user->username }}
                    </p>
                </button>
            </form>
        @empty
            <p class="self-center text-xs text-[#737373]">Share a thought with friends.</p>
        @endforelse

        <button type="button" @click="openComposer()" class="w-16 shrink-0 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-dashed border-[#555] text-xl text-[#a8a8a8] transition hover:border-white hover:text-white">+</div>
            <p class="mt-2 text-[11px] text-[#a8a8a8]">New note</p>
        </button>
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        @keydown.escape.window="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
        role="dialog"
        aria-modal="true"
    >
        <form @submit.prevent="submitNote()" @click.outside="open = false" class="w-full max-w-sm rounded-xl border border-[#262626] bg-[#121212] p-5 shadow-2xl">
            <h3 class="mb-4 text-lg font-semibold text-white">Share a note</h3>
            <textarea
                x-model="content"
                maxlength="60"
                required
                :disabled="submitting"
                autofocus
                class="h-24 w-full resize-none rounded-lg border border-[#363636] bg-[#1c1c1c] p-3 text-sm text-white placeholder-[#737373] outline-none focus:border-[#a8a8a8] focus:ring-0 disabled:opacity-50"
                placeholder="Share a thought..."
            ></textarea>
            <p x-show="error" x-text="error" class="mt-2 text-xs text-red-400"></p>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" @click="open = false" class="rounded-lg px-4 py-2 text-sm text-[#a8a8a8] transition hover:text-white">Cancel</button>
                <button type="submit" :disabled="!content.trim() || submitting" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-black transition hover:bg-[#e5e5e5] disabled:opacity-50" x-text="submitting ? 'Sharing...' : 'Share'"></button>
            </div>
        </form>
    </div>
</section>

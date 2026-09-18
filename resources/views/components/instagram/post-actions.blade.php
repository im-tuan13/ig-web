@props(['modal' => false])

<div @class(['flex items-center gap-3 text-neutral-700', 'order-2 shrink-0' => $modal])>
    <button type="button" @click="toggle" :aria-label="liked ? 'Unlike' : 'Like'" :disabled="loading" class="rounded-full p-1.5 transition hover:scale-110 hover:bg-neutral-100 disabled:opacity-50">
        <svg class="h-6 w-6 transition-transform" viewBox="0 0 24 24" :fill="liked ? 'currentColor' : 'none'" :class="liked ? 'scale-110 text-red-500' : ''" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.8 4.6c-1.8-1.8-4.8-1.8-6.6 0L12 6.8 9.8 4.6C8 2.8 5 2.8 3.2 4.6s-1.8 4.8 0 6.6L12 20l8.8-8.8c1.8-1.8 1.8-4.8 0-6.6z"/></svg>
    </button>
    <button type="button" @click="$refs.commentInput.focus()" aria-label="Comment" class="rounded-full p-1.5 transition hover:bg-neutral-100"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.8 8.8 0 0 1-3.5-.7L3 21l1.8-5.1a8 8 0 0 1-1-4.4 8.4 8.4 0 0 1 17.2 0z"/></svg></button>
    <button type="button" @click="openPicker" aria-label="Share" class="rounded-full p-1.5 transition hover:bg-neutral-100"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/></svg></button>
    <button type="button" @click="toggleSave" :aria-label="saved ? 'Unsave' : 'Save'" :disabled="saveLoading" class="ml-auto rounded-full p-1.5 transition hover:bg-neutral-100 disabled:opacity-50"><svg class="h-6 w-6" viewBox="0 0 24 24" :fill="saved ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2"><path d="M6 3h12v18l-6-4-6 4z"/></svg></button>
</div>

<div x-show="pickerOpen" x-cloak @keydown.escape.window="closePicker()" class="fixed inset-0 z-[70] flex items-end justify-center bg-black/50 sm:items-center" style="display:none">
    <div @click.outside="closePicker()" class="flex max-h-[80vh] w-full max-w-sm flex-col rounded-t-2xl bg-white sm:rounded-2xl">
        <div class="flex items-center justify-between border-b border-neutral-200 px-4 py-3"><span class="text-sm font-semibold">Send to</span><button type="button" @click="closePicker()" aria-label="Close">✕</button></div>
        <div class="flex-1 overflow-y-auto px-4 py-2">
            <div x-show="loadingConversations" class="py-8 text-center text-sm text-neutral-500">Loading...</div>
            <template x-for="conversation in conversations" :key="conversation.id"><label class="flex cursor-pointer items-center gap-3 py-2.5"><div class="flex h-11 w-11 items-center justify-center rounded-full bg-neutral-200 text-sm font-bold uppercase text-neutral-600" x-text="conversation.name.charAt(0)"></div><span class="min-w-0 flex-1 truncate text-sm" x-text="conversation.name"></span><input type="checkbox" :checked="selected.includes(conversation.id)" @change="toggleSelect(conversation.id)" class="h-5 w-5 rounded-full text-sky-500"></label></template>
            <div x-show="!loadingConversations && conversations.length === 0" class="py-8 text-center text-sm text-neutral-500">No conversations yet.</div>
        </div>
        <div class="border-t border-neutral-200 px-4 py-3"><button type="button" @click="sendToSelected" :disabled="selected.length === 0 || sending" class="w-full rounded-lg bg-sky-500 py-2.5 text-sm font-semibold text-white disabled:opacity-50"><span x-show="!sending && !sent">Send (<span x-text="selected.length"></span>)</span><span x-show="sending">Sending...</span><span x-show="sent">Sent!</span></button><button type="button" @click="copyLink(); closePicker()" class="mt-2 w-full text-center text-sm text-neutral-600 hover:underline">Copy link / Share via device</button></div>
    </div>
</div>

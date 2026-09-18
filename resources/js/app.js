import '../css/app.css';
import './bootstrap';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

Alpine.plugin(intersect);

window.Alpine = Alpine;

Alpine.data('storyViewer', ({ nextUrl, previousUrl, closeUrl, currentOwnerStoryIndex }) => ({
    progress: 0,
    timer: null,
    hoverPaused: false,
    holding: false,
    tabPaused: document.hidden,
    viewersOpen: false,
    duration: 5000,
    visibilityChangeListener: null,

    init() {
        this.visibilityChangeListener = () => {
            this.tabPaused = document.hidden;
        };

        document.addEventListener('visibilitychange', this.visibilityChangeListener);

        this.timer = window.setInterval(() => {
            if (this.isPaused()) return;

            this.progress = Math.min(this.progress + (100 / (this.duration / 100)), 100);

            if (this.progress === 100) {
                window.clearInterval(this.timer);
                this.next();
            }
        }, 100);
    },

    destroy() {
        window.clearInterval(this.timer);
        document.removeEventListener('visibilitychange', this.visibilityChangeListener);
    },

    hold(event) {
        this.holding = true;
        event.currentTarget.setPointerCapture?.(event.pointerId);
    },

    releaseHold(event) {
        this.holding = false;

        if (event.currentTarget.hasPointerCapture?.(event.pointerId)) {
            event.currentTarget.releasePointerCapture(event.pointerId);
        }
    },

    isPaused() {
        return this.hoverPaused || this.holding || this.viewersOpen || this.tabPaused;
    },

    segmentProgress(index) {
        if (index < currentOwnerStoryIndex) return 100;
        if (index > currentOwnerStoryIndex) return 0;

        return this.progress;
    },

    next() {
        window.location.assign(nextUrl);
    },

    previous() {
        if (previousUrl) window.location.assign(previousUrl);
    },

    close() {
        window.location.assign(closeUrl);
    },
}));

window.postLike = (postId, initialLikesCount, initiallyLiked) => ({
    liked: initiallyLiked,
    likesCount: initialLikesCount,
    loading: false,

    async toggle() {
        if (this.loading) return;

        const previous = {
            liked: this.liked,
            likesCount: this.likesCount,
        };

        this.liked = !this.liked;
        this.likesCount += this.liked ? 1 : -1;
        this.loading = true;

        try {
            const response = await fetch(`/posts/${postId}/likes`, {
                method: this.liked ? 'POST' : 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!response.ok) throw new Error();

            const data = await response.json();

            this.liked = data.liked;
            this.likesCount = data.likes_count;
        } catch {
            this.liked = previous.liked;
            this.likesCount = previous.likesCount;
        } finally {
            this.loading = false;
        }
    },
});

window.postFollow = (username, initialStatus) => ({
    status: initialStatus, // 'accepted' | 'pending' | null
    followLoading: false,

    async toggleFollow() {
        if (this.followLoading) return;

        const previousStatus = this.status;
        const isFollowingOrPending = this.status === 'accepted' || this.status === 'pending';
        this.followLoading = true;

        try {
            const response = await fetch(`/ajax/users/${username}/follow`, {
                method: isFollowingOrPending ? 'DELETE' : 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!response.ok) throw new Error();

            const data = await response.json();

            this.status = data.status ?? (data.followed ? 'accepted' : null);
        } catch {
            this.status = previousStatus;
        } finally {
            this.followLoading = false;
        }
    },
});

window.postSave = (postId, initiallySaved) => ({
    saved: initiallySaved,
    saveLoading: false,

    async toggleSave() {
        if (this.saveLoading) return;

        const previous = this.saved;

        this.saved = !this.saved;
        this.saveLoading = true;

        try {
            const response = await fetch(`/posts/${postId}/saves`, {
                method: this.saved ? 'POST' : 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!response.ok) throw new Error();

            const data = await response.json();
            this.saved = data.saved;

            // If user just unsaved, notify the saved tab to remove this post
            if (!data.saved) {
                window.dispatchEvent(new CustomEvent('post-unsaved', { detail: { postId } }));
            }
        } catch {
            this.saved = previous;
        } finally {
            this.saveLoading = false;
        }
    },
});

window.postComment = (postId, initialComments, initialCommentsCount) => ({
    comments: initialComments,
    commentsCount: initialCommentsCount,
    comment: '',
    commentLoading: false,

    async deleteComment(commentId) {
        try {
            const response = await fetch(`/posts/${postId}/comments/${commentId}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!response.ok) throw new Error();

            const data = await response.json();
            this.comments = this.comments.filter(commentItem => commentItem.id !== commentId);
            this.commentsCount = data.comments_count;
        } catch {}
    },

    async submitComment() {
        if (!this.comment.trim() || this.commentLoading) return;

        this.commentLoading = true;

        try {
            const response = await fetch(`/posts/${postId}/comments`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    comment: this.comment,
                }),
            });

            if (!response.ok) throw new Error();

            const data = await response.json();

            this.comments.push(data.comment);
            this.commentsCount = data.comments_count;
            this.comment = '';
        } finally {
            this.commentLoading = false;
        }
    },
});

window.postShare = (postId) => ({
    pickerOpen: false, loadingConversations: false, conversations: [], selected: [], sending: false, sent: false,
    async openPicker() {
        this.pickerOpen = true;
        if (this.conversations.length) return;
        this.loadingConversations = true;
        try { const response = await fetch('/share/conversations', { headers: { Accept: 'application/json' } }); if (response.ok) this.conversations = await response.json(); } catch {}
        this.loadingConversations = false;
    },
    closePicker() { this.pickerOpen = false; this.selected = []; this.sent = false; },
    toggleSelect(id) { this.selected = this.selected.includes(id) ? this.selected.filter((existing) => existing !== id) : [...this.selected, id]; },
    async sendToSelected() {
        if (!this.selected.length || this.sending) return;
        this.sending = true;
        try { const response = await fetch(`/posts/${postId}/share`, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ conversation_ids: this.selected }) }); if (!response.ok) throw new Error(); this.sent = true; this.selected = []; window.setTimeout(() => this.closePicker(), 1200); } catch {} finally { this.sending = false; }
    },
    async copyLink() {
        const url = `${window.location.origin}/posts/${postId}`;
        try { if (navigator.share) await navigator.share({ title: 'Instagram', url }); else await navigator.clipboard.writeText(url); } catch {}
    },
});

/*
|--------------------------------------------------------------------------
| Instagram Search (Realtime)
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {

    const input = document.getElementById('search-input');
    const results = document.getElementById('search-results');

    if (!input || !results) {
        return;
    }

    let timer = null;

    input.addEventListener('input', () => {

        clearTimeout(timer);

        timer = setTimeout(async () => {

            const keyword = input.value.trim();

            const response = await fetch(`/search/users?q=${encodeURIComponent(keyword)}`, {
                headers: {
                    Accept: 'application/json'
                }
            });

            const users = await response.json();

            results.innerHTML = '';

            if (users.length === 0) {
                results.innerHTML =
                    '<div class="py-10 text-center text-neutral-500">No users found.</div>';
                return;
            }

            users.forEach(user => {

                const avatar = user.avatar
                    ? `<img src="/${user.avatar}" class="h-12 w-12 rounded-full object-cover">`
                    : `<div class="flex h-12 w-12 items-center justify-center rounded-full bg-neutral-200 font-semibold">${user.name.charAt(0)}</div>`;

                results.insertAdjacentHTML('beforeend', `
                    <a href="/u/${user.username}" class="flex items-center gap-3 rounded-xl p-2 hover:bg-neutral-100 transition">
                        ${avatar}
                        <div class="min-w-0">
                            <div class="truncate font-semibold">${user.username}</div>
                            <div class="truncate text-sm text-neutral-500">${user.name}</div>
                        </div>
                    </a>
                `);

            });

        }, 250);

    });

});

document.addEventListener('DOMContentLoaded', () => {
    const feed = document.getElementById('feed-container');
    const sentinel = document.getElementById('feed-sentinel');
    const loader = document.getElementById('feed-loader');
    const endMessage = document.getElementById('feed-end');
    const retry = document.getElementById('feed-retry');
    const retryButton = document.getElementById('feed-retry-button');

    if (!feed || !sentinel || !loader || !endMessage || !retry || !retryButton) return;

    let nextPage = sentinel.dataset.nextPage;
    let loading = false;
    let observer;

    const loadMorePosts = async () => {
        if (loading || !nextPage) return;

        observer.unobserve(sentinel);

        loading = true;
        loader.classList.remove('hidden');
        retry.classList.add('hidden');
        let shouldResumeObserving = false;

        try {
            const response = await fetch(nextPage, {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) throw new Error('Unable to load more posts.');

            const posts = await response.text();

            if (posts.trim()) {
                const template = document.createElement('template');
                template.innerHTML = posts;

                const appendedPosts = Array.from(template.content.children);
                feed.append(template.content);
                appendedPosts.forEach((post) => Alpine.initTree(post));
            }

            nextPage = response.headers.get('X-Next-Page');

            if (!nextPage) {
                observer.disconnect();
                sentinel.remove();
                endMessage.classList.remove('hidden');
            } else {
                shouldResumeObserving = true;
            }
        } catch (error) {
            console.error(error);
            retry.classList.remove('hidden');
        } finally {
            loading = false;
            loader.classList.add('hidden');

            if (shouldResumeObserving) observer.observe(sentinel);
        }
    };

    observer = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting) loadMorePosts();
    }, {
        rootMargin: '400px 0px',
    });

    retryButton.addEventListener('click', loadMorePosts);
    observer.observe(sentinel);
});

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('post-modal');
    const modalContent = document.getElementById('post-modal-content');
    const closeButton = document.getElementById('post-modal-close');

    if (!modal || !modalContent || !closeButton) return;

    let requestController = null;
    let modalIsOpen = false;

    const setModalVisibility = (visible) => {
        modal.classList.toggle('hidden', !visible);
        modal.classList.toggle('flex', visible);
        modal.setAttribute('aria-hidden', String(!visible));
        document.body.classList.toggle('overflow-hidden', visible);
        modalIsOpen = visible;
    };

    const closeModal = () => {
        if (modalIsOpen) history.back();
    };

    const loadPost = async (url, updateHistory = false) => {
        requestController?.abort();
        requestController = new AbortController();

        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: requestController.signal,
            });

            if (!response.ok) throw new Error('Unable to load post.');

            const post = await response.text();
            const template = document.createElement('template');
            template.innerHTML = post;

            modalContent.replaceChildren(template.content);
            Alpine.initTree(modalContent);

            if (updateHistory) {
                const state = { postModal: true };

                if (modalIsOpen) {
                    history.replaceState(state, '', url);
                } else {
                    history.pushState(state, '', url);
                }
            }

            setModalVisibility(true);
        } catch (error) {
            if (error.name !== 'AbortError') console.error(error);
        }
    };

    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-post-modal]');

        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        event.preventDefault();
        loadPost(link.href, true);
    });

    closeButton.addEventListener('click', closeModal);

    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeModal();
    });

    window.addEventListener('popstate', (event) => {
        if (event.state?.postModal) {
            loadPost(window.location.href);
            return;
        }

        requestController?.abort();
        modalContent.replaceChildren();
        setModalVisibility(false);
    });
});

/*
|--------------------------------------------------------------------------
| Sidebar (search/notif panel toggles + notification & DM unread counts)
|--------------------------------------------------------------------------
*/
Alpine.data('sidebar', () => ({
    searchOpen: false,
    notifOpen: false,
    count: 0,
    dmCountValue: 0,
    notifPollInterval: null,
    dmPollInterval: null,

    init() {
        this.count = parseInt(document.getElementById('notification-badge-count')?.textContent || '0');
        this.dmCountValue = parseInt(document.getElementById('dm-badge-count')?.textContent || '0');

        this.notifPollInterval = setInterval(async () => {
            try {
                const response = await fetch('/notifications/count', {
                    headers: { Accept: 'application/json' },
                });

                if (response.ok) {
                    const data = await response.json();
                    this.count = data.count;
                }
            } catch {}
        }, 15000);

        this.dmPollInterval = setInterval(async () => {
            try {
                const response = await fetch('/messages/unread/count', {
                    headers: { Accept: 'application/json' },
                });

                if (response.ok) {
                    const data = await response.json();
                    this.dmCountValue = data.count;
                }
            } catch {}
        }, 15000);
    },

    destroy() {
        if (this.notifPollInterval) clearInterval(this.notifPollInterval);
        if (this.dmPollInterval) clearInterval(this.dmPollInterval);
    },
}));

/*
|--------------------------------------------------------------------------
| DM Chat (Conversation)
|--------------------------------------------------------------------------
*/
window.dmChat = (conversationId, lastCreatedAt, currentUserId) => ({
    newMessage: '',
    pickerOpen: false,
    emojis: ['😀','😂','😍','🥳','😭','🔥','❤️','👍','🙏','✨','😎','🎉','💯','🤗','😴','👏'],
    stickers: [{key:'heart',emoji:'💖'},{key:'laugh',emoji:'😂'},{key:'party',emoji:'🎉'},{key:'fire',emoji:'🔥'},{key:'cat',emoji:'😺'}],
    sending: false,
    pollInterval: null,
    lastCreatedAt: lastCreatedAt || null,

    init() {
        this.formatInitialTimes();
        this.scrollToBottom();
        this.$watch('newMessage', () => {});

        this.pollInterval = setInterval(() => {
            this.pollMessages();
        }, 3000);
    },

    destroy() {
        if (this.pollInterval) clearInterval(this.pollInterval);
    },

    scrollToBottom() {
        this.$nextTick(() => {
            const container = this.$refs.messagesContainer;
            if (container) container.scrollTop = container.scrollHeight;
        });
    },

    async sendMessage() {
        if (!this.newMessage.trim() || this.sending) return;

        this.sending = true;

        try {
            const response = await fetch(`/messages/${conversationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ message: this.newMessage, type: 'text' }),
            });

            if (!response.ok) throw new Error();

            const message = await response.json();
            this.lastCreatedAt = message.created_at;

            const container = this.$refs.messagesContainer;

            this.appendMessage(message, true);
            this.scrollToBottom();
            this.newMessage = '';

            // Mark as read
            await fetch(`/messages/${conversationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
        } catch {
            // silent
        } finally {
            this.sending = false;
        }
    },

    async pollMessages() {
        try {
            const params = new URLSearchParams();
            if (this.lastCreatedAt) params.set('after', this.lastCreatedAt);

            const response = await fetch(`/messages/${conversationId}/poll?${params}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) return;

            const messages = await response.json();

            if (messages.length === 0) return;

            const container = this.$refs.messagesContainer;
            const wasNearBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 100;

            messages.forEach((message) => {
                if (this.messageExists(message.id)) return;
                this.appendMessage(message, parseInt(message.user_id) === parseInt(currentUserId));
            });

            this.lastCreatedAt = messages[messages.length - 1].created_at;

            if (wasNearBottom) this.scrollToBottom();

            // Mark as read
            await fetch(`/messages/${conversationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
        } catch {}
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    messageExists(id) {
        return [...this.$refs.messagesContainer.querySelectorAll('[data-message-id]')]
            .some((element) => String(element.dataset.messageId) === String(id));
    },

    formatInitialTimes() {
        this.$refs.messagesContainer?.querySelectorAll('.message-time').forEach((time) => {
            time.textContent = this.formatTime(time.dateTime);
        });
        this.$refs.messagesContainer?.querySelectorAll('[data-date-divider]').forEach((divider) => {
            divider.querySelector('span').textContent = this.formatDate(divider.dataset.date);
        });
    },

    formatTime(timestamp) {
        return new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },

    dateKey(timestamp) {
        const date = new Date(timestamp);
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    },

    formatDate(timestamp) {
        const date = new Date(timestamp);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(today.getDate() - 1);
        if (this.dateKey(timestamp) === this.dateKey(today)) return 'Today';
        if (this.dateKey(timestamp) === this.dateKey(yesterday)) return 'Yesterday';
        return date.toLocaleDateString([], { month: 'long', day: 'numeric', year: 'numeric' });
    },

    appendMessage(message, mine) {
        const container = this.$refs.messagesContainer;
        if (this.messageExists(message.id)) return;
        const key = this.dateKey(message.created_at);
        const lastMessage = container.querySelector('[data-message-created-at]:last-of-type');
        const lastKey = lastMessage ? this.dateKey(lastMessage.dataset.messageCreatedAt) : null;
        if (key !== lastKey) {
            const divider = document.createElement('div');
            divider.className = 'my-5 text-center text-xs font-semibold text-[#737373]';
            divider.dataset.dateDivider = '';
            divider.dataset.date = message.created_at;
            divider.innerHTML = `<span class="rounded-full bg-[#1c1c1c] px-3 py-1">${this.formatDate(message.created_at)}</span>`;
            container.appendChild(divider);
        }
        const div = document.createElement('div');
        div.className = 'mb-3 flex items-end gap-2 ' + (mine ? 'justify-end' : 'justify-start');
        div.dataset.messageId = message.id;
        div.dataset.messageCreatedAt = message.created_at;
        div.innerHTML = this.messageHtml(message, mine);
        container.appendChild(div);
    },

    insertEmoji(emoji) { this.newMessage += emoji; this.$nextTick(() => this.$refs.messageInput?.focus()); },

    async sendSticker(key) {
        if (this.sending) return;
        this.sending = true;
        try {
            const response = await fetch(`/messages/${conversationId}`, { method: 'POST', headers: {'Content-Type':'application/json', Accept:'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify({type:'sticker', sticker_key:key, message:''}) });
            if (!response.ok) throw new Error('Sticker could not be sent.');
            const message = await response.json();
            this.lastCreatedAt = message.created_at;
            this.appendMessage(message, true); this.scrollToBottom(); this.pickerOpen = false;
        } catch { this.error = 'Sticker could not be sent.'; } finally { this.sending = false; }
    },

    messageHtml(message, mine) {
        const stickers = {heart:'💖', laugh:'😂', party:'🎉', fire:'🔥', cat:'😺'};
        if (message.type === 'sticker') return `<div class="px-1 text-6xl" aria-label="Sticker">${stickers[message.sticker_key] || '✨'}</div>`;
        return `<div class="max-w-[75%] rounded-2xl px-4 py-2.5 text-sm leading-relaxed ${mine ? 'bg-sky-500 text-white' : 'bg-neutral-100 text-neutral-900'}"><p>${this.escapeHtml(message.message || '')}</p><time class="mt-0.5 block text-right text-[10px] opacity-70" datetime="${message.created_at}">${this.formatTime(message.created_at)}</time></div>`;
    },
});

window.callManager = (currentUserId) => ({
    open: false, incoming: false, call: null, pc: null, stream: null, signalCursor: 0, poller: null, incomingPoll: null, timeout: null,
    title: '', statusLabel: '', error: '', muted: false, cameraOff: false, callerInitial: '📞',
    config: { iceServers: [{ urls: 'stun:stun.l.google.com:19302' }, { urls: 'stun:stun1.l.google.com:19302' }] },
    init() {
        this.startIncomingPoll();
        this.onStart = (event) => this.start(event.detail.userId);
        window.addEventListener('start-call', this.onStart);
    },
    destroy() { window.removeEventListener('start-call', this.onStart); this.stopPolling(); if (this.incomingPoll) clearInterval(this.incomingPoll); this.incomingPoll = null; this.cleanup(); },
    startIncomingPoll() { this.pollIncoming(); this.incomingPoll = setInterval(() => this.pollIncoming(), 2500); },
    async pollIncoming() { if (this.open && !this.incoming) return; try { const r = await fetch('/calls/incoming', {headers:{Accept:'application/json'}}); if (!r.ok) return; const payload = await r.json(); const call = payload?.call; if (payload?.has_call === true && this.validCallId(call)) { if (!this.open) this.showIncoming(call); return; } if (payload?.has_call === false || this.incoming) this.dismissIncoming(); } catch {} },
    validCallId(call = this.call) { const id = call?.id; return (typeof id === 'number' && Number.isSafeInteger(id) && id > 0) || (typeof id === 'string' && /^[1-9]\d*$/.test(id)); },
    callId() { return this.validCallId() ? String(this.call.id) : null; },
    dismissIncoming() { if (!this.incoming) return; this.cleanup(); this.open = false; this.incoming = false; this.call = null; this.signalCursor = 0; },
    async start(userId) { this.error = ''; this.incoming = false; this.statusLabel = 'Calling...'; this.open = true; try { const r = await this.api('/calls', 'POST', {receiver_id:userId,type:'video'}); if (!this.validCallId(r)) throw new Error('Invalid call response.'); this.call = r; this.signalCursor = 0; this.title = r.receiver?.username || 'User'; await this.prepareMedia(); await this.createPeer(true); this.timeout = setTimeout(() => this.endCall('timeout'), 35000); } catch (e) { this.error = e.message || 'Unable to start call.'; } },
    showIncoming(call) { if (!this.validCallId(call)) return; this.call = call; this.signalCursor = 0; this.open = true; this.incoming = true; this.title = call.caller?.username || 'Incoming call'; this.callerInitial = (this.title[0] || '📞').toUpperCase(); this.statusLabel = 'Incoming video call'; this.timeout = setTimeout(() => this.endCall('timeout'), 35000); },
    async accept() { const id = this.callId(); if (!id) { this.dismissIncoming(); return; } this.incoming = false; this.statusLabel = 'Connecting...'; try { await this.api(`/calls/${id}/action`, 'POST', {action:'accept'}); await this.prepareMedia(); await this.createPeer(false); } catch (e) { this.error = e.message || 'Camera or microphone permission is required.'; } },
    async decline() { const id = this.callId(); if (id) await this.api(`/calls/${id}/action`, 'POST', {action:'decline'}).catch(()=>{}); this.closeUi('User declined'); },
    async prepareMedia() { if (!navigator.mediaDevices?.getUserMedia) throw new Error('This browser does not support video calls.'); try { this.stream = await navigator.mediaDevices.getUserMedia({video:true,audio:true}); this.$refs.localVideo.srcObject = this.stream; } catch { throw new Error('Camera/microphone permission was denied or the device is unavailable.'); } },
    async createPeer(isCaller) { const id = this.callId(); if (!id) throw new Error('Invalid call ID.'); this.pc = new RTCPeerConnection(this.config); this.stream.getTracks().forEach(track => this.pc.addTrack(track, this.stream)); this.pc.ontrack = e => { this.$refs.remoteVideo.srcObject = e.streams[0]; }; this.pc.onicecandidate = e => { if (e.candidate && this.callId() === id) this.api(`/calls/${id}/signals`, 'POST', {kind:'ice',payload:e.candidate.toJSON()}).catch(()=>{}); }; this.pc.onconnectionstatechange = () => { if (['failed','disconnected'].includes(this.pc.connectionState)) this.error = 'WebRTC connection failed. Check your network and try again.'; if (this.pc.connectionState === 'connected' && this.callId() === id) { this.statusLabel = 'Connected'; this.api(`/calls/${id}/connected`, 'POST', {}).catch(()=>{}); } }; this.startSignalPoll(); if (isCaller) { const offer = await this.pc.createOffer(); await this.pc.setLocalDescription(offer); await this.api(`/calls/${id}/signals`, 'POST', {kind:'offer',payload:offer}); } },
    startSignalPoll() { this.stopPolling(); this.poller = setInterval(() => this.readSignals(), 700); },
    async readSignals() { const id = this.callId(); if (!id) { this.dismissIncoming(); return; } try { const response = await fetch(`/calls/${id}/signals?after=${this.signalCursor}`, {headers:{Accept:'application/json'}}); if (!response.ok) return; const result = await response.json(); const signals = result.signals || []; for (const s of signals) { this.signalCursor = Math.max(this.signalCursor, s.id); if (s.kind === 'offer' && !this.pc.currentRemoteDescription) { await this.pc.setRemoteDescription(s.payload); const answer = await this.pc.createAnswer(); await this.pc.setLocalDescription(answer); if (this.callId() !== id) return; await this.api(`/calls/${id}/signals`, 'POST', {kind:'answer',payload:answer}); } else if (s.kind === 'answer') await this.pc.setRemoteDescription(s.payload); else if (s.kind === 'ice') await this.pc.addIceCandidate(s.payload); } if (['ended','declined','missed'].includes(result.status)) this.closeUi(result.status === 'declined' ? 'User declined' : result.status === 'missed' ? 'Call missed' : 'Call ended'); } catch {} },
    async endCall(reason = 'end') { const id = this.callId(); if (id) await this.api(`/calls/${id}/action`, 'POST', {action: reason === 'timeout' ? 'timeout' : 'end'}).catch(()=>{}); this.closeUi(reason === 'timeout' ? 'Call timed out' : 'Call ended'); },
    toggleMute() { this.muted = !this.muted; this.stream?.getAudioTracks().forEach(t => t.enabled = !this.muted); },
    toggleCamera() { this.cameraOff = !this.cameraOff; this.stream?.getVideoTracks().forEach(t => t.enabled = !this.cameraOff); },
    async switchCamera() { const track = this.stream?.getVideoTracks()[0]; if (!track || !track.getCapabilities?.().facingMode) return; const mode = track.getSettings().facingMode === 'user' ? 'environment' : 'user'; try { const s = await navigator.mediaDevices.getUserMedia({video:{facingMode:mode},audio:false}); const next = s.getVideoTracks()[0]; await this.pc?.getSenders().find(x=>x.track?.kind === 'video')?.replaceTrack(next); track.stop(); this.stream.removeTrack(track); this.stream.addTrack(next); this.$refs.localVideo.srcObject = this.stream; } catch {} },
    async api(url, method, body) { const callMatch = url.match(/^\/calls\/([^/]+)\/(?:action|signals|connected)/); if (callMatch && (!callMatch[1] || Number.isNaN(Number(callMatch[1])) || Number(callMatch[1]) <= 0)) return; const r = await fetch(url, {method, headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body:JSON.stringify(body)}); const data = await r.json().catch(()=>({})); if (!r.ok) throw new Error(data.message || 'Request failed.'); return data; },
    stopPolling() { if (this.poller) clearInterval(this.poller); this.poller = null; },
    cleanup() { this.stopPolling(); if (this.timeout) clearTimeout(this.timeout); this.timeout = null; this.stream?.getTracks().forEach(t=>t.stop()); this.pc?.close(); this.stream=null; this.pc=null; },
    closeUi(message) { this.statusLabel = message; this.cleanup(); setTimeout(() => { this.open=false; this.incoming=false; this.call=null; this.signalCursor=0; }, 1200); },
});

/*
|--------------------------------------------------------------------------
| Relationships Modal (Followers / Following)
|--------------------------------------------------------------------------
*/
Alpine.data('relationshipsModal', () => ({
    open: false,
    type: 'followers',
    username: null,
    users: [],
    query: '',
    loading: false,
    page: 1,
    hasMore: true,
    searchTimer: null,
    currentRequest: null,

    init() {
        this.$watch('query', () => {
            this.debouncedSearch();
        });
    },

    openModal(type, username) {
        this.type = type;
        this.username = username;
        this.users = [];
        this.query = '';
        this.page = 1;
        this.hasMore = true;
        this.loading = false;
        this.open = true;

        this.$nextTick(() => {
            this.$refs.searchInput?.focus();
            this.fetchUsers();
        });
    },

    close() {
        this.open = false;
        this.currentRequest?.abort();
        this.currentRequest = null;
    },

    get modalTitle() {
        return this.type === 'followers' ? 'Followers' : 'Following';
    },

    get endpoint() {
        return `/u/${this.username}/${this.type}`;
    },

    async fetchUsers() {
        if (this.loading || !this.hasMore) return;

        this.currentRequest?.abort();
        this.currentRequest = new AbortController();

        this.loading = true;

        try {
            const params = new URLSearchParams({ page: this.page });
            if (this.query.trim()) params.set('q', this.query.trim());

            const response = await fetch(`${this.endpoint}?${params}`, {
                headers: { Accept: 'application/json' },
                signal: this.currentRequest.signal,
            });

            if (!response.ok) throw new Error();

            const data = await response.json();

            if (this.page === 1) {
                this.users = data.data;
            } else {
                this.users = [...this.users, ...data.data];
            }

            this.hasMore = data.next_page_url !== null;
            this.page++;
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Failed to fetch users:', error);
            }
        } finally {
            this.loading = false;
            this.currentRequest = null;
        }
    },

    async loadMore() {
        if (!this.hasMore || this.loading) return;
        await this.fetchUsers();
    },

    debouncedSearch() {
        clearTimeout(this.searchTimer);

        this.searchTimer = setTimeout(() => {
            this.page = 1;
            this.hasMore = true;
            this.fetchUsers();
        }, 300);
    },

    async toggleFollow(user) {
        if (user.followLoading) return;

        user.followLoading = true;
        const previous = user.is_followed_by_current_user;

        // Optimistic UI
        user.is_followed_by_current_user = !user.is_followed_by_current_user;

        try {
            const response = await fetch(`/ajax/users/${user.username}/follow`, {
                method: user.is_followed_by_current_user ? 'POST' : 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!response.ok) throw new Error();

            const data = await response.json();
            user.is_followed_by_current_user = data.followed;
        } catch {
            user.is_followed_by_current_user = previous;
        } finally {
            user.followLoading = false;
        }
    },
}));

/*
|--------------------------------------------------------------------------
| Profile Tabs (Posts / Saved)
|--------------------------------------------------------------------------
*/
Alpine.data('profileTabs', () => ({
    activeTab: 'posts',
    savedPosts: [],
    savedPage: 1,
    savedHasMore: true,
    savedLoading: false,
    savedNextUrl: null,

    init() {
        this.$watch('activeTab', (tab) => {
            if (tab === 'saved' && this.savedPosts.length === 0) {
                this.fetchSaved();
            }
        });

        window.addEventListener('post-unsaved', (event) => {
            const postId = event.detail.postId;
            this.savedPosts = this.savedPosts.filter(p => p.id !== postId);

            this.$nextTick(() => {
                if (this.savedPosts.length === 0 && this.savedHasMore) {
                    this.savedPage = 1;
                    this.savedHasMore = true;
                    this.fetchSaved();
                }
            });
        });
    },

    switchTab(tab) {
        this.activeTab = tab;
    },

    async fetchSaved() {
        if (this.savedLoading || !this.savedHasMore) return;

        this.savedLoading = true;

        try {
            const params = new URLSearchParams({ page: this.savedPage });
            const response = await fetch(`/profile/saved?${params}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!response.ok) throw new Error();

            const data = await response.json();

            if (data.html) {
                const template = document.createElement('template');
                template.innerHTML = data.html;

                if (this.savedPage === 1) {
                    this.savedPosts = Array.from(template.content.children).map(el => ({
                        id: parseInt(el.querySelector('[data-post-id]')?.dataset.postId || el.querySelector('a[href*="/posts/"]')?.href?.split('/').pop() || 0),
                        html: el.outerHTML,
                    }));
                } else {
                    const newPosts = Array.from(template.content.children).map(el => ({
                        id: parseInt(el.querySelector('[data-post-id]')?.dataset.postId || el.querySelector('a[href*="/posts/"]')?.href?.split('/').pop() || 0),
                        html: el.outerHTML,
                    }));
                    this.savedPosts = [...this.savedPosts, ...newPosts];
                }

                // If loaded via AJAX, re-init Alpine for new posts
                if (this.savedPage > 1) {
                    this.$nextTick(() => {
                        const container = document.getElementById('saved-grid');
                        if (container) {
                            const lastIndex = container.children.length - newPosts.length;
                            for (let i = lastIndex; i < container.children.length; i++) {
                                Alpine.initTree(container.children[i]);
                            }
                        }
                    });
                }

                this.savedNextUrl = data.next_page_url;
                this.savedHasMore = data.has_more;
                this.savedPage++;
            }
        } catch (error) {
            console.error('Failed to fetch saved posts:', error);
        } finally {
            this.savedLoading = false;
        }
    },

    async loadMoreSaved() {
        if (!this.savedHasMore || this.savedLoading) return;
        await this.fetchSaved();
    },
}));

Alpine.start();

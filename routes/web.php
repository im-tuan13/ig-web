<?php

use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\FollowRequestController;
use App\Http\Controllers\HashtagController;
use App\Http\Controllers\HighlightController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostArchiveController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\PostSaveController;
use App\Http\Controllers\PostShareController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CollectionPostController;
use App\Http\Controllers\ReelsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\StoryViewerController;
use App\Http\Controllers\CallController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\PostTagController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\NoteController;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth'])->group(function () {

    // Feed
    Route::get('/dashboard', FeedController::class)->name('dashboard');

    // Stories
    Route::get('/stories/create', [StoryController::class, 'create'])->name('stories.create');
    Route::post('/stories', [StoryController::class, 'store'])->name('stories.store');
    Route::get('/stories/{story}', [StoryViewerController::class, 'show'])->name('stories.show');
    Route::post('/stories/{story}/reply', [StoryViewerController::class, 'reply'])->name('stories.reply');
    Route::delete('/stories/{story}', [StoryController::class, 'destroy'])->name('stories.destroy');

    // Story Highlights
    Route::get('/highlights/create', [HighlightController::class, 'create'])->name('highlights.create');
    Route::post('/highlights', [HighlightController::class, 'store'])->name('highlights.store');
    Route::get('/highlights/{highlight}', [HighlightController::class, 'show'])->name('highlights.show');
    Route::get('/highlights/{highlight}/edit', [HighlightController::class, 'edit'])->name('highlights.edit');
    Route::put('/highlights/{highlight}', [HighlightController::class, 'update'])->name('highlights.update');
    Route::delete('/highlights/{highlight}', [HighlightController::class, 'destroy'])->name('highlights.destroy');

    // Search
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/users', [SearchController::class, 'users'])->name('search.users');
    Route::get('/search/posts', [SearchController::class, 'posts'])->name('search.posts');
    Route::get('/search/hashtags', [SearchController::class, 'hashtags'])->name('search.hashtags');

    // Explore
    Route::get('/explore', ExploreController::class)->name('explore.index');

    // Hashtags
    Route::get('/tags/{hashtag}', HashtagController::class)->name('hashtags.show');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);

    // Posts
    Route::get('/posts', function () {
        return redirect()->route('dashboard');
    })->name('posts.index');

    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::post('/posts/{post}/comments', [PostCommentController::class, 'store'])->name('posts.comments.store');
    Route::delete('/posts/{post}/comments/{comment}', [PostCommentController::class, 'destroy'])->name('posts.comments.destroy');
    Route::post('/posts/{post}/likes', [PostLikeController::class, 'store'])->name('posts.likes.store');
    Route::delete('/posts/{post}/likes', [PostLikeController::class, 'destroy'])->name('posts.likes.destroy');
    Route::post('/posts/{post}/saves', [PostSaveController::class, 'store'])->name('posts.saves.store');
    Route::delete('/posts/{post}/saves', [PostSaveController::class, 'destroy'])->name('posts.saves.destroy');
    Route::get('/share/conversations', [PostShareController::class, 'conversations'])->name('share.conversations');
    Route::post('/posts/{post}/share', [PostShareController::class, 'store'])->name('posts.share');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [NotificationController::class, 'unreadCount'])->name('notifications.count');
    Route::post('/notifications/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Reels
    Route::get('/reels', [ReelsController::class, 'index'])->name('reels.index');
    Route::get('/reels/create', [ReelsController::class, 'create'])->name('reels.create');
    Route::post('/reels', [ReelsController::class, 'store'])->name('reels.store');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/group/create', [MessageController::class, 'createGroupForm'])->name('messages.group.create');
    Route::post('/messages/group', [MessageController::class, 'storeGroup'])->name('messages.group.store');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{conversation}/poll', [MessageController::class, 'poll'])->name('messages.poll');
    Route::post('/messages/{conversation}/read', [MessageController::class, 'read'])->name('messages.read');
    Route::get('/messages/unread/count', [MessageController::class, 'unreadCount'])->name('messages.unread.count');
    Route::post('/messages/start/{user:username}', [MessageController::class, 'start'])->name('messages.start');

    // WebRTC calls use authenticated database polling for signaling.
    Route::get('/calls', [CallController::class, 'history'])->name('calls.index');
    Route::post('/calls', [CallController::class, 'start'])->name('calls.start');
    Route::get('/calls/incoming', [CallController::class, 'incoming'])->name('calls.incoming');
    Route::post('/calls/timeout', [CallController::class, 'timeout'])->name('calls.timeout');
    Route::get('/calls/{call}/signals', [CallController::class, 'signals'])->name('calls.signals');
    Route::post('/calls/{call}/signals', [CallController::class, 'signal'])->name('calls.signal');
    Route::post('/calls/{call}/action', [CallController::class, 'action'])->name('calls.action');
    Route::post('/calls/{call}/connected', [CallController::class, 'connected'])->name('calls.connected');

    // Profile Saved
    Route::get('/profile/saved', [ProfileController::class, 'saved'])->name('profile.saved');

    // Archive
    Route::get('/archive', [PostArchiveController::class, 'index'])->name('posts.archive.index');
    Route::post('/posts/{post}/archive', [PostArchiveController::class, 'store'])->name('posts.archive.store');
    Route::delete('/posts/{post}/archive', [PostArchiveController::class, 'destroy'])->name('posts.archive.destroy');

    // Saved Collections
    Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
    Route::post('/collections', [CollectionController::class, 'store'])->name('collections.store');
    Route::get('/collections/{collection}', [CollectionController::class, 'show'])->name('collections.show');
    Route::get('/collections/{collection}/add', [CollectionController::class, 'add'])->name('collections.add');
    Route::delete('/collections/{collection}', [CollectionController::class, 'destroy'])->name('collections.destroy');
    Route::post('/collections/{collection}/posts/{post}', [CollectionPostController::class, 'store'])->name('collections.posts.store');
    Route::delete('/collections/{collection}/posts/{post}', [CollectionPostController::class, 'destroy'])->name('collections.posts.destroy');
    Route::post('/users/{user}/block', [ModerationController::class, 'block'])->name('users.block');
    Route::delete('/users/{user}/block', [ModerationController::class, 'unblock'])->name('users.unblock');
    Route::post('/users/{user}/mute', [ModerationController::class, 'mute'])->name('users.mute');
    Route::delete('/users/{user}/mute', [ModerationController::class, 'unmute'])->name('users.unmute');
    Route::post('/reports', [ModerationController::class, 'report'])->name('reports.store');
    Route::post('/posts/{post}/tags', [PostTagController::class, 'store'])->name('posts.tags.store');
    Route::delete('/posts/{post}/tags/{tag}', [PostTagController::class, 'destroy'])->name('posts.tags.destroy');
    Route::post('/presence/online', [PresenceController::class, 'online'])->name('presence.online');
    Route::post('/presence/offline', [PresenceController::class, 'offline'])->name('presence.offline');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::delete('/notes', [NoteController::class, 'destroy'])->name('notes.destroy');

    // Social Graph
    Route::get('/follow-requests', [FollowRequestController::class, 'index'])->name('follow-requests.index');
    Route::post('/follow-requests/{follow}/accept', [FollowRequestController::class, 'accept'])->name('follow-requests.accept');
    Route::delete('/follow-requests/{follow}', [FollowRequestController::class, 'reject'])->name('follow-requests.reject');
    Route::post('/users/{user:username}/follow', [FollowController::class, 'store'])->name('users.follow');
    Route::delete('/users/{user:username}/follow', [FollowController::class, 'destroy'])->name('users.unfollow');
    Route::get('/u/{user:username}', [ProfileController::class, 'show'])->name('users.show');
    Route::get('/u/{user:username}/followers', [ProfileController::class, 'followers'])->name('users.followers');
    Route::get('/u/{user:username}/following', [ProfileController::class, 'following'])->name('users.following');
    Route::post('/ajax/users/{user:username}/follow', [FollowController::class, 'storeAjax'])->name('ajax.users.follow');
    Route::delete('/ajax/users/{user:username}/follow', [FollowController::class, 'destroyAjax'])->name('ajax.users.unfollow');
});

require __DIR__.'/auth.php';

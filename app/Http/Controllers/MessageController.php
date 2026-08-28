<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $conversations = $user->conversations()
            ->with(['participants:id,name,username,avatar'])
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->take(1)
            )
            ->paginate(20);

        $conversations->load([
            'messages' => fn ($q) => $q->with('user:id,name,username,avatar')->latest()->take(1),
        ]);

        $unreadCounts = [];

        foreach ($conversations as $conv) {
            $unreadCounts[$conv->id] = $conv->unreadCountFor($user);
        }

        return view('messages.index', compact('conversations', 'unreadCounts'));
    }

    public function show(Request $request, Conversation $conversation): View
    {
        /** @var User $user */
        $user = $request->user();

        if (! $conversation->participants()->whereKey($user->id)->exists()) {
            abort(403);
        }

        $conversation->load([
            'participants:id,name,username,avatar',
            'messages' => fn ($q) => $q->with(['user:id,name,username,avatar', 'post:id,user_id,image,caption', 'story'])
                ->oldest('created_at')->oldest('id'),
        ]);

        $conversation->markAsRead($user);

        $otherUser = $conversation->otherParticipant($user);

        return view('messages.show', compact('conversation', 'otherUser'));
    }

    public function store(Request $request, Conversation $conversation): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $conversation->participants()->whereKey($user->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'nullable|string|max:5000',
            'type' => 'nullable|in:text,emoji,sticker',
            'sticker_key' => 'nullable|string|in:heart,laugh,party,fire,cat',
        ]);
        $type = $validated['type'] ?? 'text';
        if ($type === 'sticker' && empty($validated['sticker_key'])) abort(422, 'Please choose a sticker.');
        if ($type !== 'sticker' && blank($validated['message'])) abort(422, 'Message cannot be empty.');

        $message = $conversation->messages()->create([
            'user_id' => $user->id,
            'message' => $validated['message'],
            'type' => $type,
            'sticker_key' => $validated['sticker_key'] ?? null,
        ]);

        $message->load('user:id,name,username,avatar');

        if ($request->wantsJson()) {
            return response()->json($message, 201);
        }

        return back()->with('success', 'Message sent.');
    }

    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $conversation->participants()->whereKey($user->id)->exists()) {
            abort(403);
        }

        $after = $request->filled('after') ? Carbon::parse($request->input('after')) : null;

        $messages = $conversation->messages()
            ->with(['user:id,name,username,avatar', 'post:id,user_id,image,caption', 'story'])
            ->when($after, fn ($q) => $q->where('created_at', '>', $after))
            ->oldest('created_at')->oldest('id')
            ->get();

        return response()->json($messages);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $total = 0;

        foreach ($user->conversations as $conversation) {
            $total += $conversation->unreadCountFor($user);
        }

        return response()->json(['count' => $total]);
    }

    public function read(Request $request, Conversation $conversation): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $conversation->participants()->whereKey($user->id)->exists()) {
            abort(403);
        }

        $conversation->markAsRead($user);

        return response()->json(['success' => true]);
    }

    /**
     * API: Start a conversation with a user by username.
     */
    public function start(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'username' => 'required|string|exists:users,username',
        ]);

        $target = User::where('username', $validated['username'])->firstOrFail();

        if ($target->is($user)) {
            return back()->with('error', 'You cannot message yourself.');
        }

        $conversation = Conversation::findOrCreateBetween($user, $target);

        return redirect()->route('messages.show', $conversation);
    }

    /**
     * Show the form to create a new group conversation.
     */
    public function createGroupForm(Request $request): View
    {
        $followingUsers = $request->user()->following()
            ->wherePivot('status', 'accepted')
            ->get(['users.id', 'users.name', 'users.username', 'users.avatar']);

        return view('messages.create-group', compact('followingUsers'));
    }

    /**
     * Store a new group conversation.
     */
    public function storeGroup(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:60'],
            'participants' => ['required', 'array', 'min:2'],
            'participants.*' => ['integer', 'exists:users,id'],
        ]);

        $participantIds = array_values(array_unique(array_map('intval', $validated['participants'])));

        $conversation = Conversation::createGroup($user, $participantIds, $validated['name'] ?? null);

        return redirect()->route('messages.show', $conversation);
    }
}

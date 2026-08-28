<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CallController extends Controller
{
    private function canInteract(User $a, User $b): bool
    {
        if ($a->is($b)) {
            return false;
        }

        return \App\Models\Conversation::query()
            ->whereHas('participants', fn ($query) => $query->whereKey($a->id))
            ->whereHas('participants', fn ($query) => $query->whereKey($b->id))
            ->exists();
    }

    private function authorizeCall(Request $request, Call $call): User
    {
        $user = $request->user();
        abort_unless($user && ($call->caller_id === $user->id || $call->receiver_id === $user->id), 403);

        return $user;
    }

    /**
     * Resolve a call manually so malformed route values (for example
     * "undefined" from a client) receive a validation response instead of an
     * implicit model-binding "No query results" exception.
     */
    private function resolveCall(mixed $callId): Call
    {
        if (! is_string($callId) && ! is_int($callId)) {
            throw ValidationException::withMessages(['call_id' => 'The call ID must be a positive integer.']);
        }

        $id = (string) $callId;
        if (! preg_match('/^[1-9][0-9]*$/', $id)) {
            throw ValidationException::withMessages(['call_id' => 'The call ID must be a positive integer.']);
        }

        $call = Call::find($id);
        abort_unless($call, 404, 'Call not found.');

        return $call;
    }

    private function expireUnansweredCalls(): void
    {
        Call::where('status', 'calling')
            ->where('created_at', '<', now()->subSeconds(35))
            ->update(['status' => 'missed', 'ended_at' => now()]);
    }

    public function history(Request $request): View
    {
        $calls = Call::with(['caller:id,username,name,avatar', 'receiver:id,username,name,avatar'])
            ->where(fn ($q) => $q->where('caller_id', $request->user()->id)->orWhere('receiver_id', $request->user()->id))
            ->latest()->paginate(25);

        return view('calls.index', compact('calls'));
    }

    public function start(Request $request): JsonResponse
    {
        $data = $request->validate(['receiver_id' => ['required', 'integer', 'exists:users,id'], 'type' => ['nullable', 'in:video,audio']]);
        $user = $request->user();
        $receiver = User::findOrFail($data['receiver_id']);
        abort_unless($this->canInteract($user, $receiver), 403, 'You cannot call this user.');

        $busy = Call::whereIn('status', ['calling', 'connecting', 'connected'])
            ->where(fn ($q) => $q->whereIn('caller_id', [$user->id, $receiver->id])->orWhereIn('receiver_id', [$user->id, $receiver->id]))->exists();
        if ($busy) {
            return response()->json(['message' => 'One of the users is already in another call.'], 422);
        }

        $call = Call::create(['caller_id' => $user->id, 'receiver_id' => $receiver->id, 'type' => $data['type'] ?? 'video', 'status' => 'calling']);
        $receiver->notifications()->create(['actor_id' => $user->id, 'type' => 'incoming_call', 'data' => ['call_id' => $call->id, 'type' => $call->type]]);

        return response()->json($this->callPayload($call->fresh(['caller:id,username,name,avatar', 'receiver:id,username,name,avatar'])));
    }

    public function incoming(Request $request): JsonResponse
    {
        $this->expireUnansweredCalls();

        $call = Call::with('caller:id,username,name,avatar')->where('receiver_id', $request->user()->id)->where('status', 'calling')->latest()->first();

        return response()->json([
            'has_call' => (bool) $call,
            'call' => $call ? $this->callPayload($call) : null,
        ]);
    }

    public function signal(Request $request, mixed $call): JsonResponse
    {
        $call = $this->resolveCall($call);
        $user = $this->authorizeCall($request, $call);
        $data = $request->validate(['kind' => ['required', 'in:offer,answer,ice,renegotiate'], 'payload' => ['required', 'array']]);
        $signal = $call->signals()->create(['sender_id' => $user->id, 'kind' => $data['kind'], 'payload' => $data['payload']]);

        return response()->json(['id' => $signal->id]);
    }

    public function signals(Request $request, mixed $call): JsonResponse
    {
        $call = $this->resolveCall($call);
        $this->authorizeCall($request, $call);
        $signals = $call->signals()->where('sender_id', '!=', $request->user()->id)->when($request->integer('after'), fn ($q, $id) => $q->where('id', '>', $id))->oldest('id')->get(['id', 'kind', 'payload', 'sender_id']);

        return response()->json(['status' => $call->status, 'signals' => $signals]);
    }

    public function action(Request $request, mixed $call): JsonResponse
    {
        $call = $this->resolveCall($call);
        $user = $this->authorizeCall($request, $call);
        $data = $request->validate(['action' => ['required', 'in:accept,decline,end,timeout']]);
        if ($data['action'] === 'accept' && $call->receiver_id === $user->id && $call->status === 'calling') {
            $call->update(['status' => 'connecting', 'started_at' => now()]);
        } elseif ($data['action'] === 'decline' && $call->receiver_id === $user->id && in_array($call->status, ['calling', 'connecting'], true)) {
            $call->update(['status' => 'declined', 'ended_at' => now()]);
        } elseif ($data['action'] === 'timeout' && $call->status === 'calling') {
            $call->update(['status' => 'missed', 'ended_at' => now()]);
        } elseif ($data['action'] === 'end' && in_array($call->status, ['calling', 'connecting', 'connected'], true)) {
            $ended = now();
            $call->update(['status' => 'ended', 'ended_at' => $ended, 'duration' => $call->started_at ? $call->started_at->diffInSeconds($ended) : 0]);
        }
        if (in_array($call->fresh()->status, ['declined', 'missed', 'ended'], true)) {
            $otherId = $call->caller_id === $user->id ? $call->receiver_id : $call->caller_id;
            User::find($otherId)?->notifications()->create(['actor_id' => $user->id, 'type' => $call->status === 'missed' ? 'missed_call' : 'call_ended', 'data' => ['call_id' => $call->id]]);
        }

        return response()->json($this->callPayload($call->fresh()));
    }

    public function connected(Request $request, mixed $call): JsonResponse
    {
        $call = $this->resolveCall($call);
        $this->authorizeCall($request, $call);
        if ($call->status === 'connecting') {
            $call->update(['status' => 'connected']);
        }

        return response()->json(['status' => $call->fresh()->status]);
    }

    public function timeout(): JsonResponse
    {
        $this->expireUnansweredCalls();

        return response()->json(['ok' => true]);
    }

    private function callPayload(Call $call): array
    {
        $call->loadMissing(['caller:id,username,name,avatar', 'receiver:id,username,name,avatar']);

        return ['id' => $call->id, 'status' => $call->status, 'type' => $call->type, 'caller' => $call->caller, 'receiver' => $call->receiver, 'started_at' => $call->started_at?->toIso8601String()];
    }
}

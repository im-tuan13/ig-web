<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'name',
        'is_group',
    ];

    protected $casts = [
        'is_group' => 'boolean',
    ];

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_user')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the other participant in a one-to-one conversation.
     */
    public function otherParticipant(User $user): ?User
    {
        return $this->participants()
            ->whereKeyNot($user->id)
            ->first();
    }

    /**
     * Mark messages as read for a given user.
     */
    public function markAsRead(User $user): void
    {
        $this->participants()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);
    }

    /**
     * Get unread message count for a user.
     */
    public function unreadCountFor(User $user): int
    {
        $pivot = $this->participants()
            ->whereKey($user->id)
            ->first()
            ?->pivot;

        $lastReadAt = $pivot?->last_read_at;

        return $this->messages()
            ->where('user_id', '!=', $user->id)
            ->when($lastReadAt, fn ($q) => $q->where('created_at', '>', $lastReadAt))
            ->count();
    }

    /**
     * Find or create a one-to-one conversation between two users.
     */
    public static function findOrCreateBetween(User $user1, User $user2): self
    {
        $existing = self::whereHas('participants', fn ($q) => $q->whereKey($user1->id))
            ->whereHas('participants', fn ($q) => $q->whereKey($user2->id))
            ->whereDoesntHave('participants', fn ($q) => $q->whereKeyNot($user1->id)->whereKeyNot($user2->id))
            ->first();

        if ($existing) {
            return $existing;
        }

        $conversation = self::create();
        $conversation->participants()->attach([$user1->id, $user2->id]);

        return $conversation;
    }

    /**
     * Create a new group conversation with the given creator + participant IDs.
     */
    public static function createGroup(User $creator, array $participantIds, ?string $name = null): self
    {
        $conversation = self::create([
            'name' => $name,
            'is_group' => true,
        ]);

        $allParticipantIds = array_unique([...$participantIds, $creator->id]);

        $conversation->participants()->attach($allParticipantIds);

        return $conversation;
    }

    /**
     * Get the display name of this conversation from the given viewer's perspective.
     */
    public function displayNameFor(User $viewer): string
    {
        if ($this->is_group) {
            return $this->name ?: $this->participants()
                ->whereKeyNot($viewer->id)
                ->pluck('username')
                ->join(', ');
        }

        return $this->otherParticipant($viewer)?->username ?? 'Unknown';
    }
}

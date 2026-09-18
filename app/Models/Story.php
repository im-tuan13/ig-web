<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Story extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'image', 'expires_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function highlight(): BelongsTo
    {
        return $this->belongsTo(Highlight::class);
    }

    public function viewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'story_views')->withPivot('viewed_at')->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Limit the query to stories the given viewer is allowed to see,
     * mirroring the private-account rules enforced elsewhere (feed, explore, profile).
     */
    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        return $query->where(function (Builder $q) use ($viewer): void {
            $q->where('user_id', $viewer->id)
                ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('is_private', false))
                ->orWhereIn('user_id', $viewer->following()
                    ->wherePivot('status', 'accepted')
                    ->select('users.id'));
        });
    }

    public function getImageUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image);
    }

    protected static function booted(): void
    {
        static::deleting(function (Story $story): void {
            if (Storage::disk('public')->exists($story->image)) {
                Storage::disk('public')->delete($story->image);
            }
        });
    }
}

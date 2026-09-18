<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image',
        'caption',
        'archived_at',
        'is_video',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'is_video' => 'boolean',
    ];

    /**
     * Return a public URL for uploaded files or external images.
     */
    public function getImageUrlAttribute(): string
    {
        if (
            str_starts_with($this->image, 'http://') ||
            str_starts_with($this->image, 'https://')
        ) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'uploads/')) {
            return asset($this->image);
        }

        if (! empty($this->image)) {
            return Storage::disk('public')->url($this->image);
        }

        $firstImage = $this->images()->first();

        if ($firstImage) {
            return Storage::disk('public')->url($firstImage->path);
        }

        return '';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)->orderBy('position')->orderBy('id');
    }

    protected static function booted(): void
    {
        static::deleting(function (Post $post): void {
            foreach ($post->images as $image) {
                if (! empty($image->path) && Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                }
            }

            if (! empty($post->image) && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }
        });
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_likes')->withTimestamps();
    }

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_saves')->withTimestamps();
    }

    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class, 'hashtag_post')->withTimestamps();
    }

    public function tags(): HasMany
    {
        return $this->hasMany(PostTag::class);
    }

    /**
     * Limit the query to posts the given viewer is allowed to see,
     * mirroring the private-account rules already enforced for stories.
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

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    /**
     * Parse hashtags from a caption string and return a collection of lowercase unique tag names.
     *
     * @return array<int, string>
     */
    public static function parseHashtags(?string $caption): array
    {
        if (empty($caption)) {
            return [];
        }

        preg_match_all('/#([\w\x{80}-\x{FF}]+)/u', $caption, $matches);

        $tags = array_map(function (string $tag): string {
            return Str::lower(trim($tag));
        }, $matches[1] ?? []);

        return array_unique(array_filter($tags));
    }

    /**
     * Parse @mentions from a caption or comment string and return a collection
     * of lowercase unique usernames (without the leading @).
     *
     * @return array<int, string>
     */
    public static function parseMentions(?string $text): array
    {
        if (empty($text)) {
            return [];
        }

        preg_match_all('/@([A-Za-z0-9_-]+)/', $text, $matches);

        $usernames = array_map(function (string $username): string {
            return Str::lower(trim($username));
        }, $matches[1] ?? []);

        return array_unique(array_filter($usernames));
    }
}

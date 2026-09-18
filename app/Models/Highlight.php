<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Highlight extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'cover_image'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class)->orderBy('created_at');
    }

    public function getCoverUrlAttribute(): ?string
    {
        $image = $this->cover_image ?: $this->stories->first()?->image;

        return $image ? Storage::disk('public')->url($image) : null;
    }
}

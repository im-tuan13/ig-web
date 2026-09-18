<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Call extends Model
{
    protected $fillable = ['caller_id', 'receiver_id', 'type', 'status', 'started_at', 'ended_at', 'duration'];
    protected function casts(): array { return ['started_at' => 'datetime', 'ended_at' => 'datetime']; }
    public function caller(): BelongsTo { return $this->belongsTo(User::class, 'caller_id'); }
    public function receiver(): BelongsTo { return $this->belongsTo(User::class, 'receiver_id'); }
    public function signals(): HasMany { return $this->hasMany(CallSignal::class); }
}

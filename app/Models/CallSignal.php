<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallSignal extends Model
{
    protected $fillable = ['call_id', 'sender_id', 'kind', 'payload'];
    protected function casts(): array { return ['payload' => 'array']; }
    public function call(): BelongsTo { return $this->belongsTo(Call::class); }
    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_id'); }
}

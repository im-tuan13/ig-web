<?php
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
Broadcast::channel('conversation.{conversation}', function (User $user, Conversation $conversation): bool { return $conversation->participants()->whereKey($user->id)->exists(); });
Broadcast::channel('online', fn (User $user) => ['id'=>$user->id,'name'=>$user->username]);

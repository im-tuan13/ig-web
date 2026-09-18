<?php

namespace App\Policies;

use App\Models\Follow;
use App\Models\User;

class FollowPolicy
{
    public function respond(User $user, Follow $follow): bool
    {
        return $user->id === $follow->following_id;
    }
}

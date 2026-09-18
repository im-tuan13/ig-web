<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function moderate(User $user, User $target): bool { return $user->id !== $target->id; }
    public function follow(User $actor, User $user): bool
    {
        return $actor->isNot($user);
    }

    public function unfollow(User $actor, User $user): bool
    {
        return $actor->isNot($user);
    }
}

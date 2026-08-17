<?php

namespace App\Policies;

use App\Models\Story;
use App\Models\User;

class StoryPolicy
{
    public function view(User $user, Story $story): bool
    {
        return $user->canSee($story->user);
    }
}

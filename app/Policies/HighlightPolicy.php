<?php

namespace App\Policies;

use App\Models\Highlight;
use App\Models\User;

class HighlightPolicy
{
    public function view(User $user, Highlight $highlight): bool
    {
        return true;
    }

    public function update(User $user, Highlight $highlight): bool
    {
        return $highlight->user_id === $user->id;
    }

    public function delete(User $user, Highlight $highlight): bool
    {
        return $highlight->user_id === $user->id;
    }
}

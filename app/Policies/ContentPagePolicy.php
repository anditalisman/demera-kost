<?php

namespace App\Policies;

use App\Domain\Platform\Models\ContentPage;
use App\Models\User;

class ContentPagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('content.view') || $user->can('content.manage');
    }

    public function view(User $user, ContentPage $contentPage): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('content.manage');
    }

    public function update(User $user, ContentPage $contentPage): bool
    {
        return $user->can('content.manage');
    }

    public function delete(User $user, ContentPage $contentPage): bool
    {
        return $user->can('content.manage');
    }
}

<?php

namespace App\Policies;

use App\Domain\Platform\Models\Gallery;
use App\Models\User;

class GalleryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('content.view') || $user->can('content.manage');
    }

    public function view(User $user, Gallery $gallery): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('content.manage');
    }

    public function update(User $user, Gallery $gallery): bool
    {
        return $user->can('content.manage');
    }

    public function delete(User $user, Gallery $gallery): bool
    {
        return $user->can('content.manage');
    }
}

<?php

namespace App\Policies;

use App\Domain\Platform\Models\Faq;
use App\Models\User;

class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('content.view') || $user->can('content.manage');
    }

    public function view(User $user, Faq $faq): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('content.manage');
    }

    public function update(User $user, Faq $faq): bool
    {
        return $user->can('content.manage');
    }

    public function delete(User $user, Faq $faq): bool
    {
        return $user->can('content.manage');
    }
}

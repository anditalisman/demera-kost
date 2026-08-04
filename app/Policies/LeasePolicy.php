<?php

namespace App\Policies;

use App\Domain\Living\Models\Lease;
use App\Models\User;

class LeasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leases.view') || $user->can('leases.manage');
    }

    public function view(User $user, Lease $lease): bool
    {
        return $lease->tenant->user_id === $user->id || $this->viewAny($user);
    }

    public function manage(User $user): bool
    {
        return $user->can('leases.manage');
    }
}

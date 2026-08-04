<?php

namespace App\Policies;

use App\Domain\Living\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tenants.view') || $user->can('tenants.manage');
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $this->viewAny($user);
    }

    public function manage(User $user): bool
    {
        return $user->can('tenants.manage');
    }
}

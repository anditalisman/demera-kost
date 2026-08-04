<?php

namespace App\Policies;

use App\Domain\Living\Models\MaintenanceRequest;
use App\Models\User;

class MaintenanceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->tenant()->exists() || $user->can('maintenance.view') || $user->can('maintenance.manage');
    }

    public function view(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        return $maintenanceRequest->tenant?->user_id === $user->id
            || $user->can('maintenance.view')
            || $user->can('maintenance.manage');
    }

    public function create(User $user): bool
    {
        return $user->tenant()->exists();
    }

    public function manage(User $user): bool
    {
        return $user->can('maintenance.manage');
    }
}

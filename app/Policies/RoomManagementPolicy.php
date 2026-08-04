<?php

namespace App\Policies;

use App\Models\User;

/**
 * Shared policy for the room-catalog resources (Property, Building, Floor,
 * RoomType, Room, Facility) — all gated by the same "rooms.*" permissions,
 * so one class is bound to every one of those models in AppServiceProvider.
 */
class RoomManagementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('rooms.view') || $user->can('rooms.manage');
    }

    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('rooms.manage');
    }

    public function update(User $user): bool
    {
        return $user->can('rooms.manage');
    }

    public function delete(User $user): bool
    {
        return $user->can('rooms.manage');
    }
}

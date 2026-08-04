<?php

namespace App\Policies;

use App\Domain\Platform\Models\ApplicationSetting;
use App\Models\User;

class ApplicationSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('settings.view') || $user->can('settings.manage');
    }

    public function view(User $user, ApplicationSetting $setting): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, ApplicationSetting $setting): bool
    {
        return $user->can('settings.manage');
    }
}

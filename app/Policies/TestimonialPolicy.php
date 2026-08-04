<?php

namespace App\Policies;

use App\Domain\Platform\Models\Testimonial;
use App\Models\User;

class TestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('content.view') || $user->can('content.manage');
    }

    public function view(User $user, Testimonial $testimonial): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('content.manage');
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return $user->can('content.manage');
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        return $user->can('content.manage');
    }
}

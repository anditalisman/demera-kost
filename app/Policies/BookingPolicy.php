<?php

namespace App\Policies;

use App\Domain\Living\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('bookings.view') || $user->can('bookings.manage');
    }

    public function view(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id || $this->viewAny($user);
    }

    public function manage(User $user): bool
    {
        return $user->can('bookings.manage');
    }
}

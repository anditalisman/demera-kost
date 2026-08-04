<?php

namespace App\Policies;

use App\Domain\Living\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('payments.view') || $user->can('payments.manage');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->owns($user, $payment) || $this->viewAny($user);
    }

    public function verify(User $user): bool
    {
        return $user->can('payments.verify') || $user->can('payments.manage');
    }

    private function owns(User $user, Payment $payment): bool
    {
        $invoice = $payment->invoice;

        return $invoice?->booking?->user_id === $user->id || $invoice?->tenant?->user_id === $user->id;
    }
}

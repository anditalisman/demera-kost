<?php

namespace App\Policies;

use App\Domain\Living\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('invoices.view') || $user->can('invoices.manage');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->owns($user, $invoice) || $this->viewAny($user);
    }

    public function pay(User $user, Invoice $invoice): bool
    {
        return $this->owns($user, $invoice);
    }

    private function owns(User $user, Invoice $invoice): bool
    {
        return $invoice->booking?->user_id === $user->id || $invoice->tenant?->user_id === $user->id;
    }
}

<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\Payment;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'payment_code' => 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'invoice_id' => Invoice::factory(),
            'method' => PaymentMethod::ManualTransfer,
            'amount' => 1500000,
            'status' => PaymentStatus::Paid,
            'gateway_provider' => 'manual',
            'paid_at' => now(),
        ];
    }
}

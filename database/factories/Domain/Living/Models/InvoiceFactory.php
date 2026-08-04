<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\Lease;
use App\Domain\Living\Models\Tenant;
use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $periodStart = now()->startOfMonth();
        $subtotal = 1500000;

        return [
            'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'lease_id' => Lease::factory(),
            'tenant_id' => Tenant::factory(),
            'invoice_type' => 'rent',
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodStart->copy()->endOfMonth()->toDateString(),
            'due_date' => $periodStart->copy()->addDays(5)->toDateString(),
            'subtotal_amount' => $subtotal,
            'discount_amount' => 0,
            'late_fee_amount' => 0,
            'total_amount' => $subtotal,
            'paid_amount' => 0,
            'status' => InvoiceStatus::Unpaid,
            'issued_at' => $periodStart,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => InvoiceStatus::Paid,
            'paid_amount' => $attrs['total_amount'] ?? 1500000,
        ]);
    }
}

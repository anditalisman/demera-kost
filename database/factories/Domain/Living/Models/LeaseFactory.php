<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Lease;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\Tenant;
use App\Enums\LeaseStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lease>
 */
class LeaseFactory extends Factory
{
    protected $model = Lease::class;

    public function definition(): array
    {
        $start = now()->subMonths(2)->startOfMonth();

        return [
            'lease_number' => 'LC-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'tenant_id' => Tenant::factory(),
            'room_id' => Room::factory(),
            'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->addMonths(6)->toDateString(),
            'duration_months' => 6,
            'monthly_price' => 1500000,
            'deposit_amount' => 1000000,
            'billing_cycle_day' => $start->day,
            'status' => LeaseStatus::Active,
            'terms' => 'Sewa dibayar di muka setiap tanggal jatuh tempo. Deposit dikembalikan sesuai kebijakan pembatalan.',
            'signed_at' => $start,
            'approved_at' => $start,
        ];
    }
}

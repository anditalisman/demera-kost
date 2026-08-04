<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Deposit;
use App\Domain\Living\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deposit>
 */
class DepositFactory extends Factory
{
    protected $model = Deposit::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'lease_id' => null,
            'amount' => fake()->randomElement([500000, 1000000]),
            'status' => 'held',
            'held_at' => now()->toDateString(),
            'returned_amount' => 0,
            'returned_at' => null,
            'deduction_notes' => null,
        ];
    }
}

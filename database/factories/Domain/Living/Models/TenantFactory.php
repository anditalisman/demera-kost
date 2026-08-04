<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'room_id' => null,
            'booking_id' => null,
            'status' => 'prospective',
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => '+62812'.fake()->numerify('########'),
            'emergency_contact_relationship' => fake()->randomElement(['Orang Tua', 'Saudara', 'Pasangan']),
            'joined_at' => null,
            'moved_out_at' => null,
            'notes' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'active', 'joined_at' => now()->subMonths(2)]);
    }
}

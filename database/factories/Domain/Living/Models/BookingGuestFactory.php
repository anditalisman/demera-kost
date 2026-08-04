<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Booking;
use App\Domain\Living\Models\BookingGuest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingGuest>
 */
class BookingGuestFactory extends Factory
{
    protected $model = BookingGuest::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'full_name' => fake()->name(),
            'identity_number' => fake()->numerify('################'),
            'phone' => '+62812'.fake()->numerify('########'),
            'email' => fake()->safeEmail(),
            'relationship' => 'self',
            'is_primary' => true,
        ];
    }
}

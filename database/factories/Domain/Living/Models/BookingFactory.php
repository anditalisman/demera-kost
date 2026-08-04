<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Booking;
use App\Domain\Living\Models\Room;
use App\Enums\BookingStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $monthlyPrice = fake()->randomElement([1200000, 1500000, 1800000]);
        $deposit = 1000000;

        return [
            'booking_code' => 'BK-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'user_id' => User::factory(),
            'room_id' => Room::factory(),
            'status' => BookingStatus::Confirmed,
            'start_date' => now()->toDateString(),
            'duration_months' => fake()->randomElement([3, 6, 12]),
            'monthly_price' => $monthlyPrice,
            'deposit_amount' => $deposit,
            'admin_fee' => 25000,
            'discount_amount' => 0,
            'total_amount' => $monthlyPrice + $deposit + 25000,
            'payment_due_at' => now()->addDay(),
            'confirmed_at' => now(),
            'notes' => null,
        ];
    }
}

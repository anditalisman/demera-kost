<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Building;
use App\Domain\Living\Models\Floor;
use App\Domain\Living\Models\Property;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\RoomType;
use App\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        $roomNumber = fake()->unique()->numerify('##').fake()->randomLetter();

        return [
            'property_id' => Property::factory(),
            'building_id' => Building::factory(),
            'floor_id' => Floor::factory(),
            'room_type_id' => RoomType::factory(),
            'room_number' => $roomNumber,
            'slug' => Str::slug($roomNumber).'-'.fake()->unique()->numberBetween(1000, 9999),
            'name' => null,
            'status' => RoomStatus::Available,
            'size_sqm' => fake()->randomElement([9, 12, 16, 20]),
            'capacity' => fake()->numberBetween(1, 2),
            'monthly_price' => fake()->randomElement([1200000, 1500000, 1800000, 2200000]),
            'deposit_amount' => fake()->randomElement([500000, 1000000]),
            'additional_fees' => [
                ['label' => 'Listrik', 'amount' => 150000],
                ['label' => 'Kebersihan', 'amount' => 50000],
            ],
            'description' => 'Kamar nyaman dengan pencahayaan alami dan sirkulasi udara baik, siap dihuni.',
            'available_from' => now()->toDateString(),
            'is_active' => true,
        ];
    }

    public function status(RoomStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}

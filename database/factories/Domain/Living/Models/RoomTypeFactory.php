<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Property;
use App\Domain\Living\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RoomType>
 */
class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['Standard', 'Deluxe', 'VIP', 'Superior']);

        return [
            'property_id' => Property::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => "Tipe kamar {$name} dengan kenyamanan sesuai standar Demera Living.",
            'base_price' => fake()->randomElement([1200000, 1500000, 1800000, 2200000, 2800000]),
            'base_deposit' => fake()->randomElement([500000, 1000000]),
            'size_sqm' => fake()->randomElement([9, 12, 16, 20]),
            'default_capacity' => fake()->numberBetween(1, 2),
        ];
    }
}

<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Building;
use App\Domain\Living\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Building>
 */
class BuildingFactory extends Factory
{
    protected $model = Building::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'name' => 'Gedung '.fake()->randomElement(['A', 'B', 'C']),
            'code' => fake()->randomLetter().fake()->numerify('#'),
            'description' => null,
        ];
    }
}

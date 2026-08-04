<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Building;
use App\Domain\Living\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Floor>
 */
class FloorFactory extends Factory
{
    protected $model = Floor::class;

    public function definition(): array
    {
        $level = fake()->numberBetween(1, 4);

        return [
            'building_id' => Building::factory(),
            'name' => "Lantai {$level}",
            'level' => $level,
            'description' => null,
        ];
    }
}

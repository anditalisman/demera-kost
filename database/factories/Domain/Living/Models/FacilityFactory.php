<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['AC', 'Kasur Queen', 'Lemari Baju', 'Meja Kerja', 'Kamar Mandi Dalam', 'Water Heater']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'icon' => null,
            'type' => 'room',
            'description' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function shared(): static
    {
        return $this->state(fn () => ['type' => 'shared']);
    }
}

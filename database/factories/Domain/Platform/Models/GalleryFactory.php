<?php

namespace Database\Factories\Domain\Platform\Models;

use App\Domain\Platform\Models\Gallery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
{
    protected $model = Gallery::class;

    public function definition(): array
    {
        return [
            'title' => fake()->words(2, true),
            'category' => fake()->randomElement(['property', 'room_common_area', 'facility']),
            'image_path' => null,
            'thumbnail_path' => null,
            'caption' => null,
            'sort_order' => 0,
            'is_published' => true,
        ];
    }
}

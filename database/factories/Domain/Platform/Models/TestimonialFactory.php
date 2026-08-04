<?php

namespace Database\Factories\Domain\Platform\Models;

use App\Domain\Platform\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'author_name' => fake()->name(),
            'author_role' => 'Penghuni sejak '.fake()->year(),
            'rating' => fake()->numberBetween(4, 5),
            'content' => fake()->paragraph(2),
            'source' => 'living',
            'is_published' => true,
            'is_featured' => fake()->boolean(30),
            'sort_order' => 0,
        ];
    }
}

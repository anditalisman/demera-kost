<?php

namespace Database\Factories\Domain\Platform\Models;

use App\Domain\Platform\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'question' => fake()->sentence().'?',
            'answer' => fake()->paragraph(),
            'category' => fake()->randomElement(['general', 'booking', 'payment']),
            'is_published' => true,
            'sort_order' => 0,
        ];
    }
}

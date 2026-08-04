<?php

namespace Database\Factories\Domain\Platform\Models;

use App\Domain\Platform\Models\ContentPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentPage>
 */
class ContentPageFactory extends Factory
{
    protected $model = ContentPage::class;

    public function definition(): array
    {
        return [
            'group' => 'hero_slide',
            'key' => null,
            'title' => fake()->sentence(4),
            'subtitle' => fake()->words(3, true),
            'body' => fake()->paragraph(),
            'is_published' => true,
            'sort_order' => 0,
        ];
    }
}

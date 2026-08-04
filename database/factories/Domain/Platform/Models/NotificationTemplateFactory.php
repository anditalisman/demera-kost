<?php

namespace Database\Factories\Domain\Platform\Models;

use App\Domain\Platform\Models\NotificationTemplate;
use App\Enums\NotificationChannel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    protected $model = NotificationTemplate::class;

    public function definition(): array
    {
        return [
            'code' => 'template_'.fake()->unique()->word(),
            'name' => fake()->words(3, true),
            'channel' => NotificationChannel::Email,
            'subject' => fake()->sentence(),
            'body_template' => 'Halo {{name}}, ini adalah pemberitahuan dari Demera.',
            'is_active' => true,
        ];
    }
}

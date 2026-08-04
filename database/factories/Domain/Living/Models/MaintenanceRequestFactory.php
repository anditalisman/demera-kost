<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\MaintenanceRequest;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\Tenant;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceRequest>
 */
class MaintenanceRequestFactory extends Factory
{
    protected $model = MaintenanceRequest::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'room_id' => Room::factory(),
            'category' => fake()->randomElement(['electrical', 'plumbing', 'furniture', 'other']),
            'title' => 'AC kamar tidak dingin',
            'description' => 'AC sudah dinyalakan sejak semalam tapi ruangan tetap panas.',
            'priority' => MaintenancePriority::Normal,
            'status' => MaintenanceStatus::New,
        ];
    }
}

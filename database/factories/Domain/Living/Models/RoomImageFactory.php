<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\RoomImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomImage>
 */
class RoomImageFactory extends Factory
{
    protected $model = RoomImage::class;

    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'path' => null,
            'thumbnail_path' => null,
            'is_primary' => false,
            'sort_order' => 0,
            'caption' => null,
        ];
    }
}

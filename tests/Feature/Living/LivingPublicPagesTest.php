<?php

namespace Tests\Feature\Living;

use App\Domain\Living\Models\Building;
use App\Domain\Living\Models\Facility;
use App\Domain\Living\Models\Floor;
use App\Domain\Living\Models\Property;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\RoomType;
use App\Enums\RoomStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LivingPublicPagesTest extends TestCase
{
    use RefreshDatabase;

    private function createRoom(array $overrides = []): Room
    {
        $property = Property::create([
            'name' => 'Demera Living Kemang',
            'slug' => 'demera-living-kemang',
            'address' => 'Jl. Kemang Raya No. 1',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
        ]);
        $building = Building::create(['property_id' => $property->id, 'name' => 'Gedung A']);
        $floor = Floor::create(['building_id' => $building->id, 'name' => 'Lantai 1', 'level' => 1]);
        $roomType = RoomType::create([
            'property_id' => $property->id,
            'name' => 'Standard',
            'slug' => 'standard',
            'base_price' => 1500000,
        ]);

        return Room::create([
            'property_id' => $property->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'room_type_id' => $roomType->id,
            'room_number' => 'A101',
            'slug' => 'a101',
            'status' => RoomStatus::Available,
            'capacity' => 1,
            'monthly_price' => 1500000,
            'deposit_amount' => 1500000,
            'is_active' => true,
            ...$overrides,
        ]);
    }

    public function test_living_hub_page_renders(): void
    {
        $this->get('/living')->assertOk();
    }

    public function test_room_catalog_lists_active_rooms(): void
    {
        $this->createRoom();

        $response = $this->get('/living/rooms');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Living/Rooms/Index')
            ->has('rooms.data', 1)
        );
    }

    public function test_inactive_rooms_are_hidden_from_catalog(): void
    {
        $this->createRoom(['is_active' => false, 'room_number' => 'A102', 'slug' => 'a102']);

        $response = $this->get('/living/rooms');

        $response->assertInertia(fn ($page) => $page->has('rooms.data', 0));
    }

    public function test_room_detail_page_renders(): void
    {
        $room = $this->createRoom();

        $this->get("/living/rooms/{$room->slug}")->assertOk();
    }

    public function test_unknown_room_slug_returns_404(): void
    {
        $this->get('/living/rooms/does-not-exist')->assertNotFound();
    }

    public function test_facilities_page_renders(): void
    {
        Facility::create(['name' => 'AC', 'slug' => 'ac', 'type' => 'room']);
        Facility::create(['name' => 'Dapur Bersama', 'slug' => 'dapur-bersama', 'type' => 'shared']);

        $this->get('/living/facilities')->assertOk();
    }

    public function test_gallery_facilities_location_faq_contact_pages_render(): void
    {
        $this->get('/living/gallery')->assertOk();
        $this->get('/living/location')->assertOk();
        $this->get('/living/faq')->assertOk();
        $this->get('/living/contact')->assertOk();
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Domain\Living\Models\Building;
use App\Domain\Living\Models\Facility;
use App\Domain\Living\Models\Floor;
use App\Domain\Living\Models\Property;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\RoomType;
use App\Enums\RoomStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RoomCatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function customer(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('customer');

        return $user;
    }

    public function test_customer_cannot_access_room_admin_pages(): void
    {
        $this->actingAs($this->customer())->get('/admin/rooms')->assertForbidden();
        $this->actingAs($this->customer())->get('/admin/properties')->assertForbidden();
    }

    public function test_admin_can_create_property_building_and_floor(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/properties', [
            'name' => 'Demera Living Kemang',
            'address' => 'Jl. Kemang Raya No. 1',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'is_active' => true,
        ])->assertRedirect();

        $property = Property::query()->where('name', 'Demera Living Kemang')->firstOrFail();
        $this->assertNotEmpty($property->slug);

        $this->actingAs($admin)->post("/admin/properties/{$property->id}/buildings", [
            'name' => 'Gedung A',
        ])->assertRedirect();

        $building = Building::query()->where('property_id', $property->id)->firstOrFail();
        $this->assertSame('Gedung A', $building->name);

        $this->actingAs($admin)->post("/admin/buildings/{$building->id}/floors", [
            'name' => 'Lantai 1',
            'level' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('floors', ['building_id' => $building->id, 'name' => 'Lantai 1', 'level' => 1]);
    }

    public function test_admin_can_create_room_type_and_facility(): void
    {
        $admin = $this->admin();
        $property = Property::factory()->create();

        $this->actingAs($admin)->post('/admin/room-types', [
            'property_id' => $property->id,
            'name' => 'Kamar Standar',
            'base_price' => 1500000,
            'base_deposit' => 500000,
            'default_capacity' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('room_types', ['property_id' => $property->id, 'name' => 'Kamar Standar']);

        $this->actingAs($admin)->post('/admin/facilities', [
            'name' => 'AC',
            'type' => 'room',
            'is_active' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('facilities', ['name' => 'AC', 'type' => 'room']);
    }

    public function test_admin_can_create_and_update_room(): void
    {
        $admin = $this->admin();
        $property = Property::factory()->create();
        $building = Building::factory()->create(['property_id' => $property->id]);
        $floor = Floor::factory()->create(['building_id' => $building->id]);
        $roomType = RoomType::factory()->create(['property_id' => $property->id]);

        $response = $this->actingAs($admin)->post('/admin/rooms', [
            'property_id' => $property->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'room_type_id' => $roomType->id,
            'room_number' => '101',
            'capacity' => 2,
            'monthly_price' => 1750000,
            'deposit_amount' => 500000,
            'is_active' => true,
        ]);

        $room = Room::query()->where('room_number', '101')->firstOrFail();
        $response->assertRedirect("/admin/rooms/{$room->id}/edit");
        $this->assertNotEmpty($room->slug);
        $this->assertSame(RoomStatus::Available, $room->status);

        $this->actingAs($admin)->put("/admin/rooms/{$room->id}", [
            'property_id' => $property->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'room_type_id' => $roomType->id,
            'room_number' => '101',
            'capacity' => 2,
            'monthly_price' => 1800000,
            'deposit_amount' => 500000,
            'is_active' => true,
        ])->assertRedirect();

        $this->assertSame('1800000.00', $room->fresh()->monthly_price);
    }

    public function test_admin_can_upload_reorder_and_delete_room_photos(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public_media');

        $admin = $this->admin();
        $room = Room::factory()->create();

        $this->actingAs($admin)->post("/admin/rooms/{$room->id}/photos", [
            'image' => UploadedFile::fake()->image('room-1.jpg', 800, 600),
        ])->assertRedirect();

        $firstImage = $room->images()->firstOrFail();
        $this->assertTrue($firstImage->is_primary);

        $this->actingAs($admin)->post("/admin/rooms/{$room->id}/photos", [
            'image' => UploadedFile::fake()->image('room-2.jpg', 800, 600),
        ])->assertRedirect();

        $secondImage = $room->images()->where('id', '!=', $firstImage->id)->firstOrFail();
        $this->assertFalse($secondImage->fresh()->is_primary);

        $this->actingAs($admin)->put("/admin/rooms/{$room->id}/photos/{$secondImage->id}/primary")->assertRedirect();
        $this->assertTrue($secondImage->fresh()->is_primary);
        $this->assertFalse($firstImage->fresh()->is_primary);

        $this->actingAs($admin)->put("/admin/rooms/{$room->id}/photos/reorder", [
            'ids' => [$firstImage->id, $secondImage->id],
        ])->assertRedirect();

        $this->actingAs($admin)->delete("/admin/rooms/{$room->id}/photos/{$secondImage->id}")->assertRedirect();
        $this->assertSoftDeleted('room_images', ['id' => $secondImage->id]);
        $this->assertTrue($firstImage->fresh()->is_primary);
    }

    public function test_admin_can_sync_room_facilities(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create();
        $facilities = Facility::factory()->count(3)->create();

        $this->actingAs($admin)->put("/admin/rooms/{$room->id}/facilities", [
            'facility_ids' => $facilities->pluck('id')->take(2)->toArray(),
        ])->assertRedirect();

        $this->assertCount(2, $room->facilities()->get());
    }

    public function test_admin_can_change_room_status_and_it_is_recorded_in_history(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Available]);

        $this->actingAs($admin)->put("/admin/rooms/{$room->id}/status", [
            'status' => RoomStatus::Maintenance->value,
            'reason' => 'Perbaikan AC',
        ])->assertRedirect();

        $room->refresh();
        $this->assertSame(RoomStatus::Maintenance, $room->status);

        $history = $room->statusHistories()->latest('created_at')->first();
        $this->assertSame('available', $history->from_status);
        $this->assertSame('maintenance', $history->to_status);
        $this->assertSame('Perbaikan AC', $history->reason);
        $this->assertSame($admin->id, $history->changed_by);
    }

    public function test_admin_can_bulk_update_room_status(): void
    {
        $admin = $this->admin();
        $rooms = Room::factory()->count(3)->create(['status' => RoomStatus::Available]);

        $this->actingAs($admin)->post('/admin/rooms/bulk-status', [
            'room_ids' => $rooms->pluck('id')->toArray(),
            'status' => RoomStatus::Inactive->value,
        ])->assertRedirect();

        foreach ($rooms as $room) {
            $this->assertSame(RoomStatus::Inactive, $room->fresh()->status);
        }
    }

    public function test_admin_can_delete_room(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create();

        $this->actingAs($admin)->delete("/admin/rooms/{$room->id}")->assertRedirect('/admin/rooms');
        $this->assertSoftDeleted('rooms', ['id' => $room->id]);
    }

    public function test_public_catalog_filters_by_status_price_and_room_type(): void
    {
        $roomType = RoomType::factory()->create();

        $cheapAvailable = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::Available,
            'monthly_price' => 1000000,
            'is_active' => true,
        ]);
        $expensiveAvailable = Room::factory()->create([
            'status' => RoomStatus::Available,
            'monthly_price' => 3000000,
            'is_active' => true,
        ]);
        Room::factory()->create([
            'status' => RoomStatus::Occupied,
            'monthly_price' => 1200000,
            'is_active' => true,
        ]);

        $response = $this->get('/living/rooms?status=available&max_price=1500000');
        $response->assertOk();
        $rooms = $response->viewData('page')['props']['rooms']['data'];
        $ids = collect($rooms)->pluck('id');

        $this->assertTrue($ids->contains($cheapAvailable->id));
        $this->assertFalse($ids->contains($expensiveAvailable->id));
    }

    public function test_public_catalog_sorts_by_price(): void
    {
        $cheap = Room::factory()->create(['monthly_price' => 900000, 'status' => RoomStatus::Available, 'is_active' => true]);
        $expensive = Room::factory()->create(['monthly_price' => 2500000, 'status' => RoomStatus::Available, 'is_active' => true]);

        $response = $this->get('/living/rooms?sort=price_asc');
        $rooms = $response->viewData('page')['props']['rooms']['data'];

        $this->assertSame($cheap->id, $rooms[0]['id']);
        $this->assertSame($expensive->id, $rooms[count($rooms) - 1]['id']);
    }
}

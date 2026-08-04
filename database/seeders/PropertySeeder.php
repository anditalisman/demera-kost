<?php

namespace Database\Seeders;

use App\Domain\Living\Models\Building;
use App\Domain\Living\Models\Facility;
use App\Domain\Living\Models\Floor;
use App\Domain\Living\Models\Property;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\RoomImage;
use App\Domain\Living\Models\RoomType;
use App\Domain\Platform\Services\ImageUploadService;
use App\Domain\Platform\Services\PlaceholderImageGenerator;
use App\Enums\RoomStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $imageUploader = app(ImageUploadService::class);

        $property = Property::query()->updateOrCreate(
            ['slug' => 'demera-living-kemang'],
            [
                'name' => 'Demera Living Kemang',
                'address' => 'Jl. Kemang Selatan VIII No. 12',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12560',
                'latitude' => -6.2649,
                'longitude' => 106.8133,
                'description' => 'Kost eksklusif di kawasan Kemang, dekat perkantoran, kafe, dan akses transportasi umum.',
                'house_rules' => "1. Tidak membawa tamu menginap tanpa izin pengelola.\n2. Menjaga kebersihan kamar dan area bersama.\n3. Jam bertamu maksimal pukul 21.00.\n4. Dilarang merokok dan membawa hewan peliharaan di dalam kamar.\n5. Wajib membayar sewa paling lambat pada tanggal jatuh tempo.",
                'contact_phone' => '021-7180001',
                'contact_whatsapp' => '+6281200000001',
                'is_active' => true,
            ],
        );

        $buildingA = Building::query()->updateOrCreate(['property_id' => $property->id, 'name' => 'Gedung A'], ['code' => 'A']);
        $buildingB = Building::query()->updateOrCreate(['property_id' => $property->id, 'name' => 'Gedung B'], ['code' => 'B']);

        $floors = [
            Floor::query()->updateOrCreate(['building_id' => $buildingA->id, 'level' => 1], ['name' => 'Lantai 1']),
            Floor::query()->updateOrCreate(['building_id' => $buildingA->id, 'level' => 2], ['name' => 'Lantai 2']),
            Floor::query()->updateOrCreate(['building_id' => $buildingB->id, 'level' => 1], ['name' => 'Lantai 1']),
        ];

        $standard = RoomType::query()->updateOrCreate(
            ['property_id' => $property->id, 'slug' => 'standard'],
            ['name' => 'Standard', 'description' => 'Kamar fungsional dengan fasilitas dasar lengkap.', 'base_price' => 1300000, 'base_deposit' => 500000, 'size_sqm' => 9, 'default_capacity' => 1],
        );
        $deluxe = RoomType::query()->updateOrCreate(
            ['property_id' => $property->id, 'slug' => 'deluxe'],
            ['name' => 'Deluxe', 'description' => 'Kamar lebih luas dengan kamar mandi dalam.', 'base_price' => 1800000, 'base_deposit' => 1000000, 'size_sqm' => 14, 'default_capacity' => 1],
        );
        $vip = RoomType::query()->updateOrCreate(
            ['property_id' => $property->id, 'slug' => 'vip'],
            ['name' => 'VIP', 'description' => 'Kamar premium dengan AC, water heater, dan meja kerja luas.', 'base_price' => 2500000, 'base_deposit' => 1500000, 'size_sqm' => 20, 'default_capacity' => 2],
        );

        $roomFacilities = Facility::query()->where('type', 'room')->pluck('id');

        // 12 rooms covering every RoomStatus at least once.
        $plan = [
            ['number' => 'A101', 'building' => $buildingA, 'floor' => $floors[0], 'type' => $standard, 'status' => RoomStatus::Available],
            ['number' => 'A102', 'building' => $buildingA, 'floor' => $floors[0], 'type' => $standard, 'status' => RoomStatus::Available],
            ['number' => 'A103', 'building' => $buildingA, 'floor' => $floors[0], 'type' => $standard, 'status' => RoomStatus::Available],
            ['number' => 'A201', 'building' => $buildingA, 'floor' => $floors[1], 'type' => $deluxe, 'status' => RoomStatus::Available],
            ['number' => 'A202', 'building' => $buildingA, 'floor' => $floors[1], 'type' => $deluxe, 'status' => RoomStatus::Held],
            ['number' => 'A203', 'building' => $buildingA, 'floor' => $floors[1], 'type' => $deluxe, 'status' => RoomStatus::AwaitingPayment],
            ['number' => 'B101', 'building' => $buildingB, 'floor' => $floors[2], 'type' => $vip, 'status' => RoomStatus::Occupied],
            ['number' => 'B102', 'building' => $buildingB, 'floor' => $floors[2], 'type' => $vip, 'status' => RoomStatus::Occupied],
            ['number' => 'B103', 'building' => $buildingB, 'floor' => $floors[2], 'type' => $standard, 'status' => RoomStatus::Maintenance],
            ['number' => 'B104', 'building' => $buildingB, 'floor' => $floors[2], 'type' => $standard, 'status' => RoomStatus::Inactive],
            ['number' => 'B105', 'building' => $buildingB, 'floor' => $floors[2], 'type' => $deluxe, 'status' => RoomStatus::Available],
            ['number' => 'B106', 'building' => $buildingB, 'floor' => $floors[2], 'type' => $vip, 'status' => RoomStatus::Available],
        ];

        foreach ($plan as $spec) {
            /** @var RoomType $type */
            $type = $spec['type'];

            $room = Room::query()->updateOrCreate(
                ['property_id' => $property->id, 'room_number' => $spec['number']],
                [
                    'building_id' => $spec['building']->id,
                    'floor_id' => $spec['floor']->id,
                    'room_type_id' => $type->id,
                    'slug' => Str::slug($property->slug.'-'.$spec['number']),
                    'status' => $spec['status'],
                    'size_sqm' => $type->size_sqm,
                    'capacity' => $type->default_capacity,
                    'monthly_price' => $type->base_price,
                    'deposit_amount' => $type->base_deposit,
                    'additional_fees' => [
                        ['label' => 'Listrik', 'amount' => 150000],
                        ['label' => 'Kebersihan', 'amount' => 50000],
                    ],
                    'description' => "Kamar {$type->name} yang nyaman dan siap huni di {$spec['building']->name}, {$spec['floor']->name}.",
                    'available_from' => $spec['status'] === RoomStatus::Available ? now()->toDateString() : now()->addDays(14)->toDateString(),
                    'is_active' => $spec['status'] !== RoomStatus::Inactive,
                ],
            );

            $room->facilities()->sync($roomFacilities->random(min(4, $roomFacilities->count()))->all());

            if ($room->images()->count() === 0) {
                foreach (range(1, 2) as $i) {
                    $upload = $imageUploader->upload(
                        PlaceholderImageGenerator::make("Kamar {$spec['number']}"),
                        "rooms/{$room->id}",
                    );

                    RoomImage::create([
                        'room_id' => $room->id,
                        'path' => $upload['path'],
                        'thumbnail_path' => $upload['thumbnail_path'],
                        'is_primary' => $i === 1,
                        'sort_order' => $i,
                    ]);
                }
            }
        }
    }
}

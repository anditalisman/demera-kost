<?php

namespace Database\Seeders;

use App\Domain\Living\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $roomFacilities = ['AC', 'Kasur Queen Size', 'Lemari Baju', 'Meja & Kursi Kerja', 'Kamar Mandi Dalam', 'Water Heater', 'Jendela'];
        $sharedFacilities = ['WiFi Kecepatan Tinggi', 'Dapur Bersama', 'Ruang Tamu', 'Area Parkir', 'CCTV 24 Jam', 'Laundry', 'Rooftop'];

        foreach ($roomFacilities as $index => $name) {
            Facility::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'type' => 'room', 'sort_order' => $index, 'is_active' => true],
            );
        }

        foreach ($sharedFacilities as $index => $name) {
            Facility::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'type' => 'shared', 'sort_order' => $index, 'is_active' => true],
            );
        }
    }
}

<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        $name = 'Demera Living '.fake()->unique()->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'province' => 'DKI Jakarta',
            'postal_code' => fake()->postcode(),
            'latitude' => fake()->latitude(-6.4, -6.1),
            'longitude' => fake()->longitude(106.7, 106.9),
            'description' => 'Kost eksklusif dengan lingkungan yang aman, nyaman, dan dekat dengan pusat kegiatan kota.',
            'house_rules' => "1. Tidak membawa tamu menginap tanpa izin.\n2. Menjaga kebersihan kamar dan area bersama.\n3. Jam malam pukul 22.00 untuk tamu.\n4. Dilarang merokok di dalam kamar.\n5. Membayar sewa paling lambat tanggal jatuh tempo.",
            'contact_phone' => '021-'.fake()->numerify('#######'),
            'contact_whatsapp' => '+62812'.fake()->numerify('########'),
            'is_active' => true,
        ];
    }
}

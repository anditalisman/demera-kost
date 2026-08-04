<?php

namespace Database\Seeders;

use App\Domain\Platform\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        if (Testimonial::query()->exists()) {
            return;
        }

        $testimonials = [
            ['author_name' => 'Budi Santoso', 'author_role' => 'Penghuni sejak 2024', 'content' => 'Kamarnya bersih, pengelola responsif, dan proses sewanya jelas dari awal. Sangat nyaman untuk kerja dari rumah.', 'rating' => 5, 'is_featured' => true],
            ['author_name' => 'Siti Rahma', 'author_role' => 'Penghuni sejak 2023', 'content' => 'Lokasinya strategis dan dekat kampus. Fasilitas bersama juga terawat dengan baik.', 'rating' => 5, 'is_featured' => true],
            ['author_name' => 'Andi Wijaya', 'author_role' => 'Penghuni sejak 2024', 'content' => 'Tim Demera sangat membantu saat proses pindah kamar. Semua transparan soal biaya.', 'rating' => 4, 'is_featured' => false],
            ['author_name' => 'Dewi Lestari', 'author_role' => 'Penghuni sejak 2025', 'content' => 'Suka dengan keamanannya, ada CCTV 24 jam dan akses yang terkontrol.', 'rating' => 5, 'is_featured' => true],
            ['author_name' => 'Fajar Nugroho', 'author_role' => 'Penghuni sejak 2023', 'content' => 'Harga sesuai dengan fasilitas yang didapat. Tagihan bulanan juga selalu jelas.', 'rating' => 4, 'is_featured' => false],
        ];

        foreach ($testimonials as $index => $t) {
            Testimonial::create([
                ...$t,
                'source' => 'living',
                'is_published' => true,
                'sort_order' => $index,
            ]);
        }
    }
}

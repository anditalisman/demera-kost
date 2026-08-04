<?php

namespace Database\Seeders;

use App\Domain\Platform\Models\Gallery;
use App\Domain\Platform\Services\ImageUploadService;
use App\Domain\Platform\Services\PlaceholderImageGenerator;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        if (Gallery::query()->exists()) {
            return;
        }

        $imageUploader = app(ImageUploadService::class);

        $items = [
            ['title' => 'Tampak Depan Gedung A', 'category' => 'property'],
            ['title' => 'Tampak Depan Gedung B', 'category' => 'property'],
            ['title' => 'Ruang Tamu Bersama', 'category' => 'room_common_area'],
            ['title' => 'Dapur Bersama', 'category' => 'room_common_area'],
            ['title' => 'Area Rooftop', 'category' => 'facility'],
            ['title' => 'Area Parkir', 'category' => 'facility'],
            ['title' => 'Kegiatan Gathering Penghuni', 'category' => 'event'],
            ['title' => 'Tim Demera Living', 'category' => 'company'],
        ];

        foreach ($items as $index => $item) {
            $upload = $imageUploader->upload(PlaceholderImageGenerator::make($item['title']), 'galleries');

            Gallery::create([
                'title' => $item['title'],
                'category' => $item['category'],
                'image_path' => $upload['path'],
                'thumbnail_path' => $upload['thumbnail_path'],
                'sort_order' => $index,
                'is_published' => true,
            ]);
        }
    }
}

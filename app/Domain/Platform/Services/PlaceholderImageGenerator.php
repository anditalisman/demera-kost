<?php

namespace App\Domain\Platform\Services;

use Illuminate\Http\UploadedFile;

/**
 * Generates simple colored placeholder JPEGs for seeding — used only in
 * seeders/demo data so the app has real files in object storage without
 * depending on external placeholder image services or committed binaries.
 */
class PlaceholderImageGenerator
{
    private const PALETTE = [
        [232, 210, 163], // cream
        [211, 186, 151], // beige
        [200, 90, 46],   // terracotta
        [58, 58, 65],    // charcoal
        [171, 138, 97],  // beige-500
    ];

    public static function make(string $label, int $width = 1200, int $height = 800): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        [$r, $g, $b] = self::PALETTE[array_rand(self::PALETTE)];
        imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));

        $textColor = imagecolorallocate($image, 255, 255, 255);
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($label);
        imagestring($image, $font, (int) (($width - $textWidth) / 2), (int) ($height / 2), $label, $textColor);

        $tmpPath = tempnam(sys_get_temp_dir(), 'demera_placeholder_').'.jpg';
        imagejpeg($image, $tmpPath, 85);
        imagedestroy($image);

        return new UploadedFile($tmpPath, basename($tmpPath), 'image/jpeg', null, true);
    }
}

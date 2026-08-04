<?php

namespace App\Domain\Platform\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Handles image uploads to the public_media disk: validates MIME/size,
 * assigns a random filename (never trusts the client's original name),
 * compresses the main image, and produces a lightweight thumbnail.
 */
class ImageUploadService
{
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    private const MAX_SIZE_BYTES = 8 * 1024 * 1024; // 8MB

    public function __construct(
        private readonly ImageManager $manager = new ImageManager(new Driver),
    ) {}

    /**
     * @return array{path: string, thumbnail_path: string}
     */
    public function upload(UploadedFile $file, string $directory, int $maxWidth = 1920, int $thumbnailWidth = 400): array
    {
        $this->assertValid($file);

        $filename = Str::uuid()->toString();
        $directory = trim($directory, '/');

        $image = $this->manager->decodePath($file->getRealPath());

        if ($image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        $encoded = (string) $image->encodeUsingFileExtension('webp', quality: 82);
        $path = "{$directory}/{$filename}.webp";
        Storage::disk('public_media')->put($path, $encoded);

        $thumbImage = $this->manager->decodePath($file->getRealPath())->scaleDown(width: $thumbnailWidth);
        $encodedThumb = (string) $thumbImage->encodeUsingFileExtension('webp', quality: 75);
        $thumbPath = "{$directory}/{$filename}_thumb.webp";
        Storage::disk('public_media')->put($thumbPath, $encodedThumb);

        return ['path' => $path, 'thumbnail_path' => $thumbPath];
    }

    public function delete(?string $path, ?string $thumbnailPath = null): void
    {
        if ($path) {
            Storage::disk('public_media')->delete($path);
        }

        if ($thumbnailPath) {
            Storage::disk('public_media')->delete($thumbnailPath);
        }
    }

    private function assertValid(UploadedFile $file): void
    {
        if (! in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException('Tipe berkas tidak didukung. Gunakan JPEG, PNG, atau WebP.');
        }

        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            throw new \InvalidArgumentException('Ukuran berkas melebihi batas maksimum 8MB.');
        }
    }
}

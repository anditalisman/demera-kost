<?php

namespace App\Domain\Platform\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Handles uploads to the private_documents disk (identity documents, signed
 * contracts, payment proofs). Files are never publicly readable — retrieve
 * them via PrivateDocumentUrlService's short-lived signed URLs only.
 */
class PrivateDocumentUploadService
{
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'application/pdf'];

    private const MAX_SIZE_BYTES = 10 * 1024 * 1024; // 10MB

    /**
     * @return array{path: string, original_filename: string, mime_type: string, size_bytes: int}
     */
    public function upload(UploadedFile $file, string $directory): array
    {
        $this->assertValid($file);

        $directory = trim($directory, '/');
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $path = "{$directory}/".Str::uuid()->toString().".{$extension}";

        Storage::disk('private_documents')->put($path, file_get_contents($file->getRealPath()));

        return [
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ];
    }

    public function delete(string $path): void
    {
        Storage::disk('private_documents')->delete($path);
    }

    private function assertValid(UploadedFile $file): void
    {
        if (! in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException('Tipe berkas tidak didukung. Gunakan JPEG, PNG, atau PDF.');
        }

        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            throw new \InvalidArgumentException('Ukuran berkas melebihi batas maksimum 10MB.');
        }
    }
}

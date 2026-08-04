<?php

namespace App\Domain\Platform\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Generates short-lived signed URLs for private documents (identity docs,
 * signed contracts, payment proofs). Never expose these files via a public
 * disk or a permanent URL — access must always go through here so links
 * expire and every access is effectively scoped/auditable.
 */
class PrivateDocumentUrlService
{
    public function temporaryUrl(string $path, int $expiresInMinutes = 10): string
    {
        $url = Storage::disk('private_documents')->temporaryUrl(
            $path,
            now()->addMinutes($expiresInMinutes),
        );

        return $this->rewriteForBrowserAccess($url);
    }

    /**
     * In local/dev, the app talks to MinIO via its internal Docker hostname
     * (e.g. http://minio:9000), but presigned URLs must be reachable from the
     * user's browser. Rewrite the host portion to the public-facing endpoint
     * when the two differ; in production (real S3) this is a no-op because
     * OBJECT_STORAGE_ENDPOINT_PUBLIC_URL is left empty.
     */
    private function rewriteForBrowserAccess(string $url): string
    {
        $internalEndpoint = config('filesystems.disks.private_documents.endpoint');
        $publicEndpoint = env('OBJECT_STORAGE_ENDPOINT_PUBLIC_URL');

        if (empty($internalEndpoint) || empty($publicEndpoint) || $internalEndpoint === $publicEndpoint) {
            return $url;
        }

        return str_replace($internalEndpoint, rtrim($publicEndpoint, '/'), $url);
    }
}

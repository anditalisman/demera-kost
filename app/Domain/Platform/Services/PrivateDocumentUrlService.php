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
        // Signed against the "private_documents_signed" disk (public-facing
        // endpoint) rather than "private_documents" (internal endpoint) —
        // AWS SigV4 signs the Host header itself, so a URL generated for one
        // host and then rewritten to another fails signature validation the
        // moment a browser actually requests it. See filesystems.php.
        return Storage::disk('private_documents_signed')->temporaryUrl(
            $path,
            now()->addMinutes($expiresInMinutes),
        );
    }
}

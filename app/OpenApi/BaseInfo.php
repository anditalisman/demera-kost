<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Demera API',
    description: 'Public JSON API for Demera Living & Demera Fashion (Tahap 1). '
        .'Web pages are rendered server-side via Inertia and are not part of this '
        .'documentation — these endpoints exist for external/mobile clients and webhooks.',
    contact: new OA\Contact(email: 'developer@demera.my.id'),
)]
#[OA\Server(url: '/api/v1', description: 'Versioned public API')]
#[OA\Tag(name: 'Living', description: 'Demera Living room catalog (public, read-only)')]
#[OA\Tag(name: 'Fashion', description: 'Demera Fashion launch notifications (Segera Hadir)')]
class BaseInfo {}

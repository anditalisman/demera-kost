<?php

namespace App\Http\Controllers\Public;

use App\Domain\Living\Models\Room;
use App\Domain\Platform\Models\ContentPage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $disallow = [
            '/admin', '/account', '/login', '/register', '/profile',
            '/force-password-update', '/forgot-password', '/reset-password',
            '/confirm-password', '/verify-email', '/other-browser-sessions',
            '/storage/private',
        ];

        $lines = ['User-agent: *'];
        foreach ($disallow as $path) {
            $lines[] = "Disallow: {$path}";
        }
        $lines[] = '';
        $lines[] = 'Sitemap: '.url('/sitemap.xml');

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }

    public function sitemap(): Response
    {
        $staticPaths = [
            '/', '/living', '/living/rooms', '/living/gallery', '/living/facilities',
            '/living/location', '/living/faq', '/living/contact',
            '/fashion', '/fashion/products', '/fashion/categories',
        ];

        $urls = collect($staticPaths)->map(fn ($path) => ['loc' => url($path), 'priority' => $path === '/' ? '1.0' : '0.8']);

        $urls = $urls->merge(
            ContentPage::query()->group('policy')->published()->get()->map(fn ($page) => [
                'loc' => url("/kebijakan/{$page->key}"),
                'priority' => '0.3',
            ]),
        );

        $urls = $urls->merge(
            Room::query()->publiclyVisible()->get()->map(fn ($room) => [
                'loc' => url("/living/rooms/{$room->slug}"),
                'priority' => '0.9',
                'lastmod' => $room->updated_at->toAtomString(),
            ]),
        );

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}

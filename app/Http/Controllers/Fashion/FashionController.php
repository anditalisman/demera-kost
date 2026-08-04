<?php

namespace App\Http\Controllers\Fashion;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class FashionController extends Controller
{
    /**
     * Lightweight static preview content for the "coming soon" catalog.
     * Real product/category tables land when the Fashion module's own
     * build phase begins — this is intentionally not backed by the
     * database yet, and must never share tables with Demera Living.
     */
    private const PREVIEW_CATEGORIES = [
        ['name' => 'Outerwear', 'description' => 'Jaket dan mantel dengan potongan minimalis.'],
        ['name' => 'Essentials', 'description' => 'Basic wear berkualitas untuk sehari-hari.'],
        ['name' => 'Accessories', 'description' => 'Pelengkap gaya dengan detail editorial.'],
    ];

    private const PREVIEW_PRODUCTS = [
        ['name' => 'Structured Trench Coat', 'category' => 'Outerwear', 'slug' => 'structured-trench-coat'],
        ['name' => 'Essential Cotton Tee', 'category' => 'Essentials', 'slug' => 'essential-cotton-tee'],
        ['name' => 'Minimalist Tote Bag', 'category' => 'Accessories', 'slug' => 'minimalist-tote-bag'],
    ];

    public function index(): Response
    {
        return Inertia::render('Fashion/ComingSoon', [
            'categories' => self::PREVIEW_CATEGORIES,
            'products' => self::PREVIEW_PRODUCTS,
        ]);
    }

    public function products(): Response
    {
        return Inertia::render('Fashion/ComingSoon', [
            'categories' => self::PREVIEW_CATEGORIES,
            'products' => self::PREVIEW_PRODUCTS,
        ]);
    }

    public function categories(): Response
    {
        return Inertia::render('Fashion/ComingSoon', [
            'categories' => self::PREVIEW_CATEGORIES,
            'products' => [],
        ]);
    }

    public function product(string $slug): Response
    {
        $product = collect(self::PREVIEW_PRODUCTS)->firstWhere('slug', $slug);

        abort_if(! $product, 404);

        return Inertia::render('Fashion/ComingSoon', [
            'categories' => self::PREVIEW_CATEGORIES,
            'products' => [$product],
            'singleProduct' => $product,
        ]);
    }
}

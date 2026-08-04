<?php

namespace App\Http\Controllers\Public;

use App\Domain\Platform\Models\ContentPage;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PolicyPageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = ContentPage::query()
            ->group('policy')
            ->where('key', $slug)
            ->published()
            ->firstOrFail();

        return Inertia::render('Public/Policy', [
            'page' => $page,
        ]);
    }
}

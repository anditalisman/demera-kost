<?php

namespace App\Http\Controllers\Public;

use App\Domain\Living\Models\Room;
use App\Domain\Platform\Models\ContentPage;
use App\Domain\Platform\Models\Faq;
use App\Domain\Platform\Models\Gallery;
use App\Domain\Platform\Models\Testimonial;
use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Public/Landing', [
            'heroSlides' => ContentPage::query()->group('hero_slide')->published()->orderBy('sort_order')->get(),
            'businessInfo' => ContentPage::query()->group('business_info')->published()->get()->keyBy('key'),
            'featuredRooms' => Room::query()
                ->publiclyVisible()
                ->where('status', RoomStatus::Available)
                ->with(['primaryImage', 'roomType', 'property'])
                ->latest()
                ->take(6)
                ->get(),
            'galleries' => Gallery::query()->published()->orderBy('sort_order')->take(8)->get(),
            'testimonials' => Testimonial::query()->published()->orderByDesc('is_featured')->orderBy('sort_order')->take(6)->get(),
            'faqs' => Faq::query()->published()->orderBy('sort_order')->take(8)->get(),
        ]);
    }
}

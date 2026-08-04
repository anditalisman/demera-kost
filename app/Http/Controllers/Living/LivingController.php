<?php

namespace App\Http\Controllers\Living;

use App\Domain\Living\Models\Facility;
use App\Domain\Living\Models\Property;
use App\Domain\Living\Models\Room;
use App\Domain\Platform\Models\ContentPage;
use App\Domain\Platform\Models\Faq;
use App\Domain\Platform\Models\Gallery;
use App\Domain\Platform\Models\Testimonial;
use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class LivingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Living/Index', [
            'intro' => ContentPage::query()->group('business_info')->where('key', 'living')->published()->first(),
            'availableCount' => Room::query()->publiclyVisible()->where('status', RoomStatus::Available)->count(),
            'featuredRooms' => Room::query()
                ->publiclyVisible()
                ->where('status', RoomStatus::Available)
                ->with(['primaryImage', 'roomType', 'property'])
                ->latest()
                ->take(6)
                ->get(),
            'testimonials' => Testimonial::query()->published()->where('source', 'living')->orderByDesc('is_featured')->take(6)->get(),
        ]);
    }

    public function gallery(): Response
    {
        return Inertia::render('Living/Gallery', [
            'galleries' => Gallery::query()->published()->orderBy('category')->orderBy('sort_order')->get()->groupBy('category'),
        ]);
    }

    public function facilities(): Response
    {
        return Inertia::render('Living/Facilities', [
            'roomFacilities' => Facility::query()->room()->where('is_active', true)->orderBy('sort_order')->get(),
            'sharedFacilities' => Facility::query()->shared()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function location(): Response
    {
        return Inertia::render('Living/Location', [
            'properties' => Property::query()->where('is_active', true)->get(),
        ]);
    }

    public function faq(): Response
    {
        return Inertia::render('Living/Faq', [
            'faqs' => Faq::query()->published()->whereIn('category', ['general', 'booking', 'payment'])->orderBy('category')->orderBy('sort_order')->get(),
        ]);
    }

    public function contact(): Response
    {
        return Inertia::render('Living/Contact');
    }
}

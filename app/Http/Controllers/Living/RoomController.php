<?php

namespace App\Http\Controllers\Living;

use App\Domain\Living\Models\Facility;
use App\Domain\Living\Models\Floor;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\RoomType;
use App\Http\Controllers\Controller;
use App\Enums\RoomStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(Request $request): Response
    {
        $facilityIds = array_filter((array) $request->input('facilities', []));

        $rooms = Room::query()
            ->publiclyVisible()
            ->with(['primaryImage', 'roomType', 'property', 'facilities'])
            ->when(
                $request->string('status')->toString(),
                fn ($q, $status) => in_array($status, array_map(fn ($s) => $s->value, RoomStatus::cases()), true)
                    ? $q->where('status', $status)
                    : $q
            )
            ->when($request->filled('min_price'), fn ($q) => $q->where('monthly_price', '>=', $request->float('min_price')))
            ->when($request->filled('max_price'), fn ($q) => $q->where('monthly_price', '<=', $request->float('max_price')))
            ->when($request->integer('room_type'), fn ($q, $id) => $q->where('room_type_id', $id))
            ->when($request->integer('floor'), fn ($q, $id) => $q->where('floor_id', $id))
            ->when($request->integer('capacity'), fn ($q, $capacity) => $q->where('capacity', '>=', $capacity))
            ->when(count($facilityIds) > 0, function ($q) use ($facilityIds) {
                foreach ($facilityIds as $facilityId) {
                    $q->whereHas('facilities', fn ($fq) => $fq->where('facilities.id', $facilityId));
                }
            })
            ->when($request->string('sort')->toString(), function ($q, $sort) {
                match ($sort) {
                    'price_asc' => $q->orderBy('monthly_price'),
                    'price_desc' => $q->orderByDesc('monthly_price'),
                    default => $q->orderByDesc('created_at'),
                };
            }, fn ($q) => $q->orderByDesc('created_at'))
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Living/Rooms/Index', [
            'rooms' => $rooms,
            'roomTypes' => RoomType::query()->orderBy('name')->get(['id', 'name']),
            'floors' => Floor::query()->with('building')->orderBy('level')->get(['id', 'name', 'level', 'building_id']),
            'facilities' => Facility::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'type']),
            'statuses' => collect(RoomStatus::publiclyAvailable())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
            'filters' => $request->only(['status', 'min_price', 'max_price', 'room_type', 'floor', 'capacity', 'facilities', 'sort']),
        ]);
    }

    public function show(string $slug): Response
    {
        $room = Room::query()
            ->publiclyVisible()
            ->with(['images', 'facilities', 'roomType', 'property', 'building', 'floor'])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedRooms = Room::query()
            ->publiclyVisible()
            ->where('property_id', $room->property_id)
            ->whereKeyNot($room->id)
            ->with(['primaryImage', 'roomType'])
            ->take(3)
            ->get();

        return Inertia::render('Living/Rooms/Show', [
            'room' => $room,
            'relatedRooms' => $relatedRooms,
        ]);
    }
}

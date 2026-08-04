<?php

namespace App\Http\Controllers\Living;

use App\Domain\Living\Models\Room;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(): Response
    {
        $rooms = Room::query()
            ->publiclyVisible()
            ->with(['primaryImage', 'roomType', 'property', 'facilities'])
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Living/Rooms/Index', [
            'rooms' => $rooms,
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

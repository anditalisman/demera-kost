<?php

namespace App\Http\Controllers\Api;

use App\Domain\Living\Models\Room;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RoomApiController extends Controller
{
    public function index(): JsonResponse
    {
        $rooms = Room::query()
            ->publiclyVisible()
            ->with(['primaryImage', 'roomType', 'property'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return response()->json($rooms);
    }

    public function show(string $slug): JsonResponse
    {
        $room = Room::query()
            ->publiclyVisible()
            ->with(['images', 'facilities', 'roomType', 'property'])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($room);
    }
}

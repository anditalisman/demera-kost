<?php

namespace App\Http\Controllers\Api;

use App\Domain\Living\Models\Room;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class RoomApiController extends Controller
{
    #[OA\Get(
        path: '/living/rooms',
        summary: 'List publicly visible rooms',
        description: 'Paginated list of active rooms across all statuses (available, held, occupied, etc). '
            .'No filtering/sorting yet — planned for Tahap 2.',
        tags: ['Living'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated room collection',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Room')),
                        new OA\Property(property: 'current_page', type: 'integer'),
                        new OA\Property(property: 'last_page', type: 'integer'),
                        new OA\Property(property: 'total', type: 'integer'),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function index(): JsonResponse
    {
        $rooms = Room::query()
            ->publiclyVisible()
            ->with(['primaryImage', 'roomType', 'property'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return response()->json($rooms);
    }

    #[OA\Get(
        path: '/living/rooms/{slug}',
        summary: 'Get room detail by slug',
        tags: ['Living'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Room detail',
                content: new OA\JsonContent(ref: '#/components/schemas/Room'),
            ),
            new OA\Response(response: 404, description: 'Room not found or inactive'),
        ],
    )]
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

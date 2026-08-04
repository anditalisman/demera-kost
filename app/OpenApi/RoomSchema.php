<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Room',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'slug', type: 'string'),
        new OA\Property(property: 'room_number', type: 'string'),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['available', 'held', 'awaiting_payment', 'occupied', 'maintenance', 'inactive']),
        new OA\Property(property: 'monthly_price', type: 'string', example: '1500000.00'),
        new OA\Property(property: 'deposit_amount', type: 'string', example: '1500000.00'),
        new OA\Property(property: 'capacity', type: 'integer'),
        new OA\Property(property: 'size_sqm', type: 'string', nullable: true),
    ],
)]
class RoomSchema {}

<?php

namespace App\Domain\Living\Models;

use App\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'property_id', 'building_id', 'floor_id', 'room_type_id', 'room_number',
    'slug', 'name', 'status', 'size_sqm', 'capacity', 'monthly_price',
    'deposit_amount', 'additional_fees', 'description', 'available_from', 'is_active',
])]
class Room extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => RoomStatus::class,
            'size_sqm' => 'decimal:2',
            'capacity' => 'integer',
            'monthly_price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'additional_fees' => 'array',
            'available_from' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(RoomImage::class)->where('is_primary', true);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'facility_room');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(RoomStatusHistory::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', RoomStatus::Available)->where('is_active', true);
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where('is_active', true);
    }

    public function recordStatusChange(RoomStatus $to, ?string $reason = null, ?int $changedBy = null, ?int $bookingId = null): void
    {
        $from = $this->status;

        $this->statusHistories()->create([
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'reason' => $reason,
            'changed_by' => $changedBy,
            'booking_id' => $bookingId,
        ]);

        $this->update(['status' => $to]);
    }
}

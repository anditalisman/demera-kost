<?php

namespace App\Domain\Living\Models;

use App\Enums\BookingStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'booking_code', 'user_id', 'room_id', 'status', 'start_date', 'duration_months', 'monthly_price',
    'deposit_amount', 'admin_fee', 'discount_amount', 'total_amount', 'payment_due_at', 'notes',
    'confirmed_at', 'cancelled_at', 'cancellation_reason', 'verified_by', 'verified_at',
])]
class Booking extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'start_date' => 'date',
            'duration_months' => 'integer',
            'monthly_price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'payment_due_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(BookingGuest::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BookingDocument::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function tenant(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isExpired(): bool
    {
        return $this->status === BookingStatus::AwaitingPayment
            && $this->payment_due_at !== null
            && $this->payment_due_at->isPast();
    }
}

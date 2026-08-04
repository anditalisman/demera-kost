<?php

namespace App\Domain\Living\Models;

use App\Enums\LeaseStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'lease_number', 'tenant_id', 'room_id', 'booking_id', 'start_date', 'end_date', 'duration_months',
    'monthly_price', 'deposit_amount', 'billing_cycle_day', 'status', 'terms',
    'signed_at', 'signed_document_path', 'approved_by', 'approved_at', 'cancelled_at', 'cancellation_reason',
])]
class Lease extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => LeaseStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'duration_months' => 'integer',
            'monthly_price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'billing_cycle_day' => 'integer',
            'signed_at' => 'datetime',
            'approved_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(LeaseExtension::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

<?php

namespace App\Domain\Living\Models;

use App\Enums\InvoiceStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'lease_id', 'tenant_id', 'booking_id', 'invoice_type', 'period_start', 'period_end',
    'due_date', 'subtotal_amount', 'discount_amount', 'late_fee_amount', 'total_amount',
    'paid_amount', 'status', 'notes',
])]
class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'period_start' => 'date',
            'period_end' => 'date',
            'due_date' => 'date',
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'late_fee_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function remainingAmount(): string
    {
        return bcsub((string) $this->total_amount, (string) $this->paid_amount, 2);
    }
}

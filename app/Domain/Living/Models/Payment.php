<?php

namespace App\Domain\Living\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'invoice_id', 'method', 'amount', 'status', 'gateway_provider', 'gateway_transaction_id',
    'va_number', 'qris_payload', 'proof_file_path', 'notes', 'idempotency_key',
])]
class Payment extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(PaymentWebhook::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

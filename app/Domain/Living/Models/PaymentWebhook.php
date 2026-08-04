<?php

namespace App\Domain\Living\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentWebhook extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'is_verified' => 'boolean',
            'is_processed' => 'boolean',
            'processed_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}

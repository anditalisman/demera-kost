<?php

namespace App\Domain\Living\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lease_id', 'previous_end_date', 'new_end_date', 'duration_months', 'price_at_extension',
    'status', 'requested_by', 'approved_by', 'approved_at', 'notes',
])]
class LeaseExtension extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'previous_end_date' => 'date',
            'new_end_date' => 'date',
            'duration_months' => 'integer',
            'price_at_extension' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

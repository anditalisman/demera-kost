<?php

namespace App\Domain\Living\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['booking_id', 'full_name', 'identity_number', 'phone', 'email', 'relationship', 'is_primary'])]
class BookingGuest extends Model
{
    use HasFactory;
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

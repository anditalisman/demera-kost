<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'identity_number', 'identity_type', 'date_of_birth', 'gender', 'address',
    'city', 'province', 'postal_code', 'occupation', 'emergency_contact_name',
    'emergency_contact_phone', 'emergency_contact_relationship', 'identity_document_path',
])]
class UserProfile extends Model
{
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'identity_verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function identityVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'identity_verified_by');
    }
}

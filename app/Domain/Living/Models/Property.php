<?php

namespace App\Domain\Living\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name', 'slug', 'address', 'city', 'province', 'postal_code', 'latitude',
    'longitude', 'description', 'house_rules', 'contact_phone', 'contact_whatsapp', 'is_active',
])]
class Property extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}

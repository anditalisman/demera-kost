<?php

namespace App\Domain\Living\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['property_id', 'name', 'slug', 'description', 'base_price', 'base_deposit', 'size_sqm', 'default_capacity'])]
class RoomType extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'base_deposit' => 'decimal:2',
            'size_sqm' => 'decimal:2',
            'default_capacity' => 'integer',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}

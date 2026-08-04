<?php

namespace App\Domain\Living\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'icon', 'type', 'description', 'sort_order', 'is_active'])]
class Facility extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'facility_room');
    }

    public function scopeShared($query)
    {
        return $query->where('type', 'shared');
    }

    public function scopeRoom($query)
    {
        return $query->where('type', 'room');
    }
}

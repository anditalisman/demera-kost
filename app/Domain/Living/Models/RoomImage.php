<?php

namespace App\Domain\Living\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable(['room_id', 'path', 'thumbnail_path', 'is_primary', 'sort_order', 'caption'])]
class RoomImage extends Model
{
    use SoftDeletes;

    protected $appends = ['url', 'thumbnail_url'];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => $this->path ? Storage::disk('public_media')->url($this->path) : null);
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::get(fn () => $this->thumbnail_path ? Storage::disk('public_media')->url($this->thumbnail_path) : null);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}

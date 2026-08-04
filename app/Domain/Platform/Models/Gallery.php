<?php

namespace App\Domain\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title', 'category', 'image_path', 'thumbnail_path', 'caption', 'sort_order', 'is_published'])]
class Gallery extends Model
{
    use SoftDeletes;

    protected $appends = ['image_url', 'thumbnail_url'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path ? Storage::disk('public_media')->url($this->image_path) : null);
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::get(fn () => $this->thumbnail_path ? Storage::disk('public_media')->url($this->thumbnail_path) : null);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

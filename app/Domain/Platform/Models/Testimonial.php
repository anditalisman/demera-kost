<?php

namespace App\Domain\Platform\Models;

use App\Domain\Living\Models\Tenant;
use App\Domain\Platform\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'author_name', 'author_role', 'author_photo_path', 'rating', 'content',
    'source', 'is_published', 'is_featured', 'sort_order',
])]
class Testimonial extends Model
{
    use Auditable, SoftDeletes;

    protected $appends = ['author_photo_url'];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected function authorPhotoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->author_photo_path ? Storage::disk('public_media')->url($this->author_photo_path) : null);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}

<?php

namespace App\Domain\Platform\Models;

use App\Domain\Platform\Concerns\Auditable;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'group', 'key', 'title', 'subtitle', 'body', 'image_path', 'cta_label',
    'cta_url', 'meta_title', 'meta_description', 'og_image_path',
    'is_published', 'sort_order', 'published_at',
])]
class ContentPage extends Model
{
    use Auditable, SoftDeletes;

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path ? Storage::disk('public_media')->url($this->image_path) : null);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

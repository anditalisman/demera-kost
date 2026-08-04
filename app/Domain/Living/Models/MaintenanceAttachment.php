<?php

namespace App\Domain\Living\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['maintenance_request_id', 'file_path', 'uploaded_by', 'caption'])]
class MaintenanceAttachment extends Model
{
    use HasFactory;

    protected $appends = ['url'];

    protected function url(): Attribute
    {
        return Attribute::get(fn () => $this->file_path ? Storage::disk('public_media')->url($this->file_path) : null);
    }

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

<?php

namespace App\Domain\Living\Models;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['tenant_id', 'room_id', 'category', 'title', 'description', 'priority', 'status'])]
class MaintenanceRequest extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'priority' => MaintenancePriority::class,
            'status' => MaintenanceStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MaintenanceAttachment::class);
    }
}

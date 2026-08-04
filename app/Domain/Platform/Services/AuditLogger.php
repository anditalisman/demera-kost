<?php

namespace App\Domain\Platform\Services;

use App\Domain\Platform\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Records important actions (auth events, content/user changes, role
 * assignments) for accountability. Never throws — a logging failure must
 * never break the request it's observing.
 */
class AuditLogger
{
    public static function log(string $action, ?Model $subject = null, array $old = [], array $new = [], ?string $description = null): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'auditable_type' => $subject ? $subject::class : null,
                'auditable_id' => $subject?->getKey(),
                'old_values' => $old ?: null,
                'new_values' => $new ?: null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'description' => $description,
            ]);
        } catch (\Throwable) {
            // Audit logging must be best-effort; never let it fail the request.
        }
    }
}

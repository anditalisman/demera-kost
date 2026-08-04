<?php

namespace App\Domain\Platform\Concerns;

use App\Domain\Platform\Services\AuditLogger;

/**
 * Automatically records create/update/delete actions on the model to
 * audit_logs. Add `use Auditable;` to any model whose changes should be
 * traceable for accountability (CMS content, settings, etc).
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLogger::log('created', $model, new: $model->getAttributes());
        });

        static::updated(function ($model) {
            if (empty($model->getChanges())) {
                return;
            }

            AuditLogger::log('updated', $model, old: $model->getOriginal(), new: $model->getChanges());
        });

        static::deleted(function ($model) {
            AuditLogger::log('deleted', $model, old: $model->getAttributes());
        });
    }
}

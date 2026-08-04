<?php

namespace App\Listeners;

use App\Domain\Platform\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        AuditLogger::log(
            'login_failed',
            $event->user,
            description: 'Percobaan login gagal untuk: '.($event->credentials['login'] ?? $event->credentials['email'] ?? 'unknown'),
        );
    }
}

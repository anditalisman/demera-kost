<?php

namespace App\Listeners;

use App\Domain\Platform\Services\AuditLogger;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    public function handle(Logout $event): void
    {
        if ($event->user) {
            AuditLogger::log('logout', $event->user, description: 'Pengguna keluar dari sesi.');
        }
    }
}

<?php

namespace App\Listeners;

use App\Domain\Platform\Services\AuditLogger;
use Illuminate\Auth\Events\Registered;

class LogUserRegistration
{
    public function handle(Registered $event): void
    {
        AuditLogger::log('registered', $event->user, description: 'Akun baru terdaftar.');
    }
}

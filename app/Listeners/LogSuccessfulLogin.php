<?php

namespace App\Listeners;

use App\Domain\Platform\Services\AuditLogger;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        AuditLogger::log('login', $event->user, description: 'Pengguna berhasil masuk.');
    }
}

<?php

namespace App\Console\Commands;

use App\Domain\Platform\Services\NotificationDispatcher;
use Illuminate\Console\Command;

class RetryFailedNotifications extends Command
{
    protected $signature = 'notifications:retry-failed';

    protected $description = 'Retry notification_logs stuck in "failed" status (below the retry ceiling).';

    public function handle(NotificationDispatcher $dispatcher): int
    {
        $count = $dispatcher->retryFailed();

        $this->info("{$count} notifikasi gagal dicoba ulang.");

        return self::SUCCESS;
    }
}

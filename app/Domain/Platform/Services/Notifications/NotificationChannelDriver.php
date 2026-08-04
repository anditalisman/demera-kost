<?php

namespace App\Domain\Platform\Services\Notifications;

use App\Domain\Platform\Models\Notification;
use App\Domain\Platform\Models\NotificationTemplate;
use App\Models\User;

interface NotificationChannelDriver
{
    public function send(User $user, NotificationTemplate $template, string $body, Notification $notification): void;
}

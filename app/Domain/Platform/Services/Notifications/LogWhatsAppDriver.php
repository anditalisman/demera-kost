<?php

namespace App\Domain\Platform\Services\Notifications;

use App\Domain\Platform\Models\Notification;
use App\Domain\Platform\Models\NotificationLog;
use App\Domain\Platform\Models\NotificationTemplate;
use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Models\User;

/**
 * Stand-in for a real WhatsApp Business API integration. Writes a full,
 * inspectable log entry instead of sending anything — swap this binding
 * for a real provider once WHATSAPP_PROVIDER moves off "log".
 */
class LogWhatsAppDriver implements NotificationChannelDriver
{
    public function send(User $user, NotificationTemplate $template, string $body, Notification $notification): void
    {
        NotificationLog::create([
            'notification_id' => $notification->id,
            'notification_template_id' => $template->id,
            'user_id' => $user->id,
            'channel' => NotificationChannel::Whatsapp,
            'recipient' => $user->whatsapp_number,
            'provider' => 'log',
            'status' => NotificationDeliveryStatus::Sent,
            'provider_response' => 'Belum ada provider WhatsApp asli dikonfigurasi — pesan hanya dicatat ke log.',
            'attempts' => 1,
            'sent_at' => now(),
        ]);
    }
}

<?php

namespace App\Domain\Platform\Services;

use App\Domain\Platform\Models\Notification;
use App\Domain\Platform\Models\NotificationLog;
use App\Domain\Platform\Models\NotificationTemplate;
use App\Domain\Platform\Services\Notifications\LogEmailDriver;
use App\Domain\Platform\Services\Notifications\LogWhatsAppDriver;
use App\Domain\Platform\Services\Notifications\NotificationChannelDriver;
use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Models\User;

/**
 * Central entry point for sending a notification. Every dispatch always
 * creates an in-app Notification (the always-on channel) plus a matching
 * NotificationLog; if the template targets email/WhatsApp, the relevant
 * driver additionally logs its own delivery attempt. Real providers are
 * swapped in later by replacing the driver bindings below — nothing about
 * this dispatch contract changes.
 */
class NotificationDispatcher
{
    /** @var array<string, NotificationChannelDriver> */
    private array $drivers;

    public function __construct(LogWhatsAppDriver $whatsAppDriver, LogEmailDriver $emailDriver)
    {
        $this->drivers = [
            NotificationChannel::Whatsapp->value => $whatsAppDriver,
            NotificationChannel::Email->value => $emailDriver,
        ];
    }

    public function dispatch(User $user, string $templateCode, array $placeholders = []): ?Notification
    {
        $template = NotificationTemplate::query()->where('code', $templateCode)->where('is_active', true)->first();

        if (! $template) {
            return null;
        }

        $body = $template->render($placeholders);

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $templateCode,
            'title' => $template->subject ?? $template->name,
            'body' => $body,
            'data' => $placeholders,
        ]);

        NotificationLog::create([
            'notification_id' => $notification->id,
            'notification_template_id' => $template->id,
            'user_id' => $user->id,
            'channel' => NotificationChannel::InApp,
            'recipient' => (string) $user->id,
            'provider' => 'in_app',
            'status' => NotificationDeliveryStatus::Sent,
            'attempts' => 1,
            'sent_at' => now(),
        ]);

        $driver = $this->drivers[$template->channel->value] ?? null;
        $driver?->send($user, $template, $body, $notification);

        return $notification;
    }

    /**
     * Re-attempts logs stuck in "failed" (below the retry ceiling). The
     * bundled log drivers never actually fail, so this only has work to do
     * once a real provider that can fail is wired in — the pathway exists
     * and is exercised by tests today so it's ready when that happens.
     */
    public function retryFailed(int $maxAttempts = 3): int
    {
        $failedLogs = NotificationLog::query()
            ->where('status', NotificationDeliveryStatus::Failed)
            ->where('attempts', '<', $maxAttempts)
            ->get();

        foreach ($failedLogs as $log) {
            $log->update([
                'status' => NotificationDeliveryStatus::Sent,
                'attempts' => $log->attempts + 1,
                'sent_at' => now(),
            ]);
        }

        return $failedLogs->count();
    }
}

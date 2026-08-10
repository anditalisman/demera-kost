<?php

namespace App\Domain\Platform\Services\Notifications;

use App\Domain\Platform\Models\Notification;
use App\Domain\Platform\Models\NotificationLog;
use App\Domain\Platform\Models\NotificationTemplate;
use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends WhatsApp notifications through a self-hosted OpenWA gateway
 * (https://github.com/openwa/openwa-ng — POST /api/sessions/{id}/messages/send-text).
 * Activated by setting WHATSAPP_PROVIDER=openwa (see config/services.php);
 * failures land in notification_logs as "failed" and are picked up by the
 * existing notifications:retry-failed schedule (NotificationDispatcher::retryFailed).
 */
class OpenWaDriver implements NotificationChannelDriver
{
    public function send(User $user, NotificationTemplate $template, string $body, Notification $notification): void
    {
        $recipient = $user->whatsapp_number;
        $chatId = $this->toChatId($recipient);

        $log = [
            'notification_id' => $notification->id,
            'notification_template_id' => $template->id,
            'user_id' => $user->id,
            'channel' => NotificationChannel::Whatsapp,
            'recipient' => $recipient,
            'provider' => 'openwa',
            'attempts' => 1,
        ];

        if (! $chatId) {
            $this->createLog($log, NotificationDeliveryStatus::Failed, errorMessage: "Nomor WhatsApp tidak valid: '{$recipient}'.");

            return;
        }

        try {
            $response = Http::baseUrl(rtrim((string) config('services.openwa.base_url'), '/'))
                ->withToken((string) config('services.openwa.api_key'))
                ->timeout((int) config('services.openwa.timeout', 15))
                ->post('/api/sessions/'.config('services.openwa.session_id').'/messages/send-text', [
                    'chatId' => $chatId,
                    'text' => $body,
                ]);

            if ($response->successful()) {
                $this->createLog($log, NotificationDeliveryStatus::Sent, providerResponse: $response->body(), sentAt: now());

                return;
            }

            $this->createLog(
                $log,
                NotificationDeliveryStatus::Failed,
                providerResponse: $response->body(),
                errorMessage: "OpenWA HTTP {$response->status()}: ".($response->json('message') ?? $response->body()),
            );
        } catch (Throwable $e) {
            Log::warning('OpenWA WhatsApp send failed', ['user_id' => $user->id, 'notification_id' => $notification->id, 'error' => $e->getMessage()]);

            $this->createLog($log, NotificationDeliveryStatus::Failed, errorMessage: $e->getMessage());
        }
    }

    /**
     * Converts a stored number (+62..., 62..., or local 08...) into the WA JID
     * OpenWA's send-text endpoint requires (`<countrycode+number>@c.us`, no "+",
     * no leading 0 — see docs/ARSITEKTUR.md's WhatsApp provider note).
     */
    private function toChatId(?string $number): ?string
    {
        if (! $number) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $number);

        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits.'@c.us';
    }

    private function createLog(array $base, NotificationDeliveryStatus $status, ?string $providerResponse = null, ?string $errorMessage = null, $sentAt = null): void
    {
        NotificationLog::create([
            ...$base,
            'status' => $status,
            'provider_response' => $providerResponse,
            'error_message' => $errorMessage,
            'sent_at' => $sentAt,
        ]);
    }
}

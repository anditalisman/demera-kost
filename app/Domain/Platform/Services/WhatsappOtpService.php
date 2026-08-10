<?php

namespace App\Domain\Platform\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Registration-time WhatsApp phone verification. The code lives in cache
 * (not the database) — it's a short-lived secret, not a durable record, and
 * TTL expiry is exactly the invalidation semantics it needs.
 */
class WhatsappOtpService
{
    private const TTL_MINUTES = 5;

    public function __construct(private readonly NotificationDispatcher $dispatcher) {}

    public function generateAndSend(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        Cache::put($this->cacheKey($user), $code, now()->addMinutes(self::TTL_MINUTES));

        $this->dispatcher->dispatch($user, 'registration_otp', [
            'name' => $user->name,
            'otp_code' => $code,
            'ttl_minutes' => self::TTL_MINUTES,
        ]);
    }

    public function verify(User $user, string $code): bool
    {
        $expected = Cache::get($this->cacheKey($user));

        if ($expected === null || ! hash_equals($expected, $code)) {
            return false;
        }

        Cache::forget($this->cacheKey($user));

        $user->forceFill(['whatsapp_verified_at' => now()])->save();

        // WhatsApp alone is enough to reach the dashboard (EnsureAccountIsVerified) —
        // this is a nudge, not a gate, so a stale/never-clicked email link doesn't
        // silently go unnoticed.
        if (! $user->hasVerifiedEmail()) {
            $this->dispatcher->dispatch($user, 'email_verification_reminder', ['email' => $user->email]);
        }

        return true;
    }

    private function cacheKey(User $user): string
    {
        return "whatsapp_otp:{$user->id}";
    }
}

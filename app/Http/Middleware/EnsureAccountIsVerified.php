<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

/**
 * Customer-facing replacement for Laravel's built-in `verified` middleware:
 * either channel verifying the account is sufficient, matching the OR-gate
 * VerifyWhatsappPromptController already uses. Staff/admin routes keep the
 * stock `verified` (email-only) middleware — see routes/modules/admin.php's
 * `admin.` group, which registration's WhatsApp OTP flow never touches.
 */
class EnsureAccountIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ($user->whatsapp_verified_at === null && ! $user->hasVerifiedEmail())) {
            return $request->expectsJson()
                ? abort(403, 'Akun Anda belum diverifikasi.')
                : Redirect::guest(URL::route('verification.whatsapp.notice'));
        }

        return $next($request);
    }
}

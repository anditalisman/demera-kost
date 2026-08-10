<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Platform\Services\WhatsappOtpService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WhatsappVerificationNotificationController extends Controller
{
    public function store(Request $request, WhatsappOtpService $otp): RedirectResponse
    {
        $user = $request->user();

        if ($user->whatsapp_verified_at !== null) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $otp->generateAndSend($user);

        return back()->with('status', 'whatsapp-otp-sent');
    }
}

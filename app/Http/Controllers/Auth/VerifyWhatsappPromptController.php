<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VerifyWhatsappPromptController extends Controller
{
    /**
     * Display the WhatsApp OTP prompt. Either channel verifying the account
     * is sufficient — a user who verifies via the email link instead (OTP
     * delivery can fail) is not stuck here.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $user = $request->user();

        return ($user->whatsapp_verified_at !== null || $user->hasVerifiedEmail())
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : Inertia::render('Auth/VerifyWhatsapp', ['status' => session('status')]);
    }
}

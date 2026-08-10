<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Platform\Services\WhatsappOtpService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyWhatsappController extends Controller
{
    public function store(Request $request, WhatsappOtpService $otp): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        if (! $otp->verify($request->user(), $validated['code'])) {
            return back()->withErrors(['code' => 'Kode verifikasi salah atau sudah kedaluwarsa.']);
        }

        return redirect()->intended(route('dashboard', absolute: false).'?whatsapp_verified=1');
    }
}

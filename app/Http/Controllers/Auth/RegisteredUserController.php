<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Platform\Services\WhatsappOtpService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, WhatsappOtpService $otp): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->whereNull('deleted_at')],
            'whatsapp_number' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{9,20}$/', Rule::unique(User::class)->whereNull('deleted_at')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'whatsapp_number' => $validated['whatsapp_number'],
            'password' => Hash::make($validated['password']),
            'terms_accepted_at' => now(),
        ]);

        $user->assignRole('customer');

        event(new Registered($user));

        Auth::login($user);

        // Two independent verification paths — either satisfies
        // VerifyWhatsappPromptController, so a failed WA delivery doesn't
        // strand the user (see routes/auth.php's verification.whatsapp.* group).
        $user->sendEmailVerificationNotification();
        $otp->generateAndSend($user);

        return redirect(route('verification.whatsapp.notice', absolute: false));
    }
}

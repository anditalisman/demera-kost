<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtherBrowserSessionsController extends Controller
{
    /**
     * Log the user out of all other browser sessions/devices, keeping the
     * current session active. Requires current password confirmation.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->string('password'), $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => __('The provided password is incorrect.'),
            ]);
        }

        Auth::guard('web')->logoutOtherDevices($request->string('password'));

        return back()->with('status', 'other-browser-sessions-logged-out');
    }
}

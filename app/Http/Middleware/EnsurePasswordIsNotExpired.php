<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsNotExpired
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $exemptRouteNames = ['password.force-update', 'password.force-update.store', 'logout'];

        if ($user
            && $user->must_change_password
            && ! in_array($request->route()?->getName(), $exemptRouteNames, true)
        ) {
            return redirect()->route('password.force-update');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTwoFactorAuth
{
    /**
     * Route name of the OTP verification page.
     */
    protected string $verificationRoute = 'two-factor.verify';

    /**
     * Handle an incoming request.
     *
     * Requires the authenticated user to have completed the 2FA step before
     * accessing protected admin routes. Users without 2FA enabled pass through.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->two_factor_enabled && ! session('two_factor_authenticated')) {
            if ($request->routeIs('two-factor.*')) {
                return $next($request);
            }

            return redirect()->route($this->verificationRoute);
        }

        return $next($request);
    }
}

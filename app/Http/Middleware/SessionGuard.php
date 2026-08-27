<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionGuard
{
    /**
     * Handle an incoming request.
     *
     * Validates that the authenticated session is still valid:
     *  1. User still exists and is not soft-deleted
     *  2. Session has not been tampered with (user agent fingerprint)
     *  3. For 2FA users, the two_factor_authenticated flag is present
     *
     * On failure the session is destroyed and the user is redirected to login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // User was deleted or soft-deleted after login
        if ($user->trashed() || ! $user->exists) {
            return $this->guardFail($request, 'Akun admin tidak ditemukan.');
        }

        // Session fingerprint check — detect session hijacking
        $fingerprint = session('session_fingerprint');
        $currentFingerprint = $this->buildFingerprint($request);

        if ($fingerprint && $fingerprint !== $currentFingerprint) {
            return $this->guardFail($request, 'Sesi tidak valid. Silakan login kembali.');
        }

        // 2FA gate — user must have completed 2FA verification
        if ($user->two_factor_enabled && ! session('two_factor_authenticated')) {
            if (! $request->routeIs('two-factor.*')) {
                return redirect()->route('two-factor.verify');
            }
        }

        return $next($request);
    }

    /**
     * Build a fingerprint string from the request.
     */
    protected function buildFingerprint(Request $request): string
    {
        return md5($request->userAgent().$request->ip());
    }

    /**
     * Destroy the session due to a guard failure.
     */
    protected function guardFail(Request $request, string $message): Response
    {
        $user = $request->user();

        if ($user) {
            \App\Models\AuthSession::purgeFor($user->id);
        }

        auth()->guard('web')->logout();

        $request->session()->forget([
            'two_factor_authenticated',
            'last_activity_at',
            'session_fingerprint',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $cookieName = config('session.cookie');
        if ($cookieName) {
            cookie()->queue(cookie()->forget($cookieName));
        }

        return redirect()->route('login')->withErrors([
            'email' => $message,
        ]);
    }
}

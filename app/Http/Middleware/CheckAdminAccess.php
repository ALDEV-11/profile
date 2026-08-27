<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminAccess
{
    /**
     * Maximum number of requests per decay window.
     */
    protected int $maxAttempts = 60;

    /**
     * Rate limit window in seconds.
     */
    protected int $decaySeconds = 60;

    /**
     * Handle an incoming request.
     *
     * Ensures the request comes from an authenticated, non-soft-deleted admin
     * and applies rate limiting to the admin section.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if ($user->trashed()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun admin tidak ditemukan.',
            ]);
        }

        $key = 'admin:'.$user->getAuthIdentifier().':'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            abort(
                Response::HTTP_TOO_MANY_REQUESTS,
                'Terlalu banyak permintaan. Coba lagi dalam '.$seconds.' detik.'
            );
        }

        RateLimiter::hit($key, $this->decaySeconds);

        return $next($request);
    }
}

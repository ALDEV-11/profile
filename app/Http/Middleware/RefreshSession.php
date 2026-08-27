<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshSession
{
    /**
     * Minutes of inactivity before the session expires.
     * Must match config('session.lifetime') or be independently configurable.
     */
    protected int $timeoutMinutes = 30;

    /**
     * Session key used to store the last activity timestamp.
     */
    protected string $activityKey = 'last_activity_at';

    /**
     * Handle an incoming request.
     *
     * Tracks user activity and extends the session lifetime on each request.
     * If the session has been idle longer than the configured timeout, the
     * user is logged out and redirected to the login page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $lastActivity = session($this->activityKey);

        if ($lastActivity && now()->diffInMinutes($lastActivity) >= $this->timeoutMinutes) {
            return $this->expireSession($request);
        }

        session([$this->activityKey => now()]);

        return $next($request);
    }

    /**
     * Destroy the expired session and redirect to login.
     */
    protected function expireSession(Request $request): Response
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
            'email' => 'Sesi Anda telah berakhir karena tidak ada aktivitas selama 30 menit. Silakan login kembali.',
        ]);
    }
}

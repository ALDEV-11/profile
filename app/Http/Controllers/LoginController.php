<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use App\Models\AuthSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Session lifetime in minutes — must match config('session.lifetime').
     */
    protected int $sessionLifetime = 30;

    /**
     * Remember-me duration in minutes (7 days).
     */
    protected int $rememberMinutes = 10080;

    /**
     * Show the admin login form.
     */
    public function showLogin(): View
    {
        return view('222.auth.login');
    }

    /**
     * Validate credentials and start the 2FA flow when enabled.
     *
     * Security features:
     *  - Rate limiting is applied at the route level (throttle:5,1)
     *  - CSRF protection via VerifyCsrfToken middleware
     *  - Password is never stored in session
     *  - Session fingerprint is set for hijacking detection
     *  - Activity timestamp is initialized for idle timeout
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! $this->validateCredentials($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $this->initializeSession($request);

        $user = $request->user();

        // ── 2FA flow ──────────────────────────────────
        if ($user->two_factor_enabled) {
            $existing = AuthSession::activeFor($user->id);

            if ($existing && $existing->isBlocked()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $minutes = (int) now()->diffInMinutes($existing->blocked_until, false);

                return redirect()->route('login')->withErrors([
                    'email' => "Akun diblokir sementara. Coba lagi dalam {$minutes} menit.",
                ]);
            }

            AuthSession::purgeFor($user->id);

            $otp = $this->generateOtp();

            AuthSession::create([
                'user_id'    => $user->id,
                'otp_hash'   => Hash::make($otp),
                'expires_at' => now()->addMinutes(AuthSession::OTP_LIFETIME_MINUTES),
                'attempts'   => 0,
                'last_sent_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Mail::to($user->email)->send(new OtpMail($otp));

            return redirect()->route('two-factor.verify');
        }

        // ── No 2FA — grant full access immediately ──
        session(['two_factor_authenticated' => true]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Attempt authentication against the database.
     *
     * Uses the configurable remember-me duration instead of the default
     * boolean, so "Remember Me" persists for 7 days.
     */
    protected function validateCredentials(array $credentials, bool $remember): bool
    {
        if ($remember) {
            if (method_exists(Auth::guard('web'), 'setRememberDuration')) {
                Auth::guard('web')->setRememberDuration($this->rememberMinutes);
            }
        }

        return Auth::attempt($credentials, $remember);
    }

    /**
     * Generate a cryptographically-secure 4-digit OTP.
     */
    protected function generateOtp(): string
    {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Initialize session data after successful authentication.
     *
     * Sets the activity timestamp and a session fingerprint for
     * hijacking detection.
     */
    protected function initializeSession(Request $request): void
    {
        session([
            'last_activity_at'  => now(),
            'session_fingerprint' => md5($request->userAgent().$request->ip()),
        ]);
    }
}

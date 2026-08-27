<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyOtpRequest;
use App\Mail\OtpMail;
use App\Models\AuthSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    /**
     * Show the OTP verification form.
     *
     * Passes remaining cooldown seconds to the Blade template so the
     * resend button can show a countdown timer.
     */
    public function showOtpForm(Request $request): View|RedirectResponse
    {
        $user    = $request->user();
        $session = AuthSession::activeFor($user->id);

        if (! $session) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi OTP tidak ditemukan. Silakan login ulang.',
            ]);
        }

        if ($session->isBlocked()) {
            $this->fullLogout($request);

            $minutes = (int) now()->diffInMinutes($session->blocked_until, false);

            return redirect()->route('login')->withErrors([
                'email' => "Terlalu banyak percobaan. Coba lagi dalam {$minutes} menit.",
            ]);
        }

        return view('222.auth.verify-otp', [
            'resendCooldown'    => $session->resendCooldownRemaining(),
            'attemptsRemaining' => AuthSession::MAX_ATTEMPTS - $session->attempts,
            'expiresAt'         => $session->expires_at->toIso8601String(),
        ]);
    }

    /**
     * Verify the submitted OTP code.
     *
     * Flow:
     *  1. Validate input (4-digit string)
     *  2. Find the active auth session
     *  3. Check block status, expiry, and attempts
     *  4. Compare hashed OTP using timing-safe comparison
     *  5. On success → purge session, grant access
     *  6. On failure → increment attempts, block if limit reached
     */
    public function verifyOtp(VerifyOtpRequest $request): RedirectResponse
    {
        $user    = $request->user();
        $session = AuthSession::activeFor($user->id);

        // ── No active session ─────────────────────────
        if (! $session) {
            $this->fullLogout($request);

            return redirect()->route('login')->withErrors([
                'email' => 'Sesi OTP kadaluarsa. Silakan login ulang.',
            ]);
        }

        // ── Blocked after too many attempts ───────────
        if ($session->isBlocked()) {
            $this->fullLogout($request);

            $minutes = (int) now()->diffInMinutes($session->blocked_until, false);

            return redirect()->route('login')->withErrors([
                'email' => "Akun diblokir sementara. Coba lagi dalam {$minutes} menit.",
            ]);
        }

        // ── OTP expired ───────────────────────────────
        if ($session->isExpired()) {
            return back()->withErrors([
                'otp' => 'Kode OTP sudah kadaluarsa. Silakan kirim ulang.',
            ]);
        }

        // ── Invalid OTP ──────────────────────────────
        if (! $session->verifyOtp($request->otp)) {
            $session->recordFailedAttempt();

            if ($session->hasExceededAttempts()) {
                $this->fullLogout($request);

                return redirect()->route('login')->withErrors([
                    'email' => 'Terlalu banyak percobaan. Silakan login ulang setelah 15 menit.',
                ]);
            }

            $remaining = AuthSession::MAX_ATTEMPTS - $session->attempts;

            return back()->withErrors([
                'otp' => "Kode OTP salah. Sisa percobaan: {$remaining}.",
            ]);
        }

        // ── Success ───────────────────────────────────
        AuthSession::purgeFor($user->id);

        session([
            'two_factor_authenticated' => true,
            'last_activity_at' => now(),
        ]);

        return redirect()->route('dashboard');
    }

    /**
     * Resend OTP code via email.
     *
     * Respects a 60-second cooldown between resend requests.
     * Generates a fresh OTP and resets the expiry timer.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $user    = $request->user();
        $session = AuthSession::activeFor($user->id);

        if (! $session) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi tidak ditemukan. Silakan login ulang.',
            ]);
        }

        if ($session->isBlocked()) {
            $this->fullLogout($request);

            return redirect()->route('login')->withErrors([
                'email' => 'Akun diblokir sementara karena percobaan gagal.',
            ]);
        }

        if (! $session->canResend()) {
            $seconds = $session->resendCooldownRemaining();

            return back()->with('status', "Tunggu {$seconds} detik sebelum mengirim ulang.");
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $session->update([
            'otp_hash'    => Hash::make($otp),
            'expires_at'  => now()->addMinutes(AuthSession::OTP_LIFETIME_MINUTES),
            'last_sent_at' => now(),
        ]);

        Mail::to($user->email)->send(new OtpMail($otp));

        return back()->with('status', 'Kode OTP baru telah dikirim ke email Anda.');
    }

    /**
     * Log the admin out and destroy the session completely.
     *
     * Steps:
     *  1. Purge all auth_sessions (OTP records)
     *  2. Logout via Auth facade
     *  3. Invalidate session data
     *  4. Regenerate CSRF token
     *  5. Clear the session cookie explicitly
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->fullLogout($request);

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Perform a complete logout:
     *  1. Purge temporary 2FA OTP codes in auth_sessions table
     *  2. Auth guard logout (removes remember-me cookie)
     *  3. Clear session keys (2FA auth, last_activity, fingerprint)
     *  4. Invalidate session & regenerate CSRF token
     *  5. Queue session cookie removal
     */
    protected function fullLogout(Request $request): void
    {
        $user = $request->user();

        if ($user) {
            AuthSession::purgeFor($user->id);
        }

        Auth::guard('web')->logout();

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
    }
}

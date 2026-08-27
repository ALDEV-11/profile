<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(): View
    {
        return view('222.auth.login');
    }

    /**
     * Authenticate the admin and start the session.
     *
     * When 2FA is enabled, an OTP is generated and emailed before access is granted.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();

        if ($user->two_factor_enabled) {
            $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            $user->forceFill([
                'two_factor_otp' => $otp,
                'two_factor_otp_expires_at' => now()->addMinutes(5),
                'two_factor_attempts' => 0,
            ])->save();

            Mail::to($user->email)->send(new OtpMail($otp));

            return redirect()->route('two-factor.verify');
        }

        session(['two_factor_authenticated' => true]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show the OTP verification form.
     */
    public function showOtpForm(): View
    {
        return view('222.auth.verify-otp');
    }

    /**
     * Verify the submitted OTP.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:4'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits' => 'Kode OTP harus terdiri dari 4 digit.',
        ]);

        $user = $request->user();

        if (! $user || ! $user->hasValidOtp($request->otp)) {
            if ($user) {
                $user->increment('two_factor_attempts');

                if ($user->two_factor_attempts >= 3) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->withErrors([
                        'email' => 'Terlalu banyak percobaan. Silakan login ulang.',
                    ]);
                }
            }

            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        $user->forceFill([
            'two_factor_otp' => null,
            'two_factor_otp_expires_at' => null,
            'two_factor_attempts' => 0,
        ])->save();

        session(['two_factor_authenticated' => true]);

        return redirect()->route('dashboard');
    }

    /**
     * Log the admin out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

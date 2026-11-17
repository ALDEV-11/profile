<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HobbyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialMediaController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

// ============================================================
// Public website (theme is applied globally via the web group)
// track.visitors records anonymous analytics on public pages only
// ============================================================
Route::middleware('track.visitors')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Contact form submission (rate limited: max 5 requests per IP per hour)
    Route::post('contact', [ContactController::class, 'store'])
        ->middleware('throttle:5,60')
        ->name('contact.store');
});

// Cron endpoints: protected by CRON_SECRET, which Vercel sends as a Bearer token.
Route::get('cron/visitors-cleanup', [CronController::class, 'visitorsCleanup'])
    ->name('cron.visitors-cleanup');

// Called by an external scheduler every few minutes to keep Neon awake.
Route::get('cron/keepalive', [CronController::class, 'keepalive'])
    ->name('cron.keepalive');

// Temporary deploy check for object storage; removed after verification.
Route::get('cron/storage-check', [CronController::class, 'storageCheck'])
    ->name('cron.storage-check');

// Admin section (kept under the "222" prefix)
Route::prefix('222')->group(function () {
    // ── Authentication (guests only) ──────────────────
    Route::middleware('guest')->group(function () {
        Route::get('auth/login', [LoginController::class, 'showLogin'])->name('login');
        Route::post('auth/login', [LoginController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('login.attempt');
    });

    // ── 2FA OTP verification (authenticated, pre-verified) ──
    Route::middleware('auth')->group(function () {
        Route::get('auth/verify-otp', [TwoFactorController::class, 'showOtpForm'])->name('two-factor.verify');
        Route::post('auth/verify-otp', [TwoFactorController::class, 'verifyOtp'])
            ->middleware('throttle:10,1')
            ->name('two-factor.verify.post');
        Route::post('auth/resend-otp', [TwoFactorController::class, 'resendOtp'])
            ->middleware('throttle:3,1')
            ->name('two-factor.resend');
        Route::post('auth/logout', [TwoFactorController::class, 'logout'])->name('logout');
    });

    // ── Protected admin routes ────────────────────────
    // session.guard: validates session integrity (fingerprint, user exists, 2FA state)
    // session.refresh: tracks activity and enforces 30-min idle timeout
    // admin.access: rate limiting + soft-delete check
    // two-factor: ensures 2FA is completed before accessing protected content
    // no.store: prevents cached pages from being restored via the browser back button
    Route::middleware(['auth', 'session.guard', 'session.refresh', 'admin.access', 'two-factor', 'no.store'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/unread-count', [DashboardController::class, 'unreadCount'])
            ->name('admin.dashboard.unread');

        // Content management
        Route::resource('hobbies', HobbyController::class)->except('show')->names('admin.hobbies');
        Route::resource('educations', EducationController::class)->except('show')->names('admin.educations');
        Route::post('educations/{education}/restore', [EducationController::class, 'restore'])
            ->name('admin.educations.restore');
        Route::resource('galleries', GalleryController::class)->except('show')->names('admin.galleries');
        Route::resource('social-medias', SocialMediaController::class)->except('show')->names('admin.social-medias');

        // Contact messages (inbox)
        Route::get('messages', [ContactMessageController::class, 'index'])->name('admin.messages.index');
        Route::get('messages/{message}', [ContactMessageController::class, 'show'])->name('admin.messages.show');
        Route::post('messages/{message}/toggle-read', [ContactMessageController::class, 'toggleRead'])
            ->name('admin.messages.toggle-read');
        Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->name('admin.messages.destroy');
        Route::post('messages/{id}/restore', [ContactMessageController::class, 'restore'])
            ->name('admin.messages.restore');

        // Analytics
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('admin.analytics.index');
        Route::get('analytics/data', [AnalyticsController::class, 'data'])->name('admin.analytics.data');

        // Profile (single record, no create/delete)
        Route::get('admin/profile/edit', [ProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::put('admin/profile', [ProfileController::class, 'update'])->name('admin.profile.update');

        // Example resource controllers go here, e.g.:
        // Route::get('messages', [ContactMessageController::class, 'index'])->name('messages.index');
        // Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });
});

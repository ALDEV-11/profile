<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class AuthSession extends Model
{
    /**
     * Maximum OTP verification attempts before blocking.
     */
    public const MAX_ATTEMPTS = 3;

    /**
     * Minutes the OTP is valid.
     */
    public const OTP_LIFETIME_MINUTES = 5;

    /**
     * Minutes to block after exceeding max attempts.
     */
    public const BLOCK_DURATION_MINUTES = 15;

    /**
     * Seconds before a new OTP can be resent.
     */
    public const RESEND_COOLDOWN_SECONDS = 60;

    protected $fillable = [
        'user_id',
        'otp_hash',
        'expires_at',
        'attempts',
        'blocked_until',
        'last_sent_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'    => 'datetime',
            'blocked_until' => 'datetime',
            'last_sent_at'  => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ──────────────────────────────────────────────
    // Query helpers
    // ──────────────────────────────────────────────

    /**
     * Get the active (latest, unexpired) OTP session for a user.
     */
    public static function activeFor(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    // ──────────────────────────────────────────────
    // State checks
    // ──────────────────────────────────────────────

    /**
     * Is this session currently blocked due to too many failed attempts?
     */
    public function isBlocked(): bool
    {
        return $this->blocked_until && $this->blocked_until->isFuture();
    }

    /**
     * Has the OTP expired?
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Has the maximum number of attempts been reached?
     */
    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Can a new OTP be sent? (respects cooldown)
     */
    public function canResend(): bool
    {
        if (! $this->last_sent_at) {
            return true;
        }

        return $this->last_sent_at->addSeconds(self::RESEND_COOLDOWN_SECONDS)->isPast();
    }

    /**
     * Remaining seconds until the resend cooldown expires.
     */
    public function resendCooldownRemaining(): int
    {
        if ($this->canResend()) {
            return 0;
        }

        return (int) now()->diffInSeconds(
            $this->last_sent_at->addSeconds(self::RESEND_COOLDOWN_SECONDS),
            false
        );
    }

    // ──────────────────────────────────────────────
    // Actions
    // ──────────────────────────────────────────────

    /**
     * Verify the given plain-text OTP against the stored hash.
     */
    public function verifyOtp(string $plainOtp): bool
    {
        return Hash::check($plainOtp, $this->otp_hash);
    }

    /**
     * Record a failed attempt and block if the limit has been reached.
     */
    public function recordFailedAttempt(): void
    {
        $this->increment('attempts');

        if ($this->hasExceededAttempts()) {
            $this->update([
                'blocked_until' => now()->addMinutes(self::BLOCK_DURATION_MINUTES),
            ]);
        }
    }

    /**
     * Invalidate this OTP session (called on successful verification).
     */
    public function invalidate(): void
    {
        $this->delete();
    }

    /**
     * Purge all OTP sessions for the given user.
     */
    public static function purgeFor(int $userId): void
    {
        static::where('user_id', $userId)->delete();
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'two_factor_enabled',
    'two_factor_secret',
    'two_factor_otp',
    'two_factor_otp_expires_at',
    'two_factor_attempts',
])]
#[Hidden(['password', 'remember_token', 'two_factor_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_otp_expires_at' => 'datetime',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Absolute URL for the admin avatar, resolved through the user's profile.
     */
    public function avatarUrl(): string
    {
        return $this->profile?->imageUrl() ?? asset('dist-dashboard/assets/img/avatars/1.png');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function socialMedias(): HasMany
    {
        return $this->hasMany(SocialMedia::class);
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    /**
     * OTP sessions for 2FA verification (stored in auth_sessions table).
     */
    public function authSessions(): HasMany
    {
        return $this->hasMany(AuthSession::class);
    }

    /**
     * Check if the user has a valid OTP in the auth_sessions table.
     *
     * Uses bcrypt comparison via the AuthSession model for timing-safe
     * verification. Kept for backwards compatibility — controllers should
     * prefer AuthSession::activeFor() directly.
     */
    public function hasValidOtp(string $otp): bool
    {
        if (! $this->two_factor_enabled) {
            return false;
        }

        $session = AuthSession::activeFor($this->id);

        if (! $session || $session->isExpired() || $session->isBlocked()) {
            return false;
        }

        return $session->verifyOtp($otp);
    }
}

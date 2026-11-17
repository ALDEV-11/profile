<?php

namespace App\Models;

use App\Support\ClearsContentCaches;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'full_name', 'about_me', 'tagline', 'email', 'phone', 'address', 'profile_image'])]
class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use ClearsContentCaches, HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Absolute URL for the profile image.
     *
     * Images inside storage (e.g. "profiles/…") resolve through the public
     * storage link; legacy records pointing at public/images/ fall back to
     * the plain asset path.
     */
    public function imageUrl(): string
    {
        if (empty($this->profile_image)) {
            return asset('images/profile-avatar.webp');
        }

        if (Str::startsWith($this->profile_image, ['profiles/', 'galleries/'])) {
            return Storage::disk('public')->url($this->profile_image);
        }

        return asset($this->profile_image);
    }
}

<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Http\UploadedFile;

class ProfileService
{
    public function __construct(
        protected ImageService $images,
    ) {
    }

    /**
     * Get (or lazily create) the single profile record for a user.
     */
    public function forUser(int $userId): Profile
    {
        return Profile::firstOrCreate(
            ['user_id' => $userId],
            [
                'full_name' => '',
                'tagline' => '',
                'about_me' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
            ]
        );
    }

    /**
     * Update profile fields and handle the profile image.
     *
     * Image precedence:
     *   1. A newly uploaded image replaces the existing one.
     *   2. remove_image=true clears the image entirely.
     *   3. Otherwise the existing image is kept.
     */
    public function update(Profile $profile, array $data, ?UploadedFile $image = null, bool $removeImage = false): Profile
    {
        $update = [
            'full_name' => $data['full_name'],
            'tagline' => $data['tagline'],
            'about_me' => $data['about_me'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
        ];

        if ($image) {
            $this->images->deleteStored($profile->profile_image);
            $update['profile_image'] = $this->images->storeSquare($image, 'profiles');
        } elseif ($removeImage) {
            $this->images->deleteStored($profile->profile_image);
            $update['profile_image'] = null;
        }

        $profile->update($update);

        return $profile;
    }
}

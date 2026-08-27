<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateSocialMediaRequest extends StoreSocialMediaRequest
{
    /**
     * Ignore the current record when checking for duplicate platforms.
     */
    protected function uniquePlatform(): Unique
    {
        return Rule::unique('social_medias', 'platform')
            ->where('user_id', $this->user()?->id)
            ->withoutTrashed()
            ->ignore($this->route('social_media')?->id);
    }

    /**
     * Same rules as store. The max-5-links check must not block editing
     * an existing record, so it is not applied here.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        // No-op: the max-5 check only applies to new links.
    }
}

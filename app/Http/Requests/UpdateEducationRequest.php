<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique as UniqueRule;

class UpdateEducationRequest extends StoreEducationRequest
{
    /**
     * Ignore the current record when checking for duplicate institutions.
     */
    protected function uniqueInstitution(): UniqueRule
    {
        return Rule::unique('educations', 'institution')
            ->where('user_id', $this->user()?->id)
            ->withoutTrashed()
            ->ignore($this->route('education')?->id);
    }
}

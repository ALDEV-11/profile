<?php

namespace App\Http\Requests;

use App\Services\SocialMediaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSocialMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'platform' => ['required', 'string', 'max:50', Rule::in(array_keys(SocialMediaService::PLATFORM_ICONS)), $this->uniquePlatform()],
            'url' => ['required', 'url', 'regex:/^https?:\/\//i', 'max:500'],
            'icon' => ['nullable', 'string', 'max:60', 'regex:/^bx[\s-][a-z0-9-]+$/i'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Ensure the platform is not already used by this user.
     */
    protected function uniquePlatform(): \Illuminate\Validation\Rules\Unique
    {
        return Rule::unique('social_medias', 'platform')
            ->where('user_id', $this->user()?->id)
            ->withoutTrashed();
    }

    /**
     * Prepare the input for validation.
     */
    protected function prepareForValidation(): void
    {
        $userId = $this->user()?->id;

        if ($userId && empty($this->input('icon'))) {
            $this->merge([
                'icon' => SocialMediaService::PLATFORM_ICONS[$this->input('platform')] ?? SocialMediaService::PLATFORM_ICONS['Custom'],
            ]);
        }
    }

    /**
     * Configure the validator instance (max 5 links).
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $userId = $this->user()?->id;

            if ($userId && \App\Models\SocialMedia::where('user_id', $userId)->count() >= SocialMediaService::MAX_LINKS) {
                $validator->errors()->add('platform', 'Maksimal '.SocialMediaService::MAX_LINKS.' link sosial media.');
            }
        });
    }

    /**
     * Custom error messages (Indonesian).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'platform.required' => 'Platform wajib diisi.',
            'platform.in' => 'Platform harus salah satu dari pilihan yang tersedia.',
            'platform.unique' => 'Platform ini sudah terdaftar.',
            'url.required' => 'URL wajib diisi.',
            'url.url' => 'URL harus berupa alamat yang valid.',
            'url.regex' => 'URL harus diawali http:// atau https://.',
            'url.max' => 'URL maksimal 500 karakter.',
            'icon.regex' => 'Ikon harus berupa class Boxicons (mis. bx bxl-github).',
            'icon.max' => 'Ikon maksimal 60 karakter.',
            'order.integer' => 'Urutan harus berupa angka.',
            'order.min' => 'Urutan tidak boleh kurang dari 0.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        // There is only one profile record per user; ignore it so the user
        // can keep their own email address.
        $profileId = $this->user()?->profile?->id;

        return [
            'full_name'    => ['required', 'string', 'max:100'],
            'tagline'      => ['required', 'string', 'max:200'],
            'about_me'     => ['required', 'string', 'max:2000'],
            'email'        => ['required', 'email', 'max:255', Rule::unique('profiles', 'email')->ignore($profileId)],
            'phone'        => ['required', 'string', 'max:30', 'regex:/^[+()\-\s\d]{7,25}$/'],
            'address'      => ['required', 'string', 'max:500'],
            'profile_image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:4096', // 4 MB
            ],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom error messages (Indonesian).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required'  => 'Nama lengkap wajib diisi.',
            'full_name.max'       => 'Nama lengkap maksimal 100 karakter.',
            'tagline.required'    => 'Tagline wajib diisi.',
            'tagline.max'         => 'Tagline maksimal 200 karakter.',
            'about_me.required'   => 'Deskripsi tentang Anda wajib diisi.',
            'about_me.max'        => 'Deskripsi tentang Anda maksimal 2000 karakter.',
            'email.required'      => 'Email wajib diisi.',
            'email.email'         => 'Format email tidak valid.',
            'email.unique'        => 'Email sudah digunakan pada profil lain.',
            'phone.required'      => 'Nomor telepon wajib diisi.',
            'phone.regex'         => 'Format nomor telepon tidak valid.',
            'address.required'    => 'Alamat wajib diisi.',
            'address.max'         => 'Alamat maksimal 500 karakter.',
            'profile_image.image' => 'File harus berupa gambar.',
            'profile_image.mimes' => 'Gambar harus berformat JPG, PNG, atau WebP.',
            'profile_image.max'   => 'Ukuran gambar maksimal 4 MB.',
        ];
    }
}

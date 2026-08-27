<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHobbyRequest extends FormRequest
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
            'name'  => ['required', 'string', 'max:100'],
            'icon'  => ['required', 'string', 'max:60', 'regex:/^bx-[a-z0-9-]+$|^[\p{L}\p{N}\p{So}\p{Sk}\p{M}\p{Cf}\p{Emoji}]+$/u'],
            'order' => ['nullable', 'integer', 'min:0'],
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
            'name.required' => 'Nama hobby wajib diisi.',
            'name.max'      => 'Nama hobby maksimal 100 karakter.',
            'icon.required' => 'Ikon hobby wajib diisi.',
            'icon.max'      => 'Ikon hobby maksimal 60 karakter.',
            'icon.regex'    => 'Ikon harus berupa ikon Boxicons (mis. bx-joystick) atau emoji.',
            'order.integer' => 'Urutan harus berupa angka.',
            'order.min'     => 'Urutan tidak boleh kurang dari 0.',
        ];
    }
}

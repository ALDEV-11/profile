<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGalleryRequest extends FormRequest
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
            'image' => ['required', 'image', 'mimes:jpg,jpeg,webp', 'max:4096'],
            'caption' => ['nullable', 'string', 'max:500'],
            'category' => ['required', Rule::in(['project', 'event', 'random_photo'])],
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
            'image.required' => 'Foto wajib diunggah.',
            'image.image' => 'File harus berupa gambar yang valid.',
            'image.mimes' => 'Format gambar harus JPG, JPEG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 4MB.',
            'caption.max' => 'Caption maksimal 500 karakter.',
            'category.required' => 'Kategori wajib diisi.',
            'category.in' => 'Kategori harus salah satu dari: Project, Event, Random Photo.',
            'order.integer' => 'Urutan harus berupa angka.',
            'order.min' => 'Urutan tidak boleh kurang dari 0.',
        ];
    }
}

<?php

namespace App\Http\Requests;

class UpdateGalleryRequest extends StoreGalleryRequest
{
    /**
     * The photo is optional when editing (existing image is kept).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,webp', 'max:4096'],
            'caption' => ['nullable', 'string', 'max:500'],
            'category' => ['required', 'in:project,event,random_photo'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

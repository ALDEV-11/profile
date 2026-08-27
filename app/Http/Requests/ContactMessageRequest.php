<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactMessageRequest extends FormRequest
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
            'name'    => ['required', 'string', 'min:2', 'max:100'],
            'email'   => ['required', 'string', 'email', 'max:255'],
            'subject' => ['required', 'string', 'min:5', 'max:100'],
            'message' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    /**
     * Custom error messages (English).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'    => 'Please enter your name.',
            'name.min'         => 'Name must be at least 2 characters.',
            'name.max'         => 'Name may not exceed 100 characters.',
            'email.required'   => 'Please enter your email address.',
            'email.email'      => 'Please enter a valid email address.',
            'email.max'        => 'Email may not exceed 255 characters.',
            'subject.required' => 'Please enter a subject.',
            'subject.min'      => 'Subject must be at least 5 characters.',
            'subject.max'      => 'Subject may not exceed 100 characters.',
            'message.required' => 'Please enter your message.',
            'message.min'      => 'Message must be at least 10 characters.',
            'message.max'      => 'Message may not exceed 1000 characters.',
        ];
    }

    /**
     * Prepare the data for validation: trim whitespace and strip HTML tags.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'    => $this->clean($this->input('name')),
            'email'   => $this->clean($this->input('email')),
            'subject' => $this->clean($this->input('subject')),
            'message' => $this->clean($this->input('message')),
        ]);
    }

    private function clean(mixed $value): ?string
    {
        return $value === null ? null : trim(strip_tags((string) $value));
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique as UniqueRule;
class StoreEducationRequest extends FormRequest
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
            'institution' => ['required', 'string', 'max:200', $this->uniqueInstitution()],
            'field_of_study' => ['required', 'string', 'max:100'],
            'degree' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'integer', 'min:1900', 'max:' . (now()->year + 10)],
            'end_date' => ['nullable', 'integer', 'min:1900', 'max:' . (now()->year + 10)],
            'is_currently_studying' => ['sometimes', 'boolean'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Prepare the input for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_currently_studying' => $this->boolean('is_currently_studying'),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $start = $this->input('start_date');
            $end = $this->input('end_date');

            if ($this->boolean('is_currently_studying')) {
                return;
            }

            if ($end === null || $end === '') {
                $validator->errors()->add('end_date', 'End year wajib diisi jika tidak sedang belajar.');

                return;
            }

            if ($start !== null && $start !== '' && (int) $end < (int) $start) {
                $validator->errors()->add('end_date', 'End year harus setelah start year.');
            }
        });
    }

    protected function uniqueInstitution(): UniqueRule
    {
        return Rule::unique('educations', 'institution')
            ->where('user_id', $this->user()?->id)
            ->withoutTrashed();
    }

    /**
     * Custom error messages (Indonesian).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'institution.required' => 'Institution wajib diisi.',
            'institution.max' => 'Institution maksimal 200 karakter.',
            'institution.unique' => 'Institution sudah terdaftar.',
            'field_of_study.required' => 'Field of study wajib diisi.',
            'field_of_study.max' => 'Field of study maksimal 100 karakter.',
            'degree.required' => 'Degree wajib diisi.',
            'degree.max' => 'Degree maksimal 100 karakter.',
            'start_date.required' => 'Start year wajib diisi.',
            'start_date.integer' => 'Start year harus berupa tahun (mis. 2018).',
            'start_date.min' => 'Start year tidak boleh sebelum 1900.',
            'start_date.max' => 'Start year tidak boleh melebihi tahun saat ini.',
            'end_date.integer' => 'End year harus berupa tahun (mis. 2022).',
            'end_date.min' => 'End year tidak boleh sebelum 1900.',
            'end_date.max' => 'End year tidak boleh melebihi tahun saat ini.',
            'order.integer' => 'Urutan harus berupa angka.',
            'order.min' => 'Urutan tidak boleh kurang dari 0.',
        ];
    }
}

<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'family' => 'sometimes',
            'name' => 'sometimes',
            'native_name' => 'sometimes',
            'code' => 'sometimes',
            'code_2' => 'sometimes',
            'status' => 'nullable|sometimes|boolean',
        ];
    }
}

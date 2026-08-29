<?php

namespace App\Http\Requests\TechnicalDictionary;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTechnicalDictionaryRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'term' => ['required', 'string', 'max:255'],
            'explanation' => ['required', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ];
    }
}

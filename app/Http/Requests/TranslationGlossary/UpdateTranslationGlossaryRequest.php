<?php

namespace App\Http\Requests\TranslationGlossary;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationGlossaryRequest extends FormRequest
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
            'language_id' => 'required|exists:languages,id|sometimes',
            'source_term' => 'required|sometimes',
            'target_term' => 'required|sometimes',
        ];
    }
}

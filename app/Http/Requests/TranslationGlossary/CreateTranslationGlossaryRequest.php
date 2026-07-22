<?php

namespace App\Http\Requests\TranslationGlossary;

use Illuminate\Foundation\Http\FormRequest;

class CreateTranslationGlossaryRequest extends FormRequest
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
            'source_term' => 'required',
            'target_term' => 'required',
        ];
    }
}

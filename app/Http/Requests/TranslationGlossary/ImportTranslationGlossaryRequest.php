<?php

namespace App\Http\Requests\TranslationGlossary;

use Illuminate\Foundation\Http\FormRequest;

class ImportTranslationGlossaryRequest extends FormRequest
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
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
            'language_id' => 'required|integer|exists:languages,id',
        ];
    }
}

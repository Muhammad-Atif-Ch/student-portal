<?php

namespace App\Http\Requests\LanguageVoice;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguageVoiceRequest extends FormRequest
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
            'language_id' => 'sometimes|exists:languages,id',
            'gender' => 'sometimes|in:Female,Male',
            'locale' => 'sometimes|string|max:255',
            'name' => 'sometimes|string|max:255',
        ];
    }
}

<?php

namespace App\Http\Requests\LanguageVoice;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageVoiceRequest extends FormRequest
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
            'language_id' => 'required|exists:languages,id',
            'gender' => [
                'required',
                'in:Female,Male',
                Rule::unique('language_voices', 'gender')
                    ->where('language_id', $this->input('language_id'))
            ],
            'locale' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ];
    }
}

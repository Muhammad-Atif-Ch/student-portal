<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApiSettingsRequest extends FormRequest
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
            'translation_api_key' => 'nullable|string|max:1000',
            'translation_api_region' => 'nullable|string|max:100',
            'tts_api_key' => 'nullable|string|max:1000',
            'tts_api_region' => 'nullable|string|max:100',
        ];
    }
}

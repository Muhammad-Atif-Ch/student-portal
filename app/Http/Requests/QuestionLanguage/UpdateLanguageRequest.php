<?php

namespace App\Http\Requests\QuestionLanguage;

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
            'language_id' => ['required', 'integer'],
            'title_audio_file' => 'nullable|mimes:mp3,wav,ogg|max:10240',
            'a_audio_file' => 'nullable|mimes:mp3,wav,ogg|max:10240',
            'b_audio_file' => 'nullable|mimes:mp3,wav,ogg|max:10240',
            'c_audio_file' => 'nullable|mimes:mp3,wav,ogg|max:10240',
            'd_audio_file' => 'nullable|mimes:mp3,wav,ogg|max:10240',
        ];
    }
}

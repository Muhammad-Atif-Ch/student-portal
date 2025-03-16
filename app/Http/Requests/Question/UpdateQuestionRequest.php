<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateQuestionRequest extends FormRequest
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
            'quiz_id' => 'required|exists:quizzes,id|sometimes',
            'question' => 'required|sometimes',
            'correct_answer' => 'required|sometimes',
            'a' => 'required|sometimes',
            'b' => 'required|sometimes',
            'c' => 'required|sometimes',
            'type' => 'required|sometimes|in:car,bike,both',
            'answer_explanation' => 'nullable|sometimes',
            'extra_explanation' => 'nullable|sometimes',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a,aac|max:10240|sometimes',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:10240|sometimes',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'quiz_id' => $this->route('quiz'),
        ]);
    }
}

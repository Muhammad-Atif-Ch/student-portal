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
            'c' => 'nullable|sometimes',
            'd' => 'nullable|sometimes',
            'type' => 'required|sometimes|in:car,bike,both',
            'answer_explanation' => 'nullable|sometimes',
            'visual_explanation' => 'nullable|sometimes|file|mimes:jpg,jpeg,png,mp4,mov,avi,mkv|max:10240',
            // 'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a,aac|max:10240|sometimes',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi,mkv|max:10240|sometimes',
            'question_translation' => 'nullable',
            'a_translation' => 'nullable',
            'b_translation' => 'nullable',
            'c_translation' => 'nullable',
            'd_translation' => 'nullable',
            'answer_explanation_translation' => 'nullable',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'quiz_id' => $this->route('quiz'),
        ]);
    }
}

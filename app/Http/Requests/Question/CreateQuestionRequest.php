<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuestionRequest extends FormRequest
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
            'question' => 'required',
            'correct_answer' => 'required',
            'a' => 'required',
            'b' => 'required',
            'c' => 'nullable',
            'd' => 'nullable',
            'type' => 'required|required|in:car,bike,both',
            // 'audio_file' => 'nullable|mimes:mp3,wav,ogg|max:10240',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi,mkv|max:10240|sometimes',
            'answer_explanation' => 'nullable',
            'visual_explanation' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi,mkv|max:10240|sometimes',
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

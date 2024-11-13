<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;

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
            'test_id' => 'required|exists:tests,id|sometimes',
            'question' => 'required|sometimes',
            'correct_answer' => 'required|sometimes',
            'a' => 'required|sometimes',
            'b' => 'required|sometimes',
            'c' => 'required|sometimes',
            'd' => 'required|sometimes',
            'e' => 'nullable|sometimes',
            'f' => 'nullable|sometimes',
            'answer_explanation' => 'nullable|sometimes',
            'audio_file' => 'nullable|file|sometimes',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'test_id' => $this->route('test'),
        ]);
    }
}

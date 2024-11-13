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
            'test_id' => 'required|exists:tests,id|sometimes',
            'question' => 'required',
            'correct_answer' => 'required',
            'a' => 'required',
            'b' => 'required',
            'c' => 'required',
            'd' => 'required',
            'e' => 'nullable',
            'f' => 'nullable',
            'answer_explanation' => 'nullable',
            'audio_file' => 'nullable|file',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'test_id' => $this->route('test'),
        ]);
    }
}

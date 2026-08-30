<?php

namespace App\Http\Requests\CpcQuestion;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCpcQuestionRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'sometimes'],
            'answer_explanation' => ['nullable', 'string'],
            'cpc_case_study_id' => ['nullable', 'exists:cpc_case_studies,id'],
            'correct_option' => ['required', 'in:a,b,c,d', 'sometimes'],
            'options' => ['required', 'array'],
            'options.a.type' => ['required', 'in:text,file'],
            'options.b.type' => ['required', 'in:text,file'],
            'options.c.type' => ['required', 'in:text,file'],
            'options.d.type' => ['required', 'in:text,file'],
            'options.a.text_value' => ['nullable', 'string'],
            'options.b.text_value' => ['nullable', 'string'],
            'options.c.text_value' => ['nullable', 'string'],
            'options.d.text_value' => ['nullable', 'string'],
            'options.a.file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,mp4,mov,avi,mkv,mp3,wav', 'max:10240'],
            'options.b.file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,mp4,mov,avi,mkv,mp3,wav', 'max:10240'],
            'options.c.file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,mp4,mov,avi,mkv,mp3,wav', 'max:10240'],
            'options.d.file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,mp4,mov,avi,mkv,mp3,wav', 'max:10240'],
        ];
    }
}

<?php

namespace App\Http\Requests\CpcQuestion;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateCpcQuestionRequest extends FormRequest
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
            'question' => ['required', 'string'],
            'answer_explanation' => ['nullable', 'string'],
            'cpc_case_study_id' => ['nullable', 'exists:cpc_case_studies,id'],
            'correct_option' => ['required', 'in:a,b,c,d'],
            'options' => ['required', 'array'],
            'options.a.type' => ['required', 'in:text,file'],
            'options.b.type' => ['required', 'in:text,file'],
            'options.c.type' => ['required', 'in:text,file'],
            'options.d.type' => ['required', 'in:text,file'],
            'options.a.text_value' => ['required_if:options.a.type,text', 'nullable', 'string'],
            'options.b.text_value' => ['required_if:options.b.type,text', 'nullable', 'string'],
            'options.c.text_value' => ['required_if:options.c.type,text', 'nullable', 'string'],
            'options.d.text_value' => ['required_if:options.d.type,text', 'nullable', 'string'],
            'options.a.file' => ['required_if:options.a.type,file', 'nullable', 'file', 'mimes:jpg,jpeg,png,gif,mp4,mov,avi,mkv,mp3,wav', 'max:10240'],
            'options.b.file' => ['required_if:options.b.type,file', 'nullable', 'file', 'mimes:jpg,jpeg,png,gif,mp4,mov,avi,mkv,mp3,wav', 'max:10240'],
            'options.c.file' => ['required_if:options.c.type,file', 'nullable', 'file', 'mimes:jpg,jpeg,png,gif,mp4,mov,avi,mkv,mp3,wav', 'max:10240'],
            'options.d.file' => ['required_if:options.d.type,file', 'nullable', 'file', 'mimes:jpg,jpeg,png,gif,mp4,mov,avi,mkv,mp3,wav', 'max:10240'],
        ];
    }
}

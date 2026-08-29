<?php

namespace App\Http\Requests\CpcExam;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCpcExamRequest extends FormRequest
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
            'cpc_type_id' => ['required', 'exists:cpc_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'mode' => [
                'required',
                'in:full,short',
                Rule::unique('cpc_exams')
                    ->where(fn ($query) => $query->where('cpc_type_id', $this->input('cpc_type_id')))
                    ->ignore($this->route('exam')),
            ],
            'total_time_minutes' => ['required', 'integer', 'min:1'],
            'total_questions' => ['required', 'integer', 'min:1'],
            'passing_score' => ['required', 'integer', 'min:1', 'lte:total_questions'],
            'min_marks_per_scenario' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

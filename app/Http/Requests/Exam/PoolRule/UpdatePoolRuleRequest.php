<?php

namespace App\Http\Requests\Exam\PoolRule;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePoolRuleRequest extends FormRequest
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
            'exam_type_id' => ['required', 'integer', 'exists:exam_types,id', 'sometimes'],
            'quiz_id' => ['required', 'integer', 'exists:quizzes,id', 'sometimes'],
            'pool_type' => ['required', Rule::in(['common', 'specific']), 'sometimes'],
            'specific_type' => ['nullable', 'required_if:pool_type,specific', Rule::in(['car', 'bike', 'bus', 'truck'])],
            'required_count' => ['required', 'integer', 'min:1', 'sometimes'],
        ];
    }
}

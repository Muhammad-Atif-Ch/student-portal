<?php

namespace App\Http\Requests\Exam\Type;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTargetTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'sometimes'],
            'total_questions' => ['required', 'integer', 'min:1', 'sometimes'],
            'passing_marks' => ['required', 'integer', 'min:1', 'sometimes'],
            'total_time_minutes' => ['required', 'integer', 'min:1', 'sometimes'],
        ];
    }
}

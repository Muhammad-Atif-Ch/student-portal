<?php

namespace App\Http\Requests\Api;

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

    protected function prepareForValidation()
    {
        //dd('validation file', is_string($this->data));
        // Convert JSON string to array before validation
        $decoded = json_decode($this->data, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $this->merge([
                'data' => $decoded
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data' => 'required|array|min:1',
            'data.*.quiz_id' => 'required|integer|min:1|exists:quizzes,id',
            'data.*.question_id' => 'required|integer|min:1',
            'data.*.answer' => 'required|string|max:10',
            'data.*.type' => 'required|string|in:practice,official,exam,test',
            'data.*.correct' => 'required|integer|in:0,1',
        ];

    }

    public function messages(): array
    {
        return [
            'data.required' => 'Quiz data is required',
            'data.array' => 'Quiz data must be a valid array',
            'data.min' => 'At least one quiz item is required',

            // Quiz ID messages
            'data.*.quiz_id.required' => 'Quiz ID is required for each item',
            'data.*.quiz_id.integer' => 'Quiz ID must be an integer',
            'data.*.quiz_id.min' => 'Quiz ID must be greater than 0',
            'data.*.quiz_id.exists' => 'The selected quiz ID does not exist',

            // Question ID messages
            'data.*.question_id.required' => 'Question ID is required for each item',
            'data.*.question_id.integer' => 'Question ID must be an integer',
            'data.*.question_id.min' => 'Question ID must be greater than 0',
            'data.*.question_id.exists' => 'The selected question ID does not exist',

            // Answer messages
            'data.*.answer.required' => 'Answer is required for each item',
            'data.*.answer.string' => 'Answer must be a string',
            'data.*.answer.max' => 'Answer cannot be longer than 10 characters',

            // Type messages
            'data.*.type.required' => 'Type is required for each item',
            'data.*.type.string' => 'Type must be a string',
            'data.*.type.in' => 'Type must be one of: practice, official, practic, exam, test',

            // Correct messages
            'data.*.correct.required' => 'Correct field is required for each item',
            'data.*.correct.boolean' => 'Correct field must be true or false (1 or 0)',
        ];
    }
}

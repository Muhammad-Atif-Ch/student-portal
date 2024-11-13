<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user');

        return [
            'name' => 'required|sometimes',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'mobile' => [
                'sometimes',
                'nullable',
                'string',
                Rule::unique('users', 'mobile')->ignore($userId)
            ],
            'city' => 'nullable|sometimes',
            'address' => 'nullable|sometimes',
            //'password' => 'sometimes',
            'image' => 'nullable|file|sometimes',
            'role_id' => 'required|exists:roles,id|sometimes',
        ];
    }

    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //         dd('test form', [
    //             'current_errors' => $validator->errors()->toArray(),
    //             'data_being_validated' => $this->all(),
    //             'failed_rules' => $validator->failed()
    //         ]);
    //     });
    // }
}

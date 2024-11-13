<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|lowercase|email|max:255|unique:users,email,except,id',
            'mobile' => 'nullable|unique:users,mobile,except,id',
            'city' => 'nullable',
            'address' => 'nullable',
            'password' => ['required', 'confirmed'],
            'image' => 'nullable|file',
            'role_id' => 'required|exists:roles,id',
        ];
    }
}

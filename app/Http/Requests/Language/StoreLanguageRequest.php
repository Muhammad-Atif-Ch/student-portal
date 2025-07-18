<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageRequest extends FormRequest
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
            'family' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'native_name' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'code_2' => 'nullable|string|max:10',
            'status' => 'required|boolean',
        ];
    }
}

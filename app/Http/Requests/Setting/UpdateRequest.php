<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'theme_layout' => 'nullable|in:1,2|sometimes',
            'sidebar_color' => 'nullable|in:1,2|sometimes',
            'color_theme' => 'nullable|in:white,black,red,green,orange,purple,cyan|sometimes',
            'mini_sidebar' => 'nullable|boolean|sometimes',
            'stiky_header' => 'nullable|boolean|sometimes',
        ];
    }
}

<?php

namespace App\Http\Requests\CpcCaseStudy;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateCpcCaseStudyRequest extends FormRequest
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
            'title' => ['required', 'string'],
            'cpc_type_id' => ['required', 'exists:cpc_types,id'],
            'blocks' => ['required', 'array', 'min:1'],
            'blocks.*.type' => ['required', 'in:text,image,list'],
            'blocks.*.content' => ['nullable', 'string'],
            'blocks.*.list_style' => ['nullable', 'in:bullet,numbered'],
            'blocks.*.items_text' => ['nullable', 'string'],
            'blocks.*.image' => ['nullable', 'file', 'image', 'max:10240'],
        ];
    }
}

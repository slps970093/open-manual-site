<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManualRequest extends FormRequest
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
        $manualId = $this->route('manual');

        return [
            'url_slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('manual', 'url_slug')->ignore($manualId),
            ],
            'name' => 'required|array',
            'name.*' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url_slug.required' => 'URL 別名為必填項',
            'url_slug.unique' => '此 URL 別名已被使用',
            'url_slug.regex' => 'URL 別名只能包含字母、數字、連字符和下劃線',
            'name.required' => '名稱為必填項',
        ];
    }
}

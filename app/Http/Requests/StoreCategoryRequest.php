<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:categories,name',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:10',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.min' => 'Category name must be at least 3 characters.',
            'name.unique' => 'A category with this name already exists.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'icon.max' => 'Icon cannot exceed 10 characters.',
        ];
    }
}

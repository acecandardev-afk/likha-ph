<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isArtisan() && !$this->user()->isSuspended();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'exists:categories,id',
            ],
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'description' => [
                'required',
                'string',
                'min:50',
                'max:2000',
            ],
            'price' => [
                'required',
                'numeric',
                'min:1',
                'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/', // Max 2 decimal places
            ],
            'stock' => [
                'required',
                'integer',
                'min:0',
                'max:9999',
            ],
            'images' => [
                'required',
                'array',
                'min:1',
                'max:5',
            ],
            'images.*' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png',
                'max:2048', // 2MB max
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a product category.',
            'category_id.exists' => 'The selected category is invalid.',
            'name.required' => 'Product name is required.',
            'name.min' => 'Product name must be at least 3 characters.',
            'name.max' => 'Product name cannot exceed 255 characters.',
            'description.required' => 'Product description is required.',
            'description.min' => 'Description must be at least 50 characters to provide adequate information.',
            'description.max' => 'Description cannot exceed 2000 characters.',
            'price.required' => 'Product price is required.',
            'price.min' => 'Price must be at least ₱1.00.',
            'price.max' => 'Price cannot exceed ₱999,999.99.',
            'price.regex' => 'Price can only have up to 2 decimal places.',
            'stock.required' => 'Stock quantity is required.',
            'stock.min' => 'Stock cannot be negative.',
            'stock.max' => 'Stock quantity seems unusually high. Please verify.',
            'images.required' => 'Please upload at least one product image.',
            'images.min' => 'At least one product image is required.',
            'images.max' => 'You can upload a maximum of 5 images.',
            'images.*.image' => 'All files must be valid images.',
            'images.*.mimes' => 'Images must be in JPEG, JPG, or PNG format.',
            'images.*.max' => 'Each image must not exceed 2MB in size.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'images.*' => 'image',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedAuthorization(): void
    {
        abort(403, 'You must be an active artisan to create products.');
    }
}
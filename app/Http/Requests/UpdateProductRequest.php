<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $this->user()
            && $this->user()->can('update', $product);
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
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'stock' => [
                'required',
                'integer',
                'min:0',
                'max:9999',
            ],
            'new_images' => [
                'nullable',
                'array',
                'max:5',
            ],
            'new_images.*' => [
                'image',
                'mimes:jpeg,jpg,png',
                'max:2048',
            ],
            'remove_images' => [
                'nullable',
                'array',
            ],
            'remove_images.*' => [
                'exists:product_images,id',
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
            'description.min' => 'Description must be at least 50 characters.',
            'price.regex' => 'Price can only have up to 2 decimal places.',
            'new_images.max' => 'You can upload a maximum of 5 new images.',
            'new_images.*.image' => 'All files must be valid images.',
            'new_images.*.mimes' => 'Images must be in JPEG, JPG, or PNG format.',
            'new_images.*.max' => 'Each image must not exceed 2MB in size.',
            'remove_images.*.exists' => 'Invalid image selected for removal.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $product = $this->route('product');

            // Ensure at least one image remains
            $currentImages = $product->images()->count();
            $removing = count($this->input('remove_images', []));
            $adding = count($this->file('new_images', []));

            $finalCount = $currentImages - $removing + $adding;

            if ($finalCount < 1) {
                $validator->errors()->add('images', 'Product must have at least one image.');
            }

            if ($finalCount > 5) {
                $validator->errors()->add('new_images', 'Total images cannot exceed 5.');
            }
        });
    }
}

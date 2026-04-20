<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isCustomer();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $product = $this->route('product');
        
        return [
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . ($product ? $product->stock : 1),
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        $product = $this->route('product');
        
        return [
            'quantity.required' => 'Please specify a quantity.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => $product 
                ? "Only {$product->stock} item(s) available in stock."
                : 'Requested quantity exceeds available stock.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $product = $this->route('product');
            
            if (!$product->isAvailable()) {
                $validator->errors()->add('product', 'This product is not available for purchase.');
            }
        });
    }
}
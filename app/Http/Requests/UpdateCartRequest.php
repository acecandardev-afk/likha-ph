<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $cart = $this->route('cart');

        return $this->user() && $cart->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $cart = $this->route('cart');

        return [
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:'.($cart ? $cart->product->stock : 1),
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        $cart = $this->route('cart');

        return [
            'quantity.required' => 'Please specify a quantity.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => $cart
                ? "Only {$cart->product->stock} item(s) available in stock."
                : 'Requested quantity exceeds available stock.',
        ];
    }
}

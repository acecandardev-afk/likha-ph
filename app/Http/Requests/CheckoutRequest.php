<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Support\Guihulngan;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', Order::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'shipping_barangay' => Guihulngan::barangayRules(true),
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'shipping_phone' => ['required', 'string', 'max:20'],
            'payment_method' => [
                'required',
                'string',
                'in:bank_transfer,gcash,cash,cod',
            ],
            'customer_notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
            'customer_notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'shipping_barangay' => 'barangay',
            'shipping_address' => 'street or additional directions',
            'shipping_phone' => 'contact number',
            'payment_method' => 'payment method',
            'customer_notes' => 'order notes',
        ];
    }
}
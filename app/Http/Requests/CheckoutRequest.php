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
            'country' => ['required', 'string', 'in:Philippines'],
            'region' => ['required', 'integer', 'exists:regions,id'],
            'province' => ['required', 'integer', 'exists:provinces,id'],
            'city' => ['required', 'integer', 'exists:cities,id'],
            'barangay' => ['required', 'integer', 'exists:barangays,id'],
            'street_address' => ['nullable', 'string', 'max:500'],
            'phone' => [
                'required',
                'string',
                'regex:/^(09\d{9}|\+63\d{10})$/',
                'max:13'
            ],
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

    public function messages(): array
    {
        return [
            'phone.regex' => 'Phone number must start with 09 (followed by 9 digits) or +63 (followed by 10 digits).',
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
            'country' => 'country',
            'region' => 'region',
            'province' => 'province',
            'city' => 'city',
            'barangay' => 'barangay',
            'street_address' => 'street address',
            'phone' => 'contact number',
            'payment_method' => 'payment method',
            'customer_notes' => 'order notes',
        ];
    }
}
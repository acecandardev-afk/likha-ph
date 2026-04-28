<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $this->user() && $this->user()->can('cancel', $order);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'reason' => [
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
            'reason.max' => 'Cancellation reason cannot exceed 500 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $order = $this->route('order');

            if ($this->user()?->isAdmin()) {
                return;
            }

            if (! $order->canBeCancelled()) {
                $validator->errors()->add('order', 'This order cannot be cancelled once it has shipped. Only pending orders can be cancelled.');
            }
        });
    }
}

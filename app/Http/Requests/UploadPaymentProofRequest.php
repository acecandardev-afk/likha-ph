<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentProofRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $this->user() && $this->user()->can('view', $order);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'proof_image' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png',
                'max:2048',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'proof_image.required' => 'Please upload payment proof.',
            'proof_image.image' => 'Payment proof must be an image file.',
            'proof_image.mimes' => 'Payment proof must be in JPEG, JPG, or PNG format.',
            'proof_image.max' => 'Payment proof must not exceed 2MB in size.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $order = $this->route('order');
            $payment = $order->payment;

            if (! $payment || ! $payment->isAwaitingProof()) {
                $validator->errors()->add('proof_image', 'Payment proof cannot be uploaded at this time.');
            }
        });
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductApprovalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $this->user() && $this->user()->can('approve', $product);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $action = $this->route()->getActionMethod();

        if ($action === 'reject') {
            return [
                'reason' => [
                    'required',
                    'string',
                    'min:10',
                    'max:500',
                ],
            ];
        }

        // For approve action
        return [
            'notes' => [
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
            'reason.required' => 'Please provide a reason for rejection.',
            'reason.min' => 'Rejection reason must be at least 10 characters.',
            'reason.max' => 'Rejection reason cannot exceed 500 characters.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }
}

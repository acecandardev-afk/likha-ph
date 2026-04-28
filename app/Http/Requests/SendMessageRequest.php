<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $this->user() && $this->user()->can('sendMessage', $order);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'message' => [
                'required',
                'string',
                'min:1',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'message.required' => 'Message cannot be empty.',
            'message.min' => 'Message must contain at least 1 character.',
            'message.max' => 'Message cannot exceed 1000 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from message
        if ($this->has('message')) {
            $this->merge([
                'message' => trim($this->message),
            ]);
        }
    }
}

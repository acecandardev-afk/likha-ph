<?php

namespace App\Http\Requests;

use App\Models\OrderItemReturn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderItemReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'reason' => ['required', 'string', Rule::in(OrderItemReturn::reasonKeys())],
            'notes' => ['required', 'string', 'min:10', 'max:5000'],
            'proof_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }
}

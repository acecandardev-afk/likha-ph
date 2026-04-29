<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/', 'unique:vouchers,code'],
            'label' => ['nullable', 'string', 'max:255'],
            'discount_type' => ['required', 'in:percent,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $starts = $this->input('starts_at');
            $ends = $this->input('ends_at');
            if ($starts !== null && $starts !== '' && $ends !== null && $ends !== ''
                && strtotime((string) $ends) < strtotime((string) $starts)) {
                $v->errors()->add('ends_at', 'End date must be on or after the start date.');
            }
            $type = $this->input('discount_type');
            $value = $this->input('discount_value');
            if ($type === 'percent' && $value !== null && (float) $value > 100) {
                $v->errors()->add('discount_value', 'Percentage discounts cannot exceed 100%.');
            }
            if ($type === 'fixed' && $value !== null && (float) $value <= 0) {
                $v->errors()->add('discount_value', 'Fixed discount amount must be greater than zero.');
            }
            if ($type === 'percent' && $value !== null && (float) $value <= 0) {
                $v->errors()->add('discount_value', 'Percent discount must be greater than zero.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Use only letters, numbers, underscores, or hyphens.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Barangay;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * Account settings store barangay as a name string. Cascade selects may also
     * post a name in edge cases. Resolve to a barangay id under the selected city.
     */
    protected function prepareForValidation(): void
    {
        $raw = $this->input('barangay');
        if ($raw === null || $raw === '') {
            return;
        }

        $b = is_string($raw) ? trim($raw) : (is_numeric($raw) ? (string) $raw : '');
        if ($b === '') {
            return;
        }
        if (is_numeric($b)) {
            $this->merge(['barangay' => (int) $b]);
        } elseif (is_numeric($this->input('city'))) {
            $id = Barangay::query()
                ->where('city_id', (int) $this->input('city'))
                ->where('name', $b)
                ->value('id');
            if ($id) {
                $this->merge(['barangay' => (int) $id]);
            }
        }

        $this->reconcileBarangayToCity();
    }

    /**
     * If barangay id does not belong to the posted city, try same barangay name
     * under that city (stale id / reseeded data from UI vs DB).
     */
    protected function reconcileBarangayToCity(): void
    {
        $rawCity = $this->input('city');
        $rawBar = $this->input('barangay');
        if (! is_numeric($rawCity) || ! is_numeric($rawBar)) {
            return;
        }

        $cid = (int) $rawCity;
        $bid = (int) $rawBar;

        if (Barangay::query()->where('id', $bid)->where('city_id', $cid)->exists()) {
            return;
        }

        $name = Barangay::query()->whereKey($bid)->value('name');
        if (! $name) {
            return;
        }

        $fixed = Barangay::query()
            ->where('city_id', $cid)
            ->where('name', $name)
            ->value('id');
        if ($fixed) {
            $this->merge(['barangay' => (int) $fixed]);
        }
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
            'barangay' => [
                'required',
                'integer',
                Rule::exists('barangays', 'id')->where(fn ($q) => $q->where('city_id', (int) $this->input('city'))),
            ],
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
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Support\SignupEmailValidation;

class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'email' => SignupEmailValidation::profileEmailRules($this->user()->id),
            'phone' => [
                'required',
                'string',
                'regex:/^(\+63|0)[0-9]{10}$/',
            ],
            'address' => [
                'required',
                'string',
                'max:500',
            ],
            'current_password' => [
                'nullable',
                'required_with:new_password',
                'string',
                'current_password',
            ],
            'new_password' => [
                'nullable',
                'required_with:current_password',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Please enter a valid Philippine phone number.',
            'address.required' => 'Address is required.',
            'current_password.required_with' => 'Current password is required to change password.',
            'current_password.current_password' => 'Current password is incorrect.',
            'new_password.required_with' => 'New password is required.',
            'new_password.min' => 'New password must be at least 8 characters.',
            'new_password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
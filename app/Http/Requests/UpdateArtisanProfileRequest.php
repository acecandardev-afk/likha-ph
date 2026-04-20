<?php

namespace App\Http\Requests;

use App\Support\Guihulngan;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArtisanProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isArtisan();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'workshop_name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'story' => [
                'nullable',
                'string',
                'min:50',
                'max:2000',
            ],
            'barangay' => Guihulngan::barangayRules(true),
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
            'profile_image' => [
                'nullable',
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
            'workshop_name.required' => 'Workshop name is required.',
            'workshop_name.min' => 'Workshop name must be at least 3 characters.',
            'workshop_name.max' => 'Workshop name cannot exceed 255 characters.',
            'story.min' => 'Your story should be at least 50 characters to be meaningful.',
            'story.max' => 'Your story cannot exceed 2000 characters.',
            'barangay.required' => 'Barangay is required.',
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Please enter a valid Philippine phone number (e.g., +639171234567 or 09171234567).',
            'address.required' => 'Address is required.',
            'address.max' => 'Address cannot exceed 500 characters.',
            'profile_image.image' => 'Profile picture must be an image file.',
            'profile_image.mimes' => 'Profile picture must be in JPEG, JPG, or PNG format.',
            'profile_image.max' => 'Profile picture must not exceed 2MB in size.',
        ];
    }
}
<?php

namespace App\Support;

use App\Rules\NotDisposableEmail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Email;

class SignupEmailValidation
{
    /**
     * Rules for the email field on new registration (unique against users.email).
     */
    public static function registrationEmailRules(): array
    {
        $email = (new Email)->rfcCompliant()->preventSpoofing();
        if (config('signup.validate_mx', true)) {
            $email->validateMxRecord();
        }

        return [
            'required',
            'string',
            'max:255',
            $email,
            'unique:users,email',
            new NotDisposableEmail,
        ];
    }

    /**
     * Rules for updating email on an existing user profile.
     */
    public static function profileEmailRules(int $userId): array
    {
        $email = (new Email)->rfcCompliant()->preventSpoofing();
        if (config('signup.validate_mx', true)) {
            $email->validateMxRecord();
        }

        return [
            'required',
            'string',
            'max:255',
            $email,
            Rule::unique('users', 'email')->ignore($userId),
            new NotDisposableEmail,
        ];
    }
}

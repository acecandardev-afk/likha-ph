<?php

namespace App\Support;

use App\Rules\NotDisposableEmail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Email;

class SignupEmailValidation
{
    /**
     * RFC-based email rule; spoofing + MX checks require PHP intl (Egulias throws otherwise).
     */
    private static function emailRule(): Email
    {
        $email = (new Email)->rfcCompliant();

        if (extension_loaded('intl')) {
            $email->preventSpoofing();
            if (config('signup.validate_mx', true)) {
                $email->validateMxRecord();
            }
        }

        return $email;
    }

    /**
     * Rules for the email field on new registration (unique against users.email).
     */
    public static function registrationEmailRules(): array
    {
        $email = self::emailRule();

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
        $email = self::emailRule();

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

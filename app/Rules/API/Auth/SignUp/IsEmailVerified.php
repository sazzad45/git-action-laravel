<?php

namespace App\Rules\API\Auth\SignUp;

use App\Domain\API\Utility\OTPPurpose;
use App\Domain\Independent\Models\OTP;
use Illuminate\Contracts\Validation\Rule;

class IsEmailVerified implements Rule
{
    private $email;

    /**
     * Create a new rule instance.
     *
     * @param string $email
     */
    public function __construct(string $email)
    {
        $this->email = $email;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(! OTP::where('identity', $this->email)
            ->where('otp', $value)
            ->where('purpose', OTPPurpose::VERIFY_EMAIL)
            ->where('status', 1)
            ->first())
            return false;

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation-ext.email_verification_information_missing');
    }
}

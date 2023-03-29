<?php

namespace App\Rules\API\Auth\SignUp;

use App\Domain\Independent\Models\FirebaseAuthToken;
use Illuminate\Contracts\Validation\Rule;

class IsMobileNumberFirebaseVerified implements Rule
{
    private $mobile_number;

    /**
     * Create a new rule instance.
     *
     * @param string $mobile_number
     */
    public function __construct(string $mobile_number)
    {
        $this->mobile_number = $mobile_number;
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
        if (! FirebaseAuthToken::where('identifier', $this->mobile_number)->where('user_uid', $value)->first())
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
        return trans('validation-ext.firebase_authentication_missing');
    }
}

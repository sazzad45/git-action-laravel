<?php

namespace App\Rules\API\Auth\ForgotPassword;

use App\Domain\UserRelation\Models\User;
use Illuminate\Contracts\Validation\Rule;

class EmailPhoneNoForSamePerson implements Rule
{
    private $phone_no;

    /**
     * Create a new rule instance.
     *
     * @param $phone_no
     */
    public function __construct($phone_no)
    {
        //
        $this->phone_no = $phone_no;
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
        $user = User::where('email', $value);

        if($user->count() == 0) return false;

        if($user->first()->phone_no != $this->phone_no) return false;

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.invalid_email_address');
    }
}

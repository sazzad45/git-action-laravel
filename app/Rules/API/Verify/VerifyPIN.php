<?php

namespace App\Rules\API\Verify;

use App\Domain\UserRelation\Models\User;
use Illuminate\Contracts\Validation\Rule;

class VerifyPIN implements Rule
{
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new rule instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        if($this->user->pin == "") return false;

        if (\Hash::check($value, $this->user->pin)) return true;

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('messages.pin_does_not_matched');
    }
}

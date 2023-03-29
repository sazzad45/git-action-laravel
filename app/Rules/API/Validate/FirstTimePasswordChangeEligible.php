<?php

namespace App\Rules\API\Validate;

use App\Domain\UserRelation\Models\User;
use Illuminate\Contracts\Validation\Rule;

class FirstTimePasswordChangeEligible implements Rule
{
    private $user;

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
        \Log::info($this->user);
        return $this->user->is_first_login == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('messages.not_eligible_for_first_time_password_change');
    }
}

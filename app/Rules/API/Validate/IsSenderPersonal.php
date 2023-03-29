<?php

namespace App\Rules\API\Validate;

use App\Domain\UserRelation\Models\UserType;
use Illuminate\Contracts\Validation\Rule;

class IsSenderPersonal implements Rule
{
    private $user;

    /**
     * Create a new rule instance.
     *
     * @param $user
     */
    public function __construct($user)
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
        $user_type = UserType::where('id', $this->user->user_type_id)->first();

        return $user_type->name == "Personal";
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('messages.sender_account_must_be_personal');
    }
}

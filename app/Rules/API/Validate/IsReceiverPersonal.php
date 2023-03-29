<?php

namespace App\Rules\API\Validate;

use App\Domain\UserRelation\Models\User;
use Illuminate\Contracts\Validation\Rule;

class IsReceiverPersonal implements Rule
{
    private $receiver_mobile_number;

    /**
     * Create a new rule instance.
     *
     * @param string $receiver_mobile_number
     */
    public function __construct(string $receiver_mobile_number)
    {
        $this->receiver_mobile_number = $receiver_mobile_number;
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
        $receiver = User::where('mobile_no', '=', $this->receiver_mobile_number)->first();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('messages.receiver_account_must_be_personal');
    }
}

<?php

namespace App\Rules\API\Validate;

use Illuminate\Contracts\Validation\Rule;

class IsOwnAccount implements Rule
{
    /**
     * @var string
     */
    private $own_account;
    /**
     * @var string
     */
    private $other_account;

    /**
     * Create a new rule instance.
     *
     * @param string $own_account
     * @param string $other_account
     */
    public function __construct(string $own_account, string $other_account)
    {
        //
        $this->own_account = $own_account;
        $this->other_account = $other_account;
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
        return !($this->own_account == $this->other_account);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('messages.cannot_transfer_money_to_own_account');
    }
}

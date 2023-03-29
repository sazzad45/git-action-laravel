<?php

namespace App\Rules\API\Auth\SignUp;

use Illuminate\Contracts\Validation\Rule;

class IsAgreed implements Rule
{
    /**
     * @var int
     */
    private $isAgreed;

    /**
     * Create a new rule instance.
     *
     * @param int $isAgreed
     */
    public function __construct(int $isAgreed)
    {
        $this->isAgreed = $isAgreed;
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
        if ($this->isAgreed == 1)
            return true;

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('messages.you_must_agree_to_toc');
    }
}

<?php


namespace App\Rules\API\Verify;

use App\Domain\UserRelation\Models\User;
use Illuminate\Contracts\Validation\Rule;

class VerifyPINorPassword implements Rule
{
    /**
     * @var User
     */
    private $user;
    private $messageStr;

    /**
     * Create a new rule instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->messageStr = trans('messages.pin_or_password_does_not_matched');
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
        if((int)config('basic_settings.pin_change_with_otp') == 1) {
            $this->messageStr = trans('messages.user_not_allowed_to_use_this_feature');
            return false;
        }

        if($this->user->pin == "") return false;

        if (\Hash::check($value, $this->user->pin)) {
            return true;
        }
        if(\Hash::check($value, $this->user->password)) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->messageStr;
    }
}

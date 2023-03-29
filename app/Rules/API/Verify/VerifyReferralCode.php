<?php

namespace App\Rules\API\Verify;

use App\Domain\UserRelation\Models\ReferralCode;
use App\Domain\UserRelation\Models\User;
use Illuminate\Contracts\Validation\Rule;

class VerifyReferralCode implements Rule
{
    /**
     * @var string
     */
    private $code;
    /**
     * @var User
     */
    private $user;
    private $referralCode;
    private $message_str;

    /**
     * Create a new rule instance.
     *
     * @param string $code
     * @param User $user
     */
    public function __construct(string $code, User $user)
    {
        //
        $this->code = $code;
        $this->user = $user;
        $this->message_str = trans('messages.referral_code_not_exists');
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
        if($this->alreadyAppliedMyCode())
        {
            return false;
        }

        if($this->codeExixts())
        {
            return false;
        }

        if($this->isNotMyCode())
        {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message_str;

    }

    private function codeExixts()
    {
        $code = ReferralCode::where('own_code', $this->code);

        if($code->count() == 0)
        {
            $this->message_str = trans('messages.referral_code_not_exists');
            return true;
        }
        $this->referralCode = $code->first();
        return false;
    }

    private function isNotMyCode()
    {
        if($this->referralCode->owner_id == $this->user->id)
        {
            $this->message_str = trans('messages.you_cannot_user_your_own_code');
            return true;
        }
        return false;
    }

    private function alreadyAppliedMyCode()
    {
        if(ReferralCode::where('owner_id', $this->user->id)->whereNotNull('referral_code')->count())
        {
            $this->message_str = trans('messages.you_already_applied_a_referral_code');
            return true;
        }
        return false;
    }
}

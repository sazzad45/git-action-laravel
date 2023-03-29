<?php

namespace App\Rules\API\Verify;

use App\Domain\UserRelation\Models\User;
use App\Domain\UserRelation\Models\UserVerificationDoc;
use Illuminate\Contracts\Validation\Rule;

class VerifyVerificationDoc implements Rule
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
        $doc = UserVerificationDoc::where('user_id', $this->user->id)->where('id', $value);

        if ($doc->count() ) {
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
        return trans('messages.verification_doc_doesnt_belongs_to_you');
    }
}

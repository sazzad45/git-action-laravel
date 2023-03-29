<?php

namespace App\Rules\API\Exists;

use App\Domain\UserRelation\Models\User;
use App\Domain\UserRelation\Models\UserVerificationDoc;
use Illuminate\Contracts\Validation\Rule;

class UploadVerificationDocumentOfType implements Rule
{
    /**
     * @var int
     */
    private $docId;
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new rule instance.
     *
     * @param int $docId
     * @param User $user
     */
    public function __construct(int $docId, User $user)
    {
        //
        $this->docId = $docId;
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
        $docs = UserVerificationDoc::where('user_id', $this->user->id)
            ->where('verification_docs_type_id', $this->docId);

        if($docs->count())
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
        return trans('messages.user_already_added_one_verification_document_of_this_type');
    }
}

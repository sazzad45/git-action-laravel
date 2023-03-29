<?php

namespace App\Rules\API\Auth\SignUp;

use App\Domain\Independent\Models\TLD;
use Illuminate\Contracts\Validation\Rule;

class EmailTLD implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $tld = TLD::where('tld', strtoupper(@end(explode('.', $attribute))))->whereStatus(1);
        if( $tld->count() )
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
        return trans('validation.email');
    }
}

<?php

namespace App\Rules\API\Verify;

use App\Domain\Independent\Models\OTP;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifyOTP implements Rule
{
    private $identifier;
    private $purpose;
    private $email;
    /**
     * Create a new rule instance.
     *
     * @param string $identifier
     * @param string $purpose
     */
    public function __construct(string $identifier, string $purpose, $email)
    {
        $this->identifier = $identifier;
        $this->email = $email;
        $this->purpose = $purpose;
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
        $otp = OTP::where(function($q){
            $q->where('identity', $this->identifier )->orWhere('identity', $this->email);
        })
            ->where('otp', $value)
            ->where('purpose', $this->purpose)
            ->where('status', 0)
            ->where('created_at', ">=", Carbon::now()->subMinutes(5)->format('Y-m-d H:i:s'))->first();
        if($otp)
        {
            $otp->status = 1;
            $otp->save();
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
        return trans('messages.otp_didnot_matched');
    }
}

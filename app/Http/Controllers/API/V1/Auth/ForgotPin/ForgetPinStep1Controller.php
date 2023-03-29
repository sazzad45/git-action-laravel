<?php

namespace App\Http\Controllers\API\V1\Auth\ForgotPin;

use App\Domain\API\Utility\OTPPurpose;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Auth\ForgotPassword\Step1\SendOTPRequest;
use App\Http\Traits\OTPTrait;
use App\Jobs\OTPForResetPassword;
use Illuminate\Support\Facades\Log;

class ForgetPinStep1Controller extends APIBaseController
{
    use OTPTrait;
    public function sendOTP(SendOTPRequest $request)
    {
        try {

            $this->send_otp($request->input('email'), $request->input('mobile_number'), OTPPurpose::PIN_RESET, $request);
            return $this->respondInJSON(200, [trans('messages.otp_sent')]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

<?php

namespace App\Http\Controllers\API\V1\Auth\ForgotPin;

use App\Domain\API\Utility\OTPPurpose;
use App\Domain\Independent\Models\OTP;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Auth\ForgotPassword\Step2\VerifyOTPRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ForgetPinStep2Controller extends APIBaseController
{
    public function verifyOTP(VerifyOTPRequest $request)
    {
        try {
            $email = $request->input('email');
            $mobile_number = $request->input('mobile_number');
            $otp = $request->input('otp');

            $otp = OTP::where('identity', $email ?? $mobile_number)
                ->where('otp', $otp)
                ->where('purpose', OTPPurpose::PIN_RESET)
                ->where('status', 0)
                ->where('created_at', ">=", Carbon::now()->subMinutes(5)->format('Y-m-d H:i:s'))->first();

            if($otp)
            {
                $otp->status = 1;
                $otp->save();
                return $this->respondInJSON(200, [trans('messages.otp_passed')]);
            }

            return $this->respondInJSON(422, [trans('messages.otp_didnot_matched')]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

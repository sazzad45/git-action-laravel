<?php

namespace App\Http\Controllers\API\V1\Auth\ForgotPassword;

use App\Constant\Security\UserActionText;
use App\Constant\UserTypeId;
use App\Domain\API\Utility\OTPPurpose;
use App\Domain\Independent\Models\OTP;
use App\Domain\UserRelation\Models\User;
use App\Events\Security\OTPVerificationFailedEvent;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Auth\ForgotPassword\Step2\VerifyOTPRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ForgetPasswordStep2Controller extends APIBaseController
{
    public function verifyOTP(VerifyOTPRequest $request)
    {
        // return $this->invalidResponse(['Forget password is under maintenance. This feature will be available soon. Please try again later.']);
        
        try {
            if($request->filled('mobile_number')) {
                $user = User::where('mobile_no', $request->input('mobile_number'))
                    ->where('user_type_id', UserTypeId::AGENT)
                    ->where('status', 1)
                    ->first();
            } else {
                $user = User::where('email', $request->input('email'))
                    ->where('user_type_id', UserTypeId::AGENT)
                    ->where('status', 1)
                    ->first();
            }

            if ( ! $user ) {
                return $this->invalidResponse([trans('messages.user_not_found')]);
            }

            if ($error = $this->userActionDailyLimitCrossed($user, trim(explode(',', $request->header('X-Forwarded-For'))[0]), UserActionText::FORGET_PASSWORD, 1)) {
                return $error;
            }

            $email = $request->input('email');
            $mobile_number = $request->input('mobile_number');
            $otp = $request->input('otp');

            $otp = OTP::where('identity', $email ?? $mobile_number)
                ->where('otp', $otp)
                ->where('purpose', OTPPurpose::PASSWORD_RESET)
                ->where('status', 0)
                ->where('created_at', ">=", Carbon::now()->subMinutes(5)->format('Y-m-d H:i:s'))->first();

            if($otp)
            {
                return $this->respondInJSON(200, [trans('messages.otp_passed')]);
            }

            if($user != "")
            {
                OTPVerificationFailedEvent::dispatch($user, json_encode(['OTP' => $request->otp]));
            }

            return $this->respondInJSON(422, [trans('messages.otp_didnot_matched')]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

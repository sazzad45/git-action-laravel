<?php

namespace App\Http\Controllers\API\V1\Auth\ForgotPassword;

use App\Constant\Security\UserActionText;
use App\Constant\UserTypeId;
use App\Domain\API\Utility\OTPPurpose;
use App\Domain\UserRelation\Models\User;
use App\Events\Security\UserActionEvent;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Auth\ForgotPassword\Step1\SendOTPRequest;
use App\Http\Traits\OTPTrait;
use App\Traits\EnsureSecurityTrait;
use Illuminate\Support\Facades\Log;

class ForgetPasswordStep1Controller extends APIBaseController
{
    use OTPTrait;
    use EnsureSecurityTrait;
    public function sendOTP(SendOTPRequest $request)
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

            $ipAddress = trim(explode(',', $request->header('X-Forwarded-For'))[0]);
            if ($error = $this->userActionDailyLimitCrossed($user, $ipAddress, UserActionText::FORGET_PASSWORD)) {
                return $error;
            }

            $this->send_otp($request->input('email'), $request->input('mobile_number'), OTPPurpose::PASSWORD_RESET, $request);
            UserActionEvent::dispatch($user, UserActionText::FORGET_PASSWORD, $ipAddress);
            $this->OTPMaxUsedBlockCheck($user, $request->input('email'), $request->input('mobile_number'));
            
            return $this->respondInJSON(200, [trans('messages.otp_sent')]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

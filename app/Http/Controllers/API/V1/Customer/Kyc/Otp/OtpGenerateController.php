<?php

namespace App\Http\Controllers\API\V1\Customer\Kyc\Otp;

use App\Channels\SmsChannel;
use App\Domain\API\Utility\OTPPurpose;
use App\Domain\Customer\Kyc\Library\CustomerKycBusinessValidation;
use App\Domain\UserRelation\Models\User;
use App\Domain\UserRelation\Models\UserVerificationDoc;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Customer\Kyc\Otp\GenerateRequest;
use App\Jobs\Otp\OtpGenerator;
use Illuminate\Support\Facades\Log;

class OtpGenerateController extends APIBaseController
{
    public function sendOtp(GenerateRequest $request)
    {
        try {
            $user = User::where('mobile_no', $request->mobile_number)->first();
            $latestKyc = UserVerificationDoc::where('user_id', $user->id)->latest('id')->first();
            if ( $error = (new CustomerKycBusinessValidation($user, $latestKyc))->validate() ) {
                return $error;
            }
            
            OtpGenerator::dispatchNow(
                $request,
                $user,
                OTPPurpose::KYC_VERIFICATION,
                $this->getChannels(),
                config('feature_sms_gateways.customer_kyc_verification')
            );

            $this->logActivity('Customer kyc verfication otp generated. Customer mobile number# ' . $user->mobile_no, $user, auth()->user());

            return $this->respondInJSON(200, [trans('messages.otp_sent')]);
        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function getChannels()
    {
        return [
            'mail',
            SmsChannel::class
        ];
    }
}

<?php


namespace App\Http\Controllers\API\V1\Auth\PasswordChange;

use App\Domain\API\Utility\OTPPurpose;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Auth\PasswordChange\Step1Request;
use App\Http\Traits\OTPTrait;
use App\Jobs\OTPForPasswordChange;
use Illuminate\Support\Facades\Log;

class PasswordChangeStep1Controller extends APIBaseController
{
    use OTPTrait;
    public function sendOTP(Step1Request $request)
    {
        try {
            $user = auth()->user();

            $this->send_otp($user->email, $user->mobile_no, OTPPurpose::PASSWORD_CHANGE, $request);

            return $this->respondInJSON(200, [trans('messages.otp_sent')]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

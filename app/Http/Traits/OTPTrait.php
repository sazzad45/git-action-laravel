<?php
namespace App\Http\Traits;

use App\Jobs\OTPForResetPassword;

trait OTPTrait {

    public function send_otp($email, $mobile, $otp_purpose, $request)
    {
        $otp = 123456;

        if(config('basic_settings.live_otp') == 1){
            $otp = random_int(100000, 999999);
        }


        OTPForResetPassword::dispatchNow(
            $email,
            $mobile,
            $otp,
            $otp_purpose,
            substr($request->header('User-Agent'), 0, 80),
            trim(explode(',', $request->header('X-Forwarded-For'))[0])
        );
    }
}

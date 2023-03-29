<?php

namespace App\Traits;


use App\Domain\UserRelation\Models\User;
use App\Jobs\SendOtp;
use App\Jobs\SendOtpInEmail;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

trait MiddlewareValidationTrait
{

    protected function middlewareValidation(Request $request, array $middlewareArray, string $otpKey)
    {
        $mobileNumber = (auth()->guest()) ? $request->input('mobile_number') : $request->user()->mobile_number;

        if (auth()->guest()) {
            if($request->filled('pin')) {

                $user = User::whereMobileNumber($request->input('mobile_number'))->firstOrFail();

                if(! Hash::check($request->input('pin'), $user->pin->pin)) {
                    return $this->unauthorizedResponse([trans('validation-ext.invalid_pin')]);
                }
            }
        } else {
            if($request->filled('pin')) {
                if(! Hash::check($request->input('pin'), $request->user()->pin->pin)) {
                    return $this->unauthorizedResponse([trans('validation-ext.invalid_pin')]);
                }
            }
        }

        if($request->filled('password')) {
            if(! Hash::check($request->input('password'), $request->user()->password)) {
                return $this->unauthorizedResponse([trans('validation-ext.invalid_password')]);
            }
        }

        if($request->filled('otp')) {
            $otp = OTP::whereMobileNumber($mobileNumber)
                ->whereOtp($request->input('otp'))
                ->wherePurpose($otpKey)
                ->whereStatus(false)
                ->first();

            if(! $otp)
                return $this->invalidResponse([ trans('validation-ext.otp_mismatch') ]);

            $otp->update(['status' => true]);
        }

        if(count($middlewareArray)) {
            foreach ($middlewareArray as $middleware) {
                if(! $request->has($middleware) || empty($request->input($middleware))) {

                    if($middleware == 'otp') {
                        if ($otpKey == 'ChangeEmail') {
                            SendOtpInEmail::dispatchNow(
                                $mobileNumber, $request->input('email'), random_int(10000, 99999), $otpKey
                            );

                            return $this->middlewareResponse([ trans('messages.otp_sent_via_email') ], $middleware);
                        } else {
                            SendOtp::dispatchNow(
                                $mobileNumber, random_int(10000, 99999), $otpKey
                            );
                        }

                        return $this->middlewareResponse([ trans('messages.otp_sent') ], $middleware);
                    }

                    return $this->middlewareResponse([], $middleware);
                }
            }
        }
    }
}

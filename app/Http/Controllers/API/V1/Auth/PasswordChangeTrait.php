<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Domain\FastPay\Constant\APIEndPoints;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait PasswordChangeTrait
{

    public function update_on_old_system($request)
    {

//        $response = Http::
//            timeout(config('internal_services.request_timeout'))
//            ->withToken(Cache::get(auth()->user()->mobile_no . '_old_token'))
//            ->post(APIEndPoints::PASSWORD_CHANGE, [
//                'password' => $request->input('password'),
//                'password_confirmation' => $request->input('password')
//            ]);
//
//        Log::info($response->body());
//        if ($response->failed() || !isset($response->json()['code']) || $response->json()['code'] != 200) {
//            throw new \Exception(isset($response->json()["messages"]) ? implode(' ', $response->json()["messages"]) : trans('internal_server_error'));
//        }

        return true;
    }

    public function reset_password_on_old_system($request, $mobile_no)
    {
//        $response = Http::
//            timeout(config('internal_services.request_timeout'))
//            ->withHeaders(["Authorization" => config('old_system.application.forgot_password_token')])
//            ->post(APIEndPoints::RESET_PASSWORD_FOR_NEW, ['mobile_no' => $mobile_no, 'password' => $request->input('password')]);
//
//        Log::info($response->body());
//        if ($response->failed() || !isset($response->json()['code']) || $response->json()['code'] != 200) {
//            throw new \Exception(isset($response->json()["messages"]) ? implode(' ', $response->json()["messages"]) : trans('internal_server_error'));
//        }

        return true;
    }
}

<?php

namespace App\Http\Controllers\API\V1\Auth\SignIn;

use App\Constant\UserStatusId;
use App\Constant\UserTypeId;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Auth\Login\LoginRequest;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoginController extends APIBaseController
{
    public function login(LoginRequest $request)
    {
        try {
            if (\Auth::attempt([
                'mobile_no' => $request->input('mobile_number'),
                'password' => $request->input('password'),
                'user_type_id' => UserTypeId::AGENT,
            ])) {


                $user = auth()->user();

                if($blockedUser = $this->checkUserHasAccess())
                    return $blockedUser;

                \DB::beginTransaction();

                \DB::statement("UPDATE oauth_access_tokens SET revoked = 1 WHERE user_id = $user->id");
                $passportToken = $user->createToken($user->mobile_no)->accessToken;
                \DB::commit();


                /** Removed Old System Connection */
                // $this->login_to_old_system($request);


                $responsePayload = [
                    'token' => $passportToken,
                    'user' => [
                        'is_first_login' => $user->is_first_login == 0
                    ]
                ];

                $this->logActivity('Logged In', $user);

                return $this->respondInJSON(200, [trans('messages.welcome')], $responsePayload);

            }

            return $this->respondInJSON(422, [trans('messages.credentials_do_not_match')]);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function checkUserHasAccess()
    {
        $message = [''];
        $user = auth()->user();

        if ($user->status == 0) {
            \Auth::logout();
            $message = ["Account is currently disable. Wait for sometime or contact with call center. Thanks"];
            return $this->respondInJSON(403, $message);
        }

        if ($user->user_status_id == UserStatusId::TEMPORARY_BLOCKED) {
            \Auth::logout();
            $message = ["Account is temporary blocked. Wait for sometime or contact with call center. Thanks"];
            return $this->respondInJSON(403, $message);
        }

        if ($user->user_status_id == UserStatusId::PERMANENTLY_CLOSED) {
            \Auth::logout();
            $message = ["Account is permanently closed. Contact with call center for details. Thanks"];
            return $this->respondInJSON(403, $message);
        }

        return false;
    }


}

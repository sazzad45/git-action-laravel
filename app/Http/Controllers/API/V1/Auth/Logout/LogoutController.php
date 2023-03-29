<?php

namespace App\Http\Controllers\API\V1\Auth\Logout;

use App\Http\Controllers\APIBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogoutController extends APIBaseController
{
    public function logout(Request $request)
    {
        try {
            return $this->respondInJSON(500, [trans('messages.app_down')]);
            if($request->user()->token()->revoke()) {
                $this->logActivity('Logged Out', $request->user());
                return $this->respondInJSON(200, [], null);
            }else{
                return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
            }

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

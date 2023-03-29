<?php

namespace App\Http\Controllers\API\V1\User\KYC;

use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\User\KYC\PinSetRequest;
use Illuminate\Support\Facades\Log;

class SetSecurityPinController extends APIBaseController
{
    public function store(PinSetRequest $request)
    {
        try {
            $user = auth()->user();

            $pin = \Hash::make($request->input('pin'));
            $user->pin = $pin;
            $user->update();

            $this->logActivity('Pin changed', $user);

            return $this->respondInJSON(200, [trans('messages.pin_set_success')]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

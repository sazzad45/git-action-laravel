<?php

namespace App\Http\Controllers\API\V1\User\Limit;

use App\Domain\Wallet\Models\Limit\UserLimitUsageList;
use App\Http\Controllers\APIBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LimitController extends APIBaseController
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            return $this->respondInJSON(200, [], (new UserLimitUsageList($user))->get());

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

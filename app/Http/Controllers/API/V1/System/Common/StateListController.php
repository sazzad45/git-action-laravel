<?php

namespace App\Http\Controllers\API\V1\System\Common;

use App\Domain\LocationManagement\Models\State;
use App\Http\Controllers\APIBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StateListController extends APIBaseController
{
    public function index(Request $request)
    {
        try {
            $states = State::active()->filterByCountry($request)->cursor();

            $responsePayload = [];
            foreach($states as $state){
                $responsePayload[] = [
                    'state_id' => $state->id,
                    'state_name' => $state->name
                ];
            }

            return $this->respondInJSON(200, [], ['states' => $responsePayload]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

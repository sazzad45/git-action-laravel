<?php

namespace App\Http\Controllers\API\V1\System\Common;

use App\Domain\LocationManagement\Models\City;
use App\Domain\LocationManagement\Models\Country;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\System\Common\City\CityRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CityController extends APIBaseController
{
    public function index(CityRequest $request)
    {
        try {
            $country = Country::with('states.cities')->find($request->input('country_id'));

            $responsePayload = [];

            foreach($country->states as $state){
                foreach($state->cities as $city){
                    $responsePayload[] = [
                        'city_id' => $city->id,
                        'city_name' => $city->name
                    ];
                }
            }

            return $this->respondInJSON(200, [], ['cities' => $responsePayload]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

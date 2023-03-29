<?php

namespace App\Http\Controllers\API\V1\System\Common;

use App\Domain\LocationManagement\Models\City;
use App\Http\Controllers\APIBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CityListController extends APIBaseController
{
    public function index(Request $request)
    {
        try {
            $cities = City::active()->filterByState($request)->cursor();

            $responsePayload = [];
            foreach($cities as $city){
                $responsePayload[] = [
                    'city_id' => $city->id,
                    'city_name' => $city->name
                ];
            }

            return $this->respondInJSON(200, [], ['cities' => $responsePayload]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

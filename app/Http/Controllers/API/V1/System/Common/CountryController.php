<?php

namespace App\Http\Controllers\API\V1\System\Common;

use App\Domain\LocationManagement\Models\Country;
use App\Http\Controllers\APIBaseController;
use Illuminate\Support\Facades\Log;

class CountryController extends APIBaseController
{
    public function index()
    {
        try {
            $countries = Country::whereStatus(1)->orderBy('name')->get();

            $responsePayload = [];

            foreach($countries as $country)
            {
                $responsePayload[] = [
                    'country_id' => $country->id,
                    'country_name' => $country->name
                ];
            }

            return $this->respondInJSON(200, [], [
                'countries' => $responsePayload,
                'default_country_id' => 103
            ]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

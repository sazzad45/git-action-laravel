<?php

namespace App\Http\Controllers\API\V1\User\KYC;

use App\Domain\LocationManagement\Models\City;
use App\Domain\LocationManagement\Models\Country;
use App\Domain\UserRelation\Models\UserProfile;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\User\KYC\UpdateGEOInfoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GEOInfoUpdateController extends APIBaseController
{
    public function update(UpdateGEOInfoRequest $request)
    {
        try {
            $user = auth()->user();

            $country = Country::find($request->input('country_id'));
            $city = City::with('state')->find($request->input('city_id'));

            $profile = UserProfile::where('user_id', $user->id);

            $userProfile = $profile->count() == 0 ?
                            (new UserProfile()) :
                            $profile->first();

            $userProfile->country_id = $country->id;
            $userProfile->state_id = $city->state->id;
            $userProfile->city_id = $city->id;
            $userProfile->address_line1 = $request->input('address_line1');

            if($profile->count() == 0)
                $user->profile()->save($userProfile);
            else
                $userProfile->update();

            $this->logActivity('Geo location set successfully', $user, $user, $userProfile->toArray());

            return $this->respondInJSON(200, [trans('messages.geo_location_set_success')]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

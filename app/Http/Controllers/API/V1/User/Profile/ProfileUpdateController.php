<?php


namespace App\Http\Controllers\API\V1\User\Profile;


use App\Domain\UserRelation\Models\User;
use App\Domain\UserRelation\Models\UserProfile;
use App\Domain\UserRelation\Models\UserVerificationDoc;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\User\Profile\UpdateRequest;
use Illuminate\Support\Facades\Log;

class ProfileUpdateController extends APIBaseController
{
    public function update(UpdateRequest $request)
    {
        try {
            $user = auth()->user();

            $profile = UserProfile::where('user_id', $user->id);

            if($profile->count() == 0) {
                $profile = new UserProfile();
                $profile->full_name = $request->input('full_name');
                $profile->surname = $request->input('surname');
                $profile->date_of_birth = $request->input('date_of_birth');
                $profile->country_id = $request->input('country_id');
                $profile->state_id = $request->input('state_id');
                $profile->address_line1 = $request->input('address_line1');
                $user->profile()->save($profile);

            }else{
                $profile = $profile->first();
                $profile->full_name = $request->input('full_name');
                $profile->surname = $request->input('surname');
                $profile->date_of_birth = $request->input('date_of_birth');
                $profile->country_id = $request->input('country_id');
                $profile->state_id = $request->input('state_id');
                $profile->address_line1 = $request->input('address_line1');

                $profile->update();
            }

            $this->logActivity('User profile updated', $user, $user, $profile->toArray());

            return $this->respondInJSON(200, [trans('messages.success_profile_update')], null);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}


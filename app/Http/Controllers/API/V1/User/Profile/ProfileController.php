<?php

namespace App\Http\Controllers\API\V1\User\Profile;

use App\Domain\UserRelation\Models\ReferralCode;
use App\Domain\UserRelation\Models\User;
use App\Domain\UserRelation\Models\UserBalance;
use App\Domain\UserRelation\Models\UserProfile;
use App\Domain\UserRelation\Models\UserVerificationDoc;
use App\Http\Controllers\APIBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfileController extends APIBaseController
{
    public function get(Request $request)
    {
        try {
            $user = auth()->user();

            $userProfile = UserProfile::with('country', 'state')
                                        ->where('user_id', $user->id)
                                        ->first();

            $responsePayload = [
                'profile' => [
                    "full_name" => $user->first_name ?? '',
                    "surname" => $user->last_name ?? '',
                    "date_of_birth" => $userProfile->date_of_birth ?? '',
                    "country" => $userProfile->country->name ?? '',
                    "state" => $userProfile->state->name ?? '',
                    "address_line1" => $userProfile->address_line1 ?? ''
                ]
            ];
            return $this->respondInJSON(200, [], $responsePayload);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

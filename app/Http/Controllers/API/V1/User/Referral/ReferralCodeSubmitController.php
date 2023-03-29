<?php

namespace App\Http\Controllers\API\V1\User\Referral;

use App\Domain\UserRelation\Models\ReferralCode;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\User\Referral\SubmitRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReferralCodeSubmitController extends APIBaseController
{
    public function submit(SubmitRequest $request)
    {
        try {
            $user = auth()->user();

            $referral = $request->user()->referralCodes()->first();

            if (! $referral ) {
                $referral = $this->generateReferral($request->user()->id);
            }

            $referral->update([
                'referral_code' => $request->input('code'),
                'referred_user_id' => $this->getReferredUserId($request->input('code'))
            ]);

            $this->logActivity('Referral code submitted', $user, $user, $referral->toArray());

            return $this->respondInJSON(200, ['Thanks for your submission.'], null);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    protected function generateReferral($ownerId)
    {
        $code = strtoupper(Str::random(8));

        while(ReferralCode::where('own_code', $code)->first()) {
            $code = strtoupper(Str::random(8));
        }

        $referral = ReferralCode::create([
            'own_code' => $code,
            'owner_id' => $ownerId
        ]);

        return $referral;
    }

    private function getReferredUserId($code)
    {
        return ReferralCode::where('own_code', $code)->first()->owner_id;
    }

}

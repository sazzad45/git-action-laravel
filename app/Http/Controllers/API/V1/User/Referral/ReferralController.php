<?php

namespace App\Http\Controllers\API\V1\User\Referral;

use App\Domain\UserRelation\Models\ReferralCode;
use App\Http\Controllers\APIBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReferralController extends APIBaseController
{
    public function get(Request $request)
    {
        try {
            $user = auth()->user();

            $referral = $request->user()->referralCodes()->first();

            if (! $referral ) {
                $referral = $this->generateReferral($request->user()->id);

                Log::critical($request->user()->id . ' - No referral code found. generated code : '.$referral->own_code);
            }

            $responsePayload = [
                'shareable_link' => 'http://fast-pay.cash',
                'referral_code' => $referral->own_code,
                'earned' => [
                    'amount' => 0,
                    'currency' => 'IQD'
                ],
                'pending' => [
                    'amount' => 0,
                    'currency' => 'IQD'
                ],
                'is_my_code_applied' => $referral->referral_code ? true : false
            ];

            return $this->respondInJSON(200, [], $responsePayload);

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
}



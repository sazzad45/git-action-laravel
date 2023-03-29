<?php

namespace App\Http\Controllers\API\V1\User;

use App\Domain\Accounting\Models\UserAccount;
use App\Domain\UserRelation\Models\ReferralCode;
use App\Domain\UserRelation\Models\User;
use App\Http\Controllers\APIBaseController;
use App\QrWarehouse;
use App\Traits\FeatureAccessTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BasicInfoController extends APIBaseController
{
    use FeatureAccessTrait;

    public function show(Request $request)
    {
        try {
            $user = auth()->user();

            try {
                $responsePayload = [
                    'user' => [
                        "first_name" => $user->original_name,
                        "last_name" => '',
                        "mobile_number" => $user->mobile_no,
                        "user_has_pin" => $user->pin != null ? true : false,
                        "kyc_status" => $user->is_kyc_verified,
                        "can_do_customer_kyc" => $this->ableToDoCustomerKyc($user),
                        "available_balance" => $this->getBalance($user),
                        "profile_thumbnail" => $user->avatar,
                        "qr_code_text" => $this->myQr($user)
                    ],
                    'settings' => [
                        'pin_change_with_otp' => (string) config('basic_settings.pin_change_with_otp')
                    ]
                ];
            }catch (\Exception $exception){
                return response()->json([
                    'code' => 401,
                    'messages' => ['Unauthenticated'],
                    'data' => null
                ],401);
            }

            return $this->respondInJSON(200, [], $responsePayload);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function getReferralCode(User $user)
    {
        $ref =  ReferralCode::where('owner_id', $user->id)->first();
        return $ref ? $ref->own_code : '' ;
    }


    private function getBalance(User $user)
    {
        $list = [];
        $accounts = UserAccount::with(
            'userAccountType',
            'accountBalances.currency'
        )
            ->where('user_id', $user->id)
            ->get();

        foreach($accounts as $account){

            foreach($account->accountBalances as $b)
            {
                $list [] = [
                    'account_type' => $account->userAccountType->name,
                    'account_no' => $account->account_no,
                    'currency' => $b->currency->code,
                    'balance' => $b->balance,
                    'old_balance' => false
                ];
            }
        }

        foreach ($list as $key => $balance){
            if(isset($list[$key]['balance'])){
                $list[$key]['balance'] = number_format($list[$key]['balance']);
            }
            $list[$key]['status'] = true;
        }

        return $list;
    }

    private function myQr(User $user)
    {
        $qrData = [
            'receiver' => [
                'name' => $user->original_name,
                'msisdn' => $user->mobile_no,
                "thumbnail" => $user->avatar
            ],
            'params' => [
                [
                    'field_type' => 'numeric',
                    'label' => 'Amount',
                    'key' => 'amount',
                    'value' => "0",
                    'placeholder' => "IQD",
                    'input' => true,
                    'type' => 'numeric',
                    'required' => true,
                    'is_read_only' => false
                ],
                [
                    'field_type' => 'textarea',
                    'label' => 'Write a note (optional)',
                    'key' => 'note',
                    'value' => "",
                    'placeholder' => '(if any)',
                    'input' => true,
                    'type' => 'alphanumeric',
                    'required' => false,
                    'is_read_only' => false
                ]
            ]
        ];

        $qr = QrWarehouse::firstOrCreate(
            ['user_id' => $user->id, 'type' => 'Profile'],
            [
                'uuid' => (string) Str::uuid(),
                'payload' => json_encode($qrData),
                'status' => false
            ]
        );

        return $this->_encrypt_string_with_prefix(
            $this->_encrypt_string(
                $qr->uuid
            )
        );
    }
}

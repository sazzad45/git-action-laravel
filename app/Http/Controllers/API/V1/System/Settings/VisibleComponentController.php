<?php

namespace App\Http\Controllers\API\V1\System\Settings;

use App\Http\Controllers\APIBaseController;
use App\Traits\FeatureAccessTrait;
use Illuminate\Support\Facades\Log;

class VisibleComponentController extends APIBaseController
{
    use FeatureAccessTrait;
    
    public function index()
    {
        try {

            $user = auth()->user();
            $user->load('agentProfile');

            $fastpayBalance = 'disable';
            $cardBalance = 'disable';

            $homeServices = [];

            $fastpayBalance = 'enable';
            $homeServices = [
                'wallet_service' => [
                    'send_money' => 'active',
                    'receive_money' => 'active',
                    'request_money' => 'active',
                    'b2b_transfer' => $this->isParentMainAgent(auth()->user()) ? 'active' : 'hide',
                    'cash_out' => $this->isParentMainAgent(auth()->user()) ? 'active' : 'hide'
                ],
                'recharge_service' => [
                    'mobile_card' => 'inactive',
                    'internet_card' => 'inactive',
                    'online_card' => 'inactive'
                ],
                'kyc_submission' => $this->ableToDoCustomerKyc(auth()->user()) ? 'active' : 'hide',
            ];

            $mobile = [
                'left_menu' => [
                    'account_settings' => 'show',
                    'statements' => 'show',
                    'limit' => 'show',
                    'refer_a_friend' => 'hide',
                    'promotions' => 'show',
                    'app_settings' => 'show',
                    'support_help' => 'show',
                    'logout' => 'show'
                ],
                'home' => $homeServices,
                'balance' => [
                    'fastpay' => $fastpayBalance,
                    'card' => $cardBalance
                ]
            ];

            $web = [];

            $data = [
                'mobile' => $mobile,
                'web' => $web
            ];

            return $this->respondInJSON(200, [], $data);
        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

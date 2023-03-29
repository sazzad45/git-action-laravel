<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\APIBaseController;
use Illuminate\Http\Request;

class VersionController extends APIBaseController
{
    public function index()
    {
        $channels = env("FIREBASE_CHANNELS") ?? [];
        if($channels && $channels != ''){
            $channels = explode(',',$channels);
        }
        return $this->respondInJSON(200, [], [
            'android' => config('mobile_app_version.android'),
            'ios' => config('mobile_app_version.ios'),
            'huawei' => config('mobile_app_version.huawei'),
            'firebase_channels' => $channels,
            'app_contents' => $this->appComponents()
        ]);
    }


    private function appComponents(): array
    {
        $mobile = [
            'left_menu' => [
                'account_settings' => 'show',
                'statements' => 'show',
                'limit' => 'show',
                'refer_a_friend' => 'show',
                'promotions' => 'show',
                'app_settings' => 'show',
                'support_help' => 'show',
                'logout' => 'show'
            ],
            'home' => [
                'wallet_service' =>[
                    'send_money' => 'active',
                    'receive_money' => 'active',
                    'request_money' => 'active'
                ],
                'recharge_service' => [
                    'mobile_card' => 'active',
                    'internet_card' => 'active',
                    'online_card' => 'active'
                ]
            ]
        ];

        $web = [

        ];

        return [
            'mobile' => $mobile,
            'web' => $web
        ];
    }
}

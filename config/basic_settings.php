<?php

return [
    'app_name' => 'agent',
    'company' => [
        'name' => 'FastPay',
        'mobile_no' => env('COMPANY_INFO_MOBILE_NO', '0662310000'),
        'email' => env('COMPANY_INFO_EMAIL', 'info@fast-pay.cash'),
        'website' => env('COMPANY_INFO_WEBSITE', 'www.fast-pay.cash'),
        'address' => env('COMPANY_INFO_ADDRESS', 'Allai Newroz Group Building, Baharka Road, Erbil Kurdistan, 44001, Iraq'),
        'facebook' => env('COMPANY_INFO_SM_FACEBOOK', 'https://www.facebook.com/fastpaycash/'),
        'youtube' => env('COMPANY_INFO_SM_YOUTUBE', 'https://www.youtube.com/channel/UC0RvUleviryXc7WdLMsAsmw'),
        'twitter' => env('COMPANY_INFO_SM_TWITTER', 'https://twitter.com/fastpaycash'),
        'instagram' => env('COMPANY_INFO_SM_INSTAGRAM', 'https://www.instagram.com/fastpaycash/'),
        'linkedin' => env('COMPANY_INFO_SM_LINKEDIN', 'http://linkedin.com/company/fastpayofficial'),
        'snapchat' => env('COMPANY_INFO_SM_SNAPCHAT', 'https://www.snapchat.com/add/fastpaycash')
    ],
    'pin_change_with_otp' => env('PIN_CHANGE_WITH_OTP', "0"),
    'currency_id' => 103,
    'currency_text' => "IQD",
    'duplicate_trx_time_diff' => env("DUPLICATE_TRX_TIME_DIFF", 120),
    'live_otp' => env('LIVE_OTP', 0),
];

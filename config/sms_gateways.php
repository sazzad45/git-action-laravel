<?php
    return [
        'live_sms' => env("SMS_SERVICE_ENABLE", false),
        'gateways' => [
            'sms_global' => 'SMSGlobal',
            'vonage_sms' => 'Vonage',
            'twilio_sms' => 'Twilio',
        ],
        'sms_global' => [
            'user' => env('SMS_GLOBAL_USER'),
            'stakeholder' => 'Fast-Pay',
            'password' => env('SMS_GLOBAL_PASSWORD')
        ],
        'vonage_sms' => [
            'api_url' => 'https://rest.nexmo.com/sms/json',
            'stakeholder' => 'Fast-Pay',
            'api_key' => env('VONAGE_SMS_API_KEY'),
            'api_secret' => env('VONAGE_SMS_API_SECRET')
        ],
        'twilio_sms' => [
            'stakeholder' => 'Fast-Pay',
            'sid' => env('TWILIO_SMS_SID'),
            'auth_token' => env('TWILIO_SMS_AUTH_TOKEN'),
        ],
    ];

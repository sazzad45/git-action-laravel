<?php

return [
    'hw_appid' => env('HW_APPID', '103836849'),
    'hw_appsecret' => env('HW_APPSECRET', 'a4fcc61ae9474650cdf25171b8053b58fa4136fe14f3c7f78caae56067af6931'),

    ### Business Push Token,For common push msg ####
    'hw_push_token_arr' => env('HW_PUSH_TOKEN_ARR', ''),

    ### Business Push Token,For IOS apn ####
    'apn_push_token_arr' => env('APN_PUSH_TOKEN_ARR', ''),

    ### Business Push Token,For webpush ####
    'webpush_push_token_arr' => env('WEBPUSH_PUSH_TOKEN_ARR', ''),

    ### FAST APP INFO : different from ordinal app####
    'hw_fast_appid' => env('HW_FAST_APPID', ''),
    'hw_fast_appsecret' => env('HW_FAST_APPSECRET', ''),
    'hw_fast_push_token' => env('HW_FAST_PUSH_TOKEN', ''),

    ### Token Server for push msg and top subscribe/unsubscribe ####
    'hw_token_server' => env('HW_TOKEN_SERVER', "https://oauth-login.cloud.huawei.com/oauth2/v2/token"),
    ### Push Server address ####
    'hw_push_server' => env('HW_PUSH_SERVER', "https://push-api.cloud.huawei.com/v1/{appid}/messages:send"),

    ### Topic Server address ####
    'hw_topic_subscribe_server' => env('HW_TOPIC_SUBSCRIBE_SERVER', "https://push-api.cloud.huawei.com/v1/{appid}/topic:subscribe"),
    'hw_topic_unsubscribe_server' => env('HW_TOPIC_UNSUBSCRIBE_SERVER', "https://push-api.cloud.huawei.com/v1/{appid}/topic:unsubscribe"),
    'hw_topic_query_subscriber_server' => env('HW_TOPIC_QUERY_SUBSCRIBER_SERVER', "https://push-api.cloud.huawei.com/v1/{appid}/topic:list"),
];

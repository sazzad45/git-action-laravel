<?php


namespace App\Huawei;


class HuaweiConfig
{
    // ORDINAL APP
    public $HW_APPID;
    public $HW_APPSECRET;
    public $HW_PUSH_TOKEN_ARR;
    public $APN_PUSH_TOKEN_ARR;
    public $WEBPUSH_PUSH_TOKEN_ARR;

    // FAST APP
    public $HW_FAST_APPID;
    public $HW_FAST_APPSECRET;
    public $HW_FAST_PUSH_TOKEN;

    public $HW_TOKEN_SERVER;
    public $HW_PUSH_SERVER;
    public $HW_TOPIC_SUBSCRIBE_SERVER;
    public $HW_TOPIC_UNSUBSCRIBE_SERVER;
    public $HW_TOPIC_QUERY_SUBSCRIBER_SERVER;

    public $HW_DEFAULT_LOG_LEVEL = 3;

    public function __construct()
    {
        $this->HW_APPID = config('huwaei.hw_appid');
        $this->HW_APPSECRET = config('huwaei.hw_appsecret');

        $this->HW_TOKEN_SERVER = config('huwaei.hw_token_server');
        $this->HW_PUSH_SERVER = config('huwaei.hw_push_server');

        $this->HW_TOPIC_SUBSCRIBE_SERVER = config('huwaei.hw_topic_subscribe_server');
        $this->HW_TOPIC_UNSUBSCRIBE_SERVER = config('huwaei.hw_topic_unsubscribe_server');
        $this->HW_TOPIC_QUERY_SUBSCRIBER_SERVER = config('huwaei.hw_topic_query_subscriber_server');

        $this->HW_PUSH_TOKEN_ARR = config('huwaei.hw_push_token_arr');
        $this->APN_PUSH_TOKEN_ARR = config('huwaei.apn_push_token_arr');
        $this->WEBPUSH_PUSH_TOKEN_ARR = config('huwaei.webpush_push_token_arr');
        //$this->HW_DEFAULT_LOG_LEVEL = config('huwaei.hw_appid');
        $this->HW_FAST_APPID = config('huwaei.hw_fast_appid');
        $this->HW_FAST_APPSECRET = config('huwaei.hw_fast_appsecret');
        $this->HW_FAST_PUSH_TOKEN = config('huwaei.hw_fast_push_token');
    }
}

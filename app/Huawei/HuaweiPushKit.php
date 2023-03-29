<?php


namespace App\Huawei;


class HuaweiPushKit
{
    private $appid;
    private $appsecret;
    private $token_expiredtime;
    private $access_token;
    private $validate_only;
    private $hw_token_server;
    private $hw_push_server;
    private $fields;

    public function __construct($appid, $appsecret, $hw_token_server, $hw_push_server)
    {
        $this->appid = $appid;
        $this->appsecret = $appsecret;
        $this->hw_token_server = $hw_token_server;
        $this->hw_push_server = $hw_push_server;
        $this->token_expiredtime = null;
        $this->accesstoken = null;
        $this->validate_only = false;
    }

    public function appid($value)
    {
        $this->appid = $value;
    }

    public function appsecret($value)
    {
        $this->appsecret = $value;
    }

    public function validate_only($value)
    {
        $this->validate_only = $value;
    }

    public function getApplicationFields()
    {
        $keys = array(
            'appid',
            'appsecret',
            'hw_token_server',
            'hw_push_server',
            'validate_only',
            'accesstoken',
            'token_expiredtime'
        );
        foreach ($keys as $key) {
            if (isset($this->$key)) {
                $this->fields[$key] = $this->$key;
            }
        }
        return $this->fields;
    }



    private function is_token_expired()
    {
        if (empty($this->accesstoken)) {
            return true;
        }
        if (time() > $this->token_expiredtime) {
            return true;
        }
        return false;
    }

    private function refresh_token()
    {
        $result = json_decode($this->curl_https_post($this->hw_token_server, http_build_query(array(
            "grant_type" => "client_credentials",
            "client_secret" => $this->appsecret,
            "client_id" => $this->appid
        )), array(
            "Content-Type: application/x-www-form-urlencoded;charset=utf-8"
        )));
        if ($result == null || ! array_key_exists("access_token", (array) $result)) {
            return null;
        }
        $this->accesstoken = $result->access_token;
        $this->token_expiredtime = time() + $result->expires_in;
        return $this->access_token;
    }

    private function curl_https_post($url, $data = array(), $header = array())
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // resolve SSL: no alternative certificate subject name matches target host name
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // check verify
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, 1); // regular post request
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // Post submit data

        $ret = @curl_exec($ch);
        if ($ret === false) {
            return null;
        }
        $info = curl_getinfo($ch);
        curl_close($ch);

        \Log::info($ret);

        return $ret;
    }

    /**
     * push_send_msg for push msg
     */
    public function push_send_msg($msg)
    {
        $body = [
            "validate_only" => $this->validate_only,
            "message" => $msg
        ];

        if ($this->is_token_expired()) {
            $this->refresh_token();
        }

        if (empty($this->accesstoken)){
            return null;
        }

        $result = json_decode(
            $this->curl_https_post(
                str_replace('{appid}', $this->appid, $this->hw_push_server),
                json_encode($body), array(
                    "Content-Type: application/json",
                    "Authorization: Bearer {$this->accesstoken}"
                ) // Use bearer auth
            ));

        // $result ==> eg: {"code":"80000000","msg":"Success","requestId":"157278422841836431010901"}

        if (! empty($result)) {
            $arrResult = json_decode(json_encode($result), true);
            \Log::info($arrResult);
            if (!empty($arrResult["code"]) && !in_array($arrResult["code"], array( "80000000",80000000))) {
            }
        }

        return $result;
    }

    /**
     * common_send_msg for topic msg/other
     */
    public function common_send_msg($msg)
    {
        if ($this->is_token_expired()) {
            $this->refresh_token();
        }

        if (empty($this->accesstoken)){
            return null;
        }

        $result = json_decode(
            $this->curl_https_post(
                str_replace('{appid}', $this->appid, $this->hw_push_server),
                json_encode($msg), array(
                    "Content-Type: application/json",
                    "Authorization: Bearer {$this->accesstoken}"
                ) // Use bearer auth
            ));

        // $result ==> eg: {"code":"80000000","msg":"Success","requestId":"157278422841836431010901"}

        if (! empty($result)) {
            $arrResult = json_decode(json_encode($result), true);
            if (isset($arrResult["code"]) && $arrResult["code"] != "80000000") {}
        }

        return $result;
    }
}

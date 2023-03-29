<?php

namespace App\Domain\System;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SMSManager
{
    private $to;
    private $message;
    private $gateway;
    private $nonEnglish;

    public function __construct($to = "", $message = "", $nonEnglish = false, $gateway = "SMSGlobal")
    {
        $this->to = $to;
        $this->message = $message;
        $this->gateway = $gateway;
    }

    private function checkLiveEnv()
    {
        if(config('sms_gateways.live_sms'))
        {
            return true;
        }
        return false;
    }

    private function getMaxSplit()
    {
        $maxsplit = 1;
        $maxsplit = (int) (ceil(strlen($this->message)/160));

        if($maxsplit > 2) {
            Log::warning("A Long SMS generated.");
            Log::warning($this->message);
        }

        return $maxsplit;
    }

    private function getMsisdn()
    {
        return str_replace("+", "", $this->to);
    }

    private function sendBySMSGlobal()
    {
        try{
            $smsUSER = config('sms_gateways.sms_global.user');
            $smsStakeHolder= config('sms_gateways.sms_global.stakeholder');
            $smsPassword = config('sms_gateways.sms_global.password');
            $otpMessage = $this->message;
            if($this->nonEnglish){
                $otpMessage = base64_encode($this->message);
            }

            $maxsplit = $this->getMaxSplit();
            $msisdn = $this->getMsisdn();

            $curl = curl_init();

            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL =>
                        'https://api.smsglobal.com/http-api.php?action=sendsms&user='.
                        $smsUSER.'&password='.
                        $smsPassword.'&from='.
                        $smsStakeHolder.'&to='.
                        $msisdn.'&text='.urlencode($otpMessage).'&maxsplit='.$maxsplit,
                    CURLOPT_USERAGENT => 'Sample cURL Request'
                )
            );

            $resp = curl_exec($curl);
            Log::info("Sms Send By SMS-Global");
            Log::info($resp);
            curl_close($curl);
            
        }catch (\Exception $e){
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
        }
    }

    private function sendByVonage()
    {
        try {
            $this->getMaxSplit();
            $msisdn = $this->getMsisdn();

            $params = [
                'from' => config('sms_gateways.vonage_sms.stakeholder'),
                'text' => $this->message,
                'to' => $msisdn,
                'api_key' => config('sms_gateways.vonage_sms.api_key'),
                'api_secret' => config('sms_gateways.vonage_sms.api_secret')
            ];
            $resp = Http::post(config('sms_gateways.vonage_sms.api_url'), $params);
            Log::info("Sms Send By Vonage");
            Log::info($resp->json());

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
        }
    }

    private function sendByTwilio()
    {
        try {
            $this->getMaxSplit();
            $msisdn = $this->getMsisdn();
    
            $sid = config('sms_gateways.twilio_sms.sid');
            $token = config('sms_gateways.twilio_sms.auth_token');
            $client = new \Twilio\Rest\Client($sid, $token);
    
            $resp = $client->messages->create(
                $msisdn,
                [
                    'from' => config('sms_gateways.twilio_sms.stakeholder'),
                    'body' => $this->message
                ]
            );
            Log::info("Sms Send By Twilio");
            Log::info($resp);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
        }
    }

    public function send()
    {
        if ($this->checkLiveEnv() == false) {
            return true;
        }

        if ($this->gateway == config('sms_gateways.gateways.sms_global')) {
            $this->sendBySMSGlobal();
        } else if ($this->gateway == config('sms_gateways.gateways.vonage_sms')) {
            $this->sendByVonage();
        } else if ($this->gateway == config('sms_gateways.gateways.twilio_sms')) {
            $this->sendByTwilio();
        }
    }
}

<?php

namespace App\Domain\FastPay\API\User;

use App\Domain\FastPay\API\CommonResponse;
use App\Domain\FastPay\API\FastPayOldApi;
use App\Domain\FastPay\Constant\APIEndPoints;
use Illuminate\Support\Facades\Log;

class BalanceInfo implements FastPayOldApi
{
    use CommonResponse;

    private $response = "";

    public function __construct()
    {

    }

    public function call(string $token)
    {

        try{
            $curl = curl_init();

            if(config('internal_services.proxy_enable') == true)
            {
                curl_setopt($curl, CURLOPT_PROXY, config('internal_services.proxy_url'));
            }

            curl_setopt_array($curl, array(
                CURLOPT_URL => APIEndPoints::BALANCE_INFO.'?lang='.app()->getLocale(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 50,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    "Authorization: Bearer {$token}"
                ),
            ));


            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if($httpcode == 401){
                throw new \Exception('Unauthenticated',401);
            }

            $this->response = json_decode($response, true);

            $this->fp_status_code = $this->response['code'];
            $this->fp_message = $this->response['messages'];
            $this->fp_data = $this->response['data'];

        }catch (\Exception $e){
            \Log::error($e);
            if($e->getCode() == 401){
                throw new \Exception('Unauthenticated',401);
            }

        }

        $this->response = json_decode($response, true);

        $this->fp_status_code = $this->response['code'];
        $this->fp_message = $this->response['messages'];
        $this->fp_data = $this->response['data'];

        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }
}

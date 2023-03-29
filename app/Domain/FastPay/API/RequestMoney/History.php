<?php


namespace App\Domain\FastPay\API\RequestMoney;


use App\Domain\FastPay\API\CommonResponse;
use App\Domain\FastPay\API\FastPayOldApi;
use App\Domain\FastPay\Constant\APIEndPoints;

class History implements FastPayOldApi
{
    use CommonResponse;
    private array $param;
    private $response = "";

    public function __construct(array $param)
    {
        $this->param = $param;
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
                CURLOPT_URL => APIEndPoints::REQUEST_MONEY_HISTORY,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 50,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    "Authorization: Bearer {$token}"
                ),
            ));

            $response = curl_exec($curl);
            \Log::info($response);
            curl_close($curl);

            $this->response = json_decode($response, true);

            $this->fp_status_code = $this->response['code'];
            $this->fp_message = $this->response['messages'];
            $this->fp_data = $this->response['data'];

            if(isset($this->response['data']['histories'])){
                $this->response['data']['history'] =  $this->response['data']['histories'];
                unset($this->response['data']['histories']);
            }
        }catch (\Exception $e){
            \Log::error($e);
        }
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }
}

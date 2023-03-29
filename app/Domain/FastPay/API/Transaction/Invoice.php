<?php


namespace App\Domain\FastPay\API\Transaction;


use App\Domain\FastPay\API\CommonResponse;
use App\Domain\FastPay\API\FastPayOldApi;
use App\Domain\FastPay\Constant\APIEndPoints;
use Illuminate\Support\Facades\Log;

class Invoice implements FastPayOldApi
{
    use CommonResponse;

    private $response = "";
    private $invoiceId;

    public function __construct($invoiceId)
    {
        $this->invoiceId = $invoiceId;
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
                CURLOPT_URL => $this->getAPIEndpoint($this->invoiceId),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 50,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    "Authorization: Bearer {$token}"
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            $this->response = json_decode($response, true);

            Log::info($this->response);

            $this->fp_status_code = $this->response['code'];
            $this->fp_message = $this->response['messages'];
            $this->fp_data = $this->response['data'];

            if(isset($this->response['data']) && empty($this->response['data'])){
                $this->response['data'] = null;
            } elseif(isset($this->response['data'][0])){
                $this->response['data'] = $this->response['data'][0];

                if((isset($this->response['data']['recipient']['avatar'])) && ($this->response['data']['recipient']['avatar'] == '' || $this->response['data']['recipient']['avatar'] == null)){
                    $this->response['data']['recipient']['avatar'] = secure_asset('person.png');
                }

//                if(isset($this->response['data']) && !empty($this->response['data'])){
//                    //$this->response['data']['bar_code'] = uniqid();
//                }
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

    private function getAPIEndpoint($invoiceId)
    {
        return APIEndPoints::INVOICE."?invoice_id=".$invoiceId.'&lang='.app()->getLocale();
    }
}

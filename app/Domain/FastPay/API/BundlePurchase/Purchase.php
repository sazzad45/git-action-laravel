<?php


namespace App\Domain\FastPay\API\BundlePurchase;


use App\Domain\FastPay\API\CommonResponse;
use App\Domain\FastPay\API\FastPayOldApi;
use App\Domain\FastPay\Constant\APIEndPoints;

class Purchase implements FastPayOldApi
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
            $startTime = date('Y-m-d H:i:s');

            $curl = curl_init();

            if(config('internal_services.proxy_enable') == true)
            {
                curl_setopt($curl, CURLOPT_PROXY, config('internal_services.proxy_url'));
            }

            curl_setopt_array($curl, array(
                CURLOPT_URL => APIEndPoints::BUNDLE_PURCHASE,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => config('internal_services.request_timeout'),
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($this->param),
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    "Authorization: Bearer {$token}"
                ),
            ));

            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            \Log::info($response);

            $endTime = date('Y-m-d H:i:s');
            $tookSeconds = (strtotime($endTime) - strtotime($startTime));
            \Log::info('CL OLD - Bundle Purchase : '.request()->user()->mobile_no. ' Start Time '.$startTime. " End Time : ".$endTime. ' took '.$tookSeconds . ' HTTPCode '.$httpcode);

            curl_close($curl);
            $this->response = json_decode($response, true);

            if(isset($this->response['code'])){
                $this->fp_status_code = $this->response['code'];
            }
            if(isset($this->response['messages'])){
                $this->fp_message = $this->response['messages'];
            }
            if(isset($this->response['data'])){
                $this->fp_data = $this->response['data'];
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

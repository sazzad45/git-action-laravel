<?php


namespace App\Domain\Transaction\Library\BundlePurchase;


use App\Http\Requests\API\Wallet\Transaction\BundlePurchase\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BundleCard
{
    public string $cardNumber;
    public float $amount;
    public bool $status;

    public int $operator_id;
    public int $bundle_id;

    public bool $has400Errors = false;
    public array $message400 = [];


    public function __construct(int $operator_id, int $bundle_id)
    {
        $this->operator_id = $operator_id;
        $this->bundle_id = $bundle_id;
    }

    public function getMobileNumberForGL(): string
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('internal_services.thirdparty.base_url')."/api/bundle/merchant_info/{$this->bundle_id}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json"
            ),
        ));

        $response = curl_exec($curl);

        /*
         * {
                "code": 200,
                "messages": [
                    "Success"
                ],
                "data": {
                    "operator_name": "Netflix",
                    "merchant_account": "+964900090009",
                    "amount": 39800
                }
            }
         */

        curl_close($curl);

        $responseArray = json_decode($response, true);

        $this->amount = $responseArray['data']['amount'];

        return $responseArray['data']['merchant_account'] ?? "";
    }

    public function bookCardNumber(PurchaseRequest $request)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('internal_services.thirdparty.base_url')."/api/bundle/activation",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $request->all() + ['purchase_type' => 'reservation'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        /*
         * {
                "code": 200,
                "messages": [
                    "Success"
                ],
                "data": {
                    "card_id": 1,
                    "bundle_id": 111
                }
            }
         */

        $responseArray = json_decode($response, true);

        Log::info($responseArray);

        if ($responseArray['code'] == 200)
            $this->cardNumber = $responseArray['data']['card_id'];
        else if($responseArray['code'] == 400) {
            $this->has400Errors = true;
            $this->message400 = $responseArray['messages'];
            $this->cardNumber = "";
        }else
            $this->cardNumber = "";
    }

    public function updateBookingStatus(string $transactionID = null): void
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('internal_services.thirdparty.base_url')."/api/bundle/card/callback",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $this->getParams($transactionID),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }

    private function getParams(string $transactionID = null): array
    {
        return array(
            'card_id' => $this->cardNumber,
            'transaction_id' => $transactionID,
            'status' => is_null($transactionID) ? 0 : 1
        );
    }




}

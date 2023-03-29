<?php

namespace Tests\Feature;


use App\Constant\APIEndPoints;
use App\Domain\Accounting\Models\AccountBalance;
use Tests\TestCase;

class APIEndPointTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {

        $response = $this->withHeaders([
            'Accept'  => 'application/json',
            'Content-Type' => 'application/json'
        ])->json(
            'POST',
            APIEndPoints::V1_LOGIN_SIGN_IN,
            [
                'mobile_number' => '+9641829298814',
                'password' => 'Password100@'
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                "code",
                "messages",
                "data" => [
                    "token",
                    "user" => [
                        "is_first_login"
                    ]
                ]
            ]);
        $response =  json_decode($response->getContent(),true);

        $token = $response['data']['token'];


        $myBalance = AccountBalance::find(1109);
        $myBalance1 = $myBalance->balance;




        $limitAPIResponse = $this->withHeaders([
            'Accept'  => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token
        ])->json('GET', APIEndPoints::V1_USER_TRANSACTIONAL_LIMIT);

        \Log::info("Limit API response 1");
        \Log::info($limitAPIResponse->getContent());





        $acBalance = AccountBalance::find(61);
        $recBalance1 = $acBalance->balance;



        $cashInResponse = $this->withHeaders([
            'Accept'  => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token
        ])->json('POST', APIEndPoints::V1_WALLET_TRANSACTION_CASH_IN_STEP2, [
            'receiver_mobile_number' => '+9641914955310',
            'amount' => 500,
            'pin' => 1234
        ]);

        //\Log::info("CashIn response 1");
        $cashInResponse = json_decode($cashInResponse->getContent(),true);


        $invoiceId = $cashInResponse['data']['summary']['invoice_id'];
        \Log::info("Invoice Id :".$invoiceId);


        $acBalance = AccountBalance::find(61);
        $recBalance2 = $acBalance->balance;
        \Log::info("Receiver Balance ".$recBalance1. ' '.$recBalance2. ' = '.($recBalance2 - $recBalance1));

        $myBalance = AccountBalance::find(1109);
        $myBalance2 = $myBalance->balance;

        \Log::info("My Balance ". $myBalance1.' '. $myBalance2. ' = '.($myBalance2 - $myBalance1));



        $limitAPIResponse2 = $this->withHeaders([
            'Accept'  => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token
        ])->json('GET', APIEndPoints::V1_USER_TRANSACTIONAL_LIMIT);

        \Log::info("Limit API response 2");
        \Log::info($limitAPIResponse2->getContent());




        $invoiceAPIResponse = $this->withHeaders([
            'Accept'  => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token
        ])->json('GET', APIEndPoints::V1_USER_TRANSACTION_INVOICE, ['invoice_id' => $invoiceId]);

        \Log::info("Invoice API response 1");
        $cashInResponse = json_decode($invoiceAPIResponse->getContent(),true);
        \Log::info($cashInResponse);







    }
}

<?php

namespace App\Http\Controllers\API\V1\Wallet\Transaction\RequestMoney;

use App\Domain\UserRelation\Models\User;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Wallet\Transaction\RequestMoney\SummaryRequest;
use Illuminate\Support\Facades\Log;

class Step1Controller extends APIBaseController
{
    public function requestMoney(SummaryRequest $request)
    {
        try {
            $responseData = $this->generateSummary($request);
            return $this->respondInJSON(200, [], $responseData);
        } catch (\Exception $e) {

            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function generateSummary(SummaryRequest $request)
    {
        $amount = $request->input('amount');
        $charge = 0;
        $total = $amount + $charge;

        $user = User::where('mobile_no', $request->input('requestee_mobile_number'))
                ->first();

        return [
            "summary" => [
                "nature_of_transaction" => "Transfer",
                "recipient" => [
                    "name" => $user->name,
                    "mobile_number" => $user->mobile_no,
                    "avatar" => $user->avatar
                ],
                "card" => null,
                "amount" => $amount . " " . config('fastpay.currency_text'),
                "charge" => $charge . " " . config('fastpay.currency_text'),
                "total_payable" => $total . " " . config('fastpay.currency_text')
            ]
        ];
    }

}

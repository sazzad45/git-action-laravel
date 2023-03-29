<?php

namespace App\Http\Controllers\API\V1\Wallet\Transaction\CashOut;

use App\Constant\TransactionType;
use App\Domain\Transaction\Library\CashOut\CashOutTransferRequestValidation;
use App\Domain\UserRelation\Models\User;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Wallet\Transaction\CashOut\SummaryRequest;
use App\Traits\CommissionTrait;
use App\Traits\TransactionTrait;
use Illuminate\Support\Facades\Log;

class Step1Controller extends APIBaseController
{
    use CommissionTrait;
    use TransactionTrait;

    public function summary(SummaryRequest $request)
    {
        try {
            $sender = $this->fetchUserByKeyVal('id', auth()->user()->id);
            $receiver = $this->fetchUserByKeyVal('mobile_no', $request->receiver_mobile_number);

            $chargeAmount = 0;
            $charge = $this->calculateCommissionOrCharge($sender, $receiver, $request->amount, TransactionType::CASH_OUT);
            if ($charge != false) {
                $chargeAmount = $charge['amount'];
            }

            if ($error = (new CashOutTransferRequestValidation($sender, $receiver, $request->amount, 0, config('basic_settings.currency_text'), TransactionType::CASH_OUT))->validate())
                return $error;

            return $this->respondInJSON(200, [], $this->generateSummary($receiver, $request->amount, $chargeAmount));
        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function generateSummary(User $receiver, $amount, $chargeAmount)
    {
        $total = $amount + $chargeAmount;
        return [
            "summary" => [
                "nature_of_transaction" => "Transfer",
                "recipient" => [
                    "name" => $receiver->name,
                    "mobile_number" => $receiver->mobile_no,
                    "avatar" => $receiver->avatar
                ],
                "amount" => number_format((int)$amount) . " " . config('basic_settings.currency_text'),
                "charge" => number_format((int)$chargeAmount) . " " . config('basic_settings.currency_text'),
                "total_payable" => number_format((int)$total) . " " . config('basic_settings.currency_text')
            ]
        ];
    }
}

<?php

namespace App\Http\Controllers\API\V1\Wallet\Transaction\CashIn;

use App\Channels\FcmPushChannel;
use App\Constant\TransactionType;
use App\Constant\TransactionTypeCode;
use App\Constant\UserAccountType;
use App\Domain\Independent\Models\Currency;
use App\Domain\Transaction\Library\CashIn\CashInRequestValidation;
use App\Domain\Transaction\Library\CashIn\Param;
use App\Domain\Transaction\Library\TransactionGenerator;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\UserRelation\Models\User;
use App\Domain\Wallet\Models\Limit\LimitCheckerParam;
use App\Domain\Wallet\Models\Limit\LimitUpdater;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Wallet\Transaction\CashIn\VerifyRequest;
use App\Jobs\TransactionConsumeUpdate;
use App\Notifications\Transactions\CashIn\Received;
use App\Notifications\Transactions\CashIn\Sent;
use App\Traits\CashBackTrait;
use App\Traits\CommissionTrait;
use App\Traits\RewardTrait;
use App\Traits\TransactionTrait;

use Illuminate\Support\Facades\Log;

class Step2Controller extends APIBaseController
{
    use CommissionTrait;
    use TransactionTrait;
    use CashBackTrait;
    use RewardTrait;

    public function execute(VerifyRequest $request)
    {
        try {
            $sender = $this->fetchUserObject(auth()->user()->mobile_no);
            $receiver = $this->fetchUserObject($request->input('receiver_mobile_number'));

            $amount = $request->amount;
            $chargeAmount = 0;
            $commission = null;
            $charge = $this->calculateCommission($sender, $receiver, $request->amount, TransactionType::CASH_IN, UserAccountType::FASTPAY_SAVINGS_ACCOUNT);
            if($charge != false){
                $chargeAmount = $this->getCommissionAmount($charge, $amount);
                $commission = $charge;
            }

            if ($error = (new CashInRequestValidation($sender, $receiver, $request->amount, $chargeAmount, config('basic_settings.currency_text'), TransactionType::CASH_IN))->validate())
                return $error;

            $response = $this->doTheTransaction($sender, $receiver, $request, $commission);

            if ($response->status == false)
                return $response->message;

            $this->processCashBack(
                $sender,
                $receiver,
                $request->amount,
                TransactionType::CASH_IN,
                UserAccountType::FASTPAY_SAVINGS_ACCOUNT,
                UserAccountType::FASTPAY_SAVINGS_ACCOUNT,
                $response->transaction
            );

            $this->processReward(
                $sender,
                $receiver,
                $request->amount,
                TransactionType::CASH_IN,
                UserAccountType::FASTPAY_SAVINGS_ACCOUNT,
                UserAccountType::FASTPAY_SAVINGS_ACCOUNT,
                $response->transaction
            );

            TransactionConsumeUpdate::dispatch(
                auth()->user(),
                date('Y-m-d'),
                TransactionType::CASH_IN,
                $request->amount
            );

            TransactionConsumeUpdate::dispatch(
                $receiver,
                date('Y-m-d'),
                TransactionType::CASH_IN,
                $request->amount
            );

            (new LimitUpdater(new LimitCheckerParam($sender, $request->amount, TransactionType::CASH_IN)))->update();
            (new LimitUpdater(new LimitCheckerParam($receiver, $request->amount, TransactionType::CASH_IN)))->update();

            $sender->notify(new Sent(['mail', 'database', FcmPushChannel::class], "Sell balance process successfully completed."));
            $receiver->notify(new Received(['mail', 'database', FcmPushChannel::class], "You have a new CashIn"));

            $logMessage = auth()->user()->name . '(' . auth()->user()->mobile_no . ')' . ' has send ' . $request->amount . ' ' . config('basic_settings.currency_text') . ' to ' . $receiver->name . '(' . $receiver->mobile_no . '). Transaction ID# ' . $response->transaction->tx_unique_id;
            $this->logActivity($logMessage, auth()->user(), auth()->user(), $response->transaction->toArray());

            return $this->respondInJSON(200, [trans('messages.money_sent_successfully')], $this->getResponse($receiver, $response->transaction));

        } catch (\Exception $e) {

            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function getResponse(User $recipient, Transaction $transaction)
    {
        return [
            "summary" => [
                "recipient" => [
                    "name" => $recipient->name,
                    "mobile_number" => $recipient->mobile_no,
                    "avatar" => $recipient->avatar
                ],
                "invoice_id" => $transaction->tx_unique_id
            ]
        ];
    }



    private function doTheTransaction($sender, $receiver, $request, $commission = null)
    {
        return (new TransactionGenerator(
            new Param(
                $sender,
                $receiver,
                $request->amount,
                Currency::where('code', $request->currency ?? 'IQD' )->first(),
                $request,
                $commission
            ), TransactionTypeCode::CASH_IN)
        )->process();
    }
}

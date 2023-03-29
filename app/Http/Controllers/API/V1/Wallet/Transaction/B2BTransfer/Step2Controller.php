<?php

namespace App\Http\Controllers\API\V1\Wallet\Transaction\B2BTransfer;

use App\Channels\FcmPushChannel;
use App\Constant\TransactionType;
use App\Constant\TransactionTypeCode;
use App\Constant\UserAccountType;
use App\Domain\Finance\Models\Commission;
use App\Domain\Transaction\Library\B2BTransfer\B2BTransferRequestValidation;
use App\Domain\Transaction\Library\B2BTransfer\Param;
use App\Domain\Transaction\Library\ExtTransaction\ChargeOnPaymentTrxProcessor;
use App\Domain\Transaction\Library\TransactionGenerator;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\UserRelation\Models\User;
use App\Domain\Wallet\Models\Limit\LimitCheckerParam;
use App\Domain\Wallet\Models\Limit\LimitUpdater;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Wallet\Transaction\B2BTransfer\VerifyRequest;
use App\Jobs\TransactionConsumeUpdate;
use App\Notifications\Transactions\B2BTransfer\Received;
use App\Notifications\Transactions\B2BTransfer\Sent;
use App\Traits\CashBackTrait;
use App\Traits\CommissionTrait;
use App\Traits\RewardTrait;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
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
            $sender = $this->fetchUserByKeyVal('id', auth()->user()->id);
            $receiver = $this->fetchUserByKeyVal('mobile_no', $request->receiver_mobile_number);

            $amount = $request->amount;
            $chargeAmount = 0;
            $commission = null;
            $charge = $this->calculateCommission($sender, $receiver, $request->amount, TransactionType::B2B_TRANSFER, UserAccountType::FASTPAY_SAVINGS_ACCOUNT);
            if($charge != false){
                $chargeAmount = $this->getCommissionAmount($charge, $amount);
                $commission = $charge;
            }

            if ($error = (new B2BTransferRequestValidation($sender, $receiver, $request->amount, $chargeAmount, config('basic_settings.currency_text'), TransactionType::B2B_TRANSFER))->validate())
                return $error;

            $response = $this->doTheTransaction($sender, $receiver, $request, $commission);

            if ($response->status == false)
                return $response->message;

            (new ChargeOnPaymentTrxProcessor(
                $sender,
                $receiver,
                $response->transaction
            ))->processTransaction();

            $this->processCashBack(
                $sender,
                $receiver,
                $request->amount,
                TransactionType::B2B_TRANSFER,
                UserAccountType::FASTPAY_SAVINGS_ACCOUNT,
                UserAccountType::FASTPAY_SAVINGS_ACCOUNT,
                $response->transaction
            );

            $this->processReward(
                $sender,
                $receiver,
                $request->amount,
                TransactionType::B2B_TRANSFER,
                UserAccountType::FASTPAY_SAVINGS_ACCOUNT,
                UserAccountType::FASTPAY_SAVINGS_ACCOUNT,
                $response->transaction
            );

            TransactionConsumeUpdate::dispatch(
                auth()->user(),
                date('Y-m-d'),
                TransactionType::B2B_TRANSFER,
                $request->amount
            );

            (new LimitUpdater(new LimitCheckerParam($sender, $request->amount, TransactionType::B2B_TRANSFER)))->update();

            $sender->notify(new Sent(['database', FcmPushChannel::class], $response->notificationMessage('Sender')));
            $receiver->notify(new Received(['database', FcmPushChannel::class], $response->notificationMessage('Receiver')));

            $logMessage = auth()->user()->name . '(' . auth()->user()->mobile_no . ')' . ' has send ' . $request->amount . ' ' . config('basic_settings.currency_text') . ' to ' . $receiver->name . '(' . $receiver->mobile_no . '). Transaction ID# ' . $response->transaction->tx_unique_id;
            $this->logActivity($logMessage, auth()->user(), auth()->user(), $response->transaction->toArray());

            return $this->respondInJSON(200, [trans('messages.money_sent_successfully')], $this->getResponse($receiver, $response->transaction));
        } catch (\Exception $e) {

            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function doTheTransaction(User $sender, User $receiver, Request $request, ?Commission $commission = null)
    {
        $senderAccount = $this->fetchAccountByKeyVal('user_id', $sender->id);
        $senderBalanceAccountWithLock = $this->fetchBalanceAccountWithLock($senderAccount->id, config('basic_settings.currency_id'));

        $receiverAccount = $this->fetchAccountByKeyVal('user_id', $receiver->id);
        $receiverBalanceAccountWithLock = $this->fetchBalanceAccountWithLock($receiverAccount->id, config('basic_settings.currency_id'));

        return (new TransactionGenerator(
            new Param(
                $sender,
                $senderAccount,
                $senderBalanceAccountWithLock,
                $receiver,
                $receiverAccount,
                $receiverBalanceAccountWithLock,
                $commission,
                $request,
                config('basic_settings.currency_id'),
                config('basic_settings.currency_text'),
                $request->amount
            ),
            TransactionTypeCode::B2B_TRANSFER
        )
        )->process();
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
}

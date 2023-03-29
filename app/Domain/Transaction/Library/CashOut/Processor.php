<?php

namespace App\Domain\Transaction\Library\CashOut;

use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Utility\TransactionStatus;
use App\Domain\Wallet\Library\Validator\TransactionValidator;
use App\Exceptions\Transaction\TransactionValidatorException;
use App\Traits\ApiResponseTrait;
use App\Traits\StatementTrait;
use App\Traits\TransactionIdTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Processor
{
    use TransactionIdTrait;
    use ApiResponseTrait;
    use StatementTrait;

    public $param;

    public function __construct(Param $param)
    {
        $this->param = $param;
    }

    public function process()
    {
        try {
            DB::beginTransaction();

            (new TransactionValidator(
                $this->param->getSenderBalanceAccountWithLock(), 
                $this->param->getSenderAccount()->id, 
                $this->param->getReceiverAccount()->id, 
                $this->param->getTransactionTypeId(),
                $this->param->getAmount() + $this->param->getChargeAmount()
            ))->validate();

            $transaction = $this->processTransaction();

            DB::commit();

            return (new Response(
                true,
                $transaction,
                null,
                [
                    'senderNotificationMessage'   => "Money Transferred at " . date("Y-m-d H:i:s"),
                    'receiverNotificationMessage' => "Money Received at " . date("Y-m-d H:i:s")
                ]
            )
            );
        } catch (TransactionValidatorException $e) {
            DB::rollBack();
            Log::error($e);
            return (
                new Response(
                    false,
                    null,
                    $this->invalidResponse([$e->getMessage()])
                )
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return (new Response(
                false,
                null,
                $this->invalidResponse([trans('messages.internal_server_error')])
            )
            );
        }
    }

    private function processTransaction()
    {
        $transaction = new Transaction();
        $transaction->tx_unique_id = $this->getUniqueTransactionId(
            date('Y-m-d H:i:s'),
            $this->param->getSender()->mobile_no,
            $this->param->getReceiver()->mobile_no
        );
        $transaction->sender_id = $this->param->getSenderAccount()->id;
        $transaction->receiver_id = $this->param->getReceiverAccount()->id;
        $transaction->transaction_type_id = $this->param->getTransactionTypeId();
        $transaction->amount = $this->param->getAmount();
        $transaction->currency_id = $this->param->getCurrencyId();
        $transaction->transaction_status_id = TransactionStatus::SUCCESS;
        $transaction->latitude = $this->param->getRequest()->header('latitude') ??  null;
        $transaction->longitude = $this->param->getRequest()->header('longitude') ??  null;
        if ($this->param->getCommission()) {
            $transaction->commission_id = $this->param->getCommission()->id;
            $transaction->sender_commission = $this->param->getChargeAmount();
            $transaction->commission_account_id = $this->param->getCommissionAccount()->getAccount()->id;
        }
        $transaction->save();

        $this->saveOnStatement(
            $transaction,
            $this->param->getSender()->id,
            $this->param->getDebitDescription($this->param->getReceiverAccount(), $this->param->getAmount()),
            0,
            $this->param->getAmount(),
            $this->param->getSenderBalanceAccountWithLock()->balance - $this->param->getAmount()
        );
        $this->param->getSenderBalanceAccountWithLock()->decrement('balance', $this->param->getAmount());

        $this->saveOnStatement(
            $transaction,
            $this->param->getReceiver()->id,
            $this->param->getCreditDescription($this->param->getSenderAccount(), $this->param->getAmount()),
            $this->param->getAmount(),
            0,
            $this->param->getReceiverBalanceAccountWithLock()->balance + $this->param->getAmount()
        );
        $this->param->getReceiverBalanceAccountWithLock()->increment('balance', $this->param->getAmount());

        if ($this->param->getCommission()) {
            $this->saveOnStatement(
                $transaction,
                $this->param->getSender()->id,
                $this->param->getDebitDescription($this->param->getCommissionAccount()->getAccount(), $this->param->getChargeAmount()),
                0,
                $this->param->getChargeAmount(),
                $this->param->getSenderBalanceAccountWithLock()->balance - $this->param->getChargeAmount()
            );
            $this->param->getSenderBalanceAccountWithLock()->decrement('balance', $this->param->getChargeAmount());

            $this->saveOnStatement(
                $transaction,
                $this->param->getCommissionAccount()->getAccount()->user_id,
                $this->param->getCreditDescription($this->param->getSenderAccount(), $this->param->getChargeAmount()),
                $this->param->getChargeAmount(),
                0,
                $this->param->getCommissionAccount()->getBalanceAccountWithLock()->balance + $this->param->getChargeAmount()
            );
            $this->param->getCommissionAccount()->getBalanceAccountWithLock()->increment('balance', $this->param->getChargeAmount());
        }

        return $transaction;
    }
}

<?php

namespace App\Domain\Transaction\Library\ExtTransaction;

use App\Constant\TransactionStatus;
use App\Constant\TransactionType;
use App\Constant\UserTypeId;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Finance\Models\ChargeOnPayment;
use App\Domain\Finance\Models\ChargeOnPaymentTransaction;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\UserRelation\Models\User;
use App\Helpers\AccountHelper;
use App\Traits\StatementTrait;
use App\Traits\TransactionIdTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ChargeOnPaymentTrxProcessor
{
    use TransactionIdTrait, StatementTrait;

    private User $sender;
    private User $receiver;
    private int $senderLevelId;
    private int $receiverLevelId;
    private Transaction $transaction;
    private ?ChargeOnPayment $chargeOnPayment;
    private int $chargeOnPaymentAmount;
    private ?ChargeOnPaymentTransaction $chargeOnPaymentTrx;

    public function __construct(User $sender, User $receiver, Transaction $transaction)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->transaction = $transaction;

        $this->init();
    }

    private function init()
    {
        $this->senderLevelId = AccountHelper::getLevelIdByUser($this->sender);
        $this->receiverLevelId = AccountHelper::getLevelIdByUser($this->receiver);
        $this->chargeOnPayment = $this->getChargeOnPayment();
    }

    private function getChargeOnPayment() : ?ChargeOnPayment
    {
        $chargeOnPayment = ChargeOnPayment::active()
            ->whereSenderUserTypeId($this->sender->user_type_id)
            ->whereSenderLevelId($this->senderLevelId)
            ->whereReceiverUserTypeId($this->receiver->user_type_id)
            ->whereReceiverLevelId($this->receiverLevelId)
            ->whereTransactionTypeId($this->transaction->transaction_type_id)
            ->whereCurrencyId(config('basic_settings.currency_id'))
            ->whereMerchantId($this->receiver->id)
            ->where('from_amount', '<=', $this->transaction->amount)
            ->where('to_amount', '>=', $this->transaction->amount)
            ->latest('id')
            ->first();

        if ( ! $chargeOnPayment ) {
            $chargeOnPayment = ChargeOnPayment::active()
                ->whereSenderUserTypeId($this->sender->user_type_id)
                ->whereSenderLevelId($this->senderLevelId)
                ->whereReceiverUserTypeId($this->receiver->user_type_id)
                ->whereReceiverLevelId($this->receiverLevelId)
                ->whereTransactionTypeId($this->transaction->transaction_type_id)
                ->whereCurrencyId(config('basic_settings.currency_id'))
                ->whereNull('merchant_id')
                ->where('from_amount', '<=', $this->transaction->amount)
                ->where('to_amount', '>=', $this->transaction->amount)
                ->latest('id')
                ->first();
        }

        return $chargeOnPayment;
    }

    public function processTransaction()
    {
        if ( ! $this->chargeOnPayment ) {
            return;
        }

        if ($this->chargeOnPayment->amount <= 0) {
            return;
        }

        if ($this->checkChargeOnPaymentTrxAlreadyExists()) {
            return;
        }

        $this->setChargeOnPaymentAmount();

        $this->initiateChargeOnPaymentTransaction();

        $this->doTheTransaction();
    }

    private function doTheTransaction()
    {
        $senderAccount = AccountHelper::getUserAccount('user_id', $this->receiver->id);
        $senderBalanceAccountWithLock = AccountHelper::getUserBalanceAccountByAccountIdWithLock($senderAccount->id, config('basic_settings.currency_id'));

        if ($senderBalanceAccountWithLock->balance < $this->chargeOnPaymentAmount) {
            Log::warning("Sender ({$senderAccount->account_no}) has no balance for charge deduction on payment. Transaction ID : {$this->transaction->tx_unique_id}");
            return;
        }

        $receiverAccount = AccountHelper::getUserAccount('id', $this->chargeOnPayment->credit_account_id);

        if ($receiverAccount->user->user_type_id != UserTypeId::CHARGE) {
            Log::warning("Receiver ({$receiverAccount->account_no}) is must be a charge type account for crediting charge on payment. Transaction ID : {$this->transaction->tx_unique_id}");
            return;
        }

        $receiverBalanceAccountWithLock = AccountHelper::getUserBalanceAccountByAccountIdWithLock($receiverAccount->id, config('basic_settings.currency_id'));

        try {
            DB::beginTransaction();

            $chargeTransaction = $this->createTransaction($senderAccount, $receiverAccount);

            /** Debit */
            $this->saveOnStatement(
                $chargeTransaction,
                $this->receiver->id,
                "Transferred " . config('basic_settings.currency_text') . " {$this->chargeOnPaymentAmount} to {$receiverAccount->account_no}.",
                0,
                $this->chargeOnPaymentAmount,
                $senderBalanceAccountWithLock->balance - $this->chargeOnPaymentAmount
            );
            $senderBalanceAccountWithLock->decrement('balance', $this->chargeOnPaymentAmount);

            /** Credit */
            $this->saveOnStatement(
                $chargeTransaction,
                $receiverAccount->user->id,
                "Received " . config('basic_settings.currency_text') . " {$this->chargeOnPaymentAmount} from {$senderAccount->account_no}.",
                $this->chargeOnPaymentAmount,
                0,
                $receiverBalanceAccountWithLock->balance + $this->chargeOnPaymentAmount
            );
            $receiverBalanceAccountWithLock->increment('balance', $this->chargeOnPaymentAmount);

            /** Update New Trx Id For Charge On Payment Transaction */
            $this->chargeOnPaymentTrx->new_trx_unq_id = $chargeTransaction->tx_unique_id;
            $this->chargeOnPaymentTrx->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Charge deduction on payment is failed. Original transaction id : " . $this->transaction->tx_unique_id);
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
        }
    }

    private function createTransaction(UserAccount $senderAccount, UserAccount $receiverAccount) : Transaction
    {
        $chargeTransaction = new Transaction();
        $chargeTransaction->tx_unique_id = $this->getUniqueTransactionId(
            date('Y-m-d H:i:s'),
            $this->receiver->mobile_no,
            $receiverAccount->user->mobile_no
        );
        $chargeTransaction->order_id = $this->transaction->tx_unique_id;
        $chargeTransaction->sender_id = $senderAccount->id;
        $chargeTransaction->receiver_id = $receiverAccount->id;
        $chargeTransaction->transaction_type_id = TransactionType::COMMISSION;
        $chargeTransaction->amount = $this->chargeOnPaymentAmount;
        $chargeTransaction->currency_id = config('basic_settings.currency_id');
        $chargeTransaction->transaction_status_id = TransactionStatus::SUCCESS;
        $chargeTransaction->save();

        return $chargeTransaction;
    }

    private function checkChargeOnPaymentTrxAlreadyExists() : bool
    {
        return ChargeOnPaymentTransaction::where([
                'charge_on_payment_id' => $this->chargeOnPayment->id,
                'original_trx_unq_id' => $this->transaction->tx_unique_id
            ])->first() != null;
    }

    private function setChargeOnPaymentAmount()
    {
        $this->chargeOnPaymentAmount = $this->getChargeOnPaymentAmount();
    }

    protected function getChargeOnPaymentAmount()
    {
        if ($this->chargeOnPayment->slab_type == "F") {
            $amount = $this->chargeOnPayment->amount;
        } else {
            $amount = ($this->transaction->amount * $this->chargeOnPayment->amount) / 100;
        }

        return ceil($amount);
    }

    private function initiateChargeOnPaymentTransaction()
    {
        $this->chargeOnPaymentTrx = ChargeOnPaymentTransaction::firstOrNew([
            'charge_on_payment_id' => $this->chargeOnPayment->id,
            'original_trx_unq_id' => $this->transaction->tx_unique_id,
        ]);
        $this->chargeOnPaymentTrx->amount = $this->chargeOnPaymentAmount;
        $this->chargeOnPaymentTrx->save();
    }
}

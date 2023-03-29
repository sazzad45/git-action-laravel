<?php


namespace App\Traits;


use App\Constant\Currency;
use App\Constant\GLAccounts;
use App\Constant\TransactionType;
use App\Domain\Accounting\Models\AccountBalance;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Finance\Models\CashBackRewards;
use App\Domain\Finance\Models\CashbackTransaction;
use App\Domain\Finance\Models\LevelAccount;
use App\Domain\Transaction\Models\Statement;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Utility\TransactionStatus;
use App\Domain\UserRelation\Models\User;
use App\Domain\Wallet\Library\Capacity\ReceiverAccountCapacityChecker;
use App\Domain\Wallet\Library\Capacity\ReceiverAccountCapacityCheckerParam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait CashBackTrait
{
    use TransactionIdTrait;

    protected function processCashBack(
        User $sender,
        User $receiver,
        $amount,
        int $transactionTypeId,
        int $senderAccountTypeId,
        int $receiverAccountTypeId,
        Transaction $transaction,
        $isUser = false
    ) {
        try {

            $senderLevel = $sender
                ->levels()
                ->wherePivot('status', 1)
                ->orderBy('level_users.created_at', 'DESC')
                ->first()
                ->pivot
                ->level_id ?? 1;

            $receiverLevel = $receiver
                ->levels()
                ->wherePivot('status', 1)
                ->orderBy('level_users.created_at', 'DESC')
                ->first()
                ->pivot
                ->level_id ?? 1;


            $cashBack = $this->calculateCashback(
                $sender,
                $receiver,
                $amount,
                $transactionTypeId,
                $senderAccountTypeId,
                $receiverAccountTypeId,
                $senderLevel,
                $receiverLevel
            );
            // check cashback is available

            if ($cashBack == false) return;

            $cashBackTransaction = CashbackTransaction::where('cash_back_reward_id', $cashBack->id)
                ->where('original_trx_unq_id', $transaction->tx_unique_id);

            // check cashback is already disburse
            if ($cashBackTransaction->count()) return;


            // ok assign a cashback for this user
            $cashBackAmount = $this->getCashBackAmount($cashBack, $amount);

            $newCashbackTransaction = new CashbackTransaction();
            $newCashbackTransaction->cash_back_reward_id = $cashBack->id;
            $newCashbackTransaction->original_trx_unq_id = $transaction->tx_unique_id;
            $newCashbackTransaction->amount = $cashBackAmount;
            $newCashbackTransaction->save();

            $levelAccount  = LevelAccount::where('level_id', $senderLevel)
                ->where('status', 1)
                ->where('type', 'cashback')
                ->orderBy('created_at', 'DESC')
                ->first();

            if ((new ReceiverAccountCapacityChecker(new ReceiverAccountCapacityCheckerParam($sender, $cashBackAmount)))->check()->limitCrossed()) {
                Log::warning("Cashback Error: receiver account capacity exceeds.");
                return;
            }

            // ok arrange the transaction
            $this->doTheCashBackTransaction($newCashbackTransaction, $transaction, $cashBackAmount, $levelAccount);
        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
        }
    }


    protected function doTheCashBackTransaction(
        CashbackTransaction $cashbackTransaction,
        Transaction $transaction,
        $cashBackAmount,
        LevelAccount $levelAccount
    ) {
        $senderAccount = UserAccount::with('user')->where('id', $levelAccount->user_account_id)->first();

        $receiverAccount = UserAccount::with('user')->where('id', $transaction->sender_id)->first();

        $senderBalanceAccount = AccountBalance::where('user_account_id', $senderAccount->id)
            ->where('currency_id', config('basic_settings.currency_id'))
            ->lockForUpdate()
            ->first();


        if ($senderBalanceAccount->balance < $cashBackAmount) {
            Log::warning("No balance for Cashback transaction");
            return;
        }

        $receiverBalanceAccount = AccountBalance::where('user_account_id', $receiverAccount->id)
            ->where('currency_id', config('basic_settings.currency_id'))
            ->lockForUpdate()
            ->first();

        try {
            DB::beginTransaction();

            $senderCurrentBalance = $senderBalanceAccount->balance - $cashBackAmount;

            $receiverCurrentBalance = $receiverBalanceAccount->balance + $cashBackAmount;

            $cbTransaction = new Transaction();
            $cbTransaction->tx_unique_id = $this->getUniqueTransactionId(
                date('Y-m-d H:i:s'),
                $senderAccount->user->mobile_no,
                $receiverAccount->user->mobile_no
            );
            $cbTransaction->order_id = $transaction->tx_unique_id;
            $cbTransaction->sender_id = $senderAccount->id;
            $cbTransaction->receiver_id = $receiverAccount->id;
            $cbTransaction->transaction_type_id = TransactionType::CASH_BACK;
            $cbTransaction->amount = $cashBackAmount;
            $cbTransaction->currency_id = config('basic_settings.currency_id');
            $cbTransaction->transaction_status_id = TransactionStatus::SUCCESS;
            $cbTransaction->save();

            Statement::create([
                'transaction_id' => $cbTransaction->id,
                'transaction_type_id' => TransactionType::CASH_BACK,
                'user_id' => $senderAccount->user->id,
                'description' => "Transferred " . config('basic_settings.currency_text') . " {$cashBackAmount} to {$receiverAccount->account_no} .",
                'debit' => $cashBackAmount,
                'credit' => 0,
                'current_balance' => $senderCurrentBalance,
                'created_at' => $cbTransaction->created_at,
                'updated_at' => $cbTransaction->created_at
            ]);
            $senderBalanceAccount->decrement('balance', $cashBackAmount);


            Statement::create([
                'transaction_id' => $cbTransaction->id,
                'transaction_type_id' => TransactionType::CASH_BACK,
                'user_id' => $receiverAccount->user->id,
                'description' => "Received " . config('basic_settings.currency_text') . " {$cashBackAmount} from {$senderAccount->account_no}.",
                'debit' => 0,
                'credit' => $cashBackAmount,
                'current_balance' => $receiverCurrentBalance,
                'created_at' => $cbTransaction->created_at,
                'updated_at' => $cbTransaction->created_at
            ]);
            $receiverBalanceAccount->increment('balance', $cashBackAmount);

            $cashbackTransaction->new_trx_unq_id = $cbTransaction->tx_unique_id;
            $cashbackTransaction->update();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
        }
    }


    protected function calculateCashback(
        User $sender,
        User $receiver,
        $amount,
        int $transactionTypeId,
        int $senderAccountTypeId,
        int $receiverAccountTypeId,
        $senderLevel,
        $receiverLevel
    ) {
        $charge = 0;
        $total = $amount + $charge;
        $date = date('Y-m-d H:i:s');

        $cashBack = CashBackRewards::where('sender_user_type_id', $sender->user_type_id)
            ->where('sender_account_type_id', $senderAccountTypeId)
            ->where('sender_level_id', $senderLevel)
            ->where('receiver_user_type_id', $receiver->user_type_id)
            ->where('receiver_account_type_id', $receiverAccountTypeId)
            ->where('receiver_level_id', $receiverLevel)
            ->where('currency_id', Currency::IQD)
            ->where('transaction_type_id', $transactionTypeId)
            ->where('amount', '>', 0)
            ->where('status', 1)
            ->where('from_amount', '<=', $amount)
            ->where('to_amount', '>=', $amount)
            ->where('starting_time', '<=', $date)
            ->where('ending_time', '>=', $date);

        $a = $cashBack->count();

        if ($cashBack->count() == 0) {
            return false;
        }

        $cashBack = $cashBack->first();

        return $cashBack;
    }

    protected function getCashBackAmount(CashBackRewards $cashBack, $amount)
    {
        $cashBackAmount = 0;

        if ($cashBack->slab_type == "F") {
            $cashBackAmount = $cashBack->amount;
        } else {
            $cashBackAmount = ($amount * $cashBack->amount) / 100;
        }
        return ceil($cashBackAmount);
    }
}

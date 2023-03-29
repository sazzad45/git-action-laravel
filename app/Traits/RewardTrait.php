<?php


namespace App\Traits;


use App\Constant\Currency;
use App\Constant\TransactionType;
use App\Domain\Accounting\Models\AccountBalance;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Finance\Models\LevelAccount;
use App\Domain\Finance\Models\Reward;
use App\Domain\Finance\Models\RewardTransaction;
use App\Domain\Transaction\Models\Statement;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Utility\TransactionStatus;
use App\Domain\UserRelation\Models\User;
use App\Domain\Wallet\Library\Capacity\ReceiverAccountCapacityChecker;
use App\Domain\Wallet\Library\Capacity\ReceiverAccountCapacityCheckerParam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait RewardTrait
{
    use TransactionIdTrait;

    protected function processReward(
        User $sender,
        User $receiver,
        $amount,
        int $transactionTypeId,
        int $senderAccountTypeId,
        int $receiverAccountTypeId,
        Transaction $transaction,
        $isMerchant = false
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


            $reward = $this->calculateReward(
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

            if ($reward == false) return;

            $rewardTransaction = RewardTransaction::where('reward_id', $reward->id)
                ->where('original_trx_unq_id', $transaction->tx_unique_id);

            // check cashback is already disburse
            if ($rewardTransaction->count()) return;


            // ok assign a cashback for this user
            $rewardAmount = $this->getRewardAmount($reward, $amount);

            $newRewardTransaction = new RewardTransaction();
            $newRewardTransaction->reward_id = $reward->id;
            $newRewardTransaction->original_trx_unq_id = $transaction->tx_unique_id;
            $newRewardTransaction->amount = $rewardAmount;
            $newRewardTransaction->save();


            // get the sender account details dynamically

            $levelAccount  = LevelAccount::where('level_id', $receiverLevel)
                ->where('status', 1)
                ->where('type', 'reward')
                ->orderBy('created_at', 'DESC')
                ->first();

            if ((new ReceiverAccountCapacityChecker(new ReceiverAccountCapacityCheckerParam($receiver, $rewardAmount)))->check()->limitCrossed()) {
                Log::warning("Reward Error: receiver account capacity exceeds.");
                return;
            }

            // ok arrange the transaction
            $this->doTheRewardTransaction($newRewardTransaction, $transaction, $rewardAmount, $levelAccount);
        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
        }
    }


    protected function doTheRewardTransaction(
        RewardTransaction &$rewardTransaction,
        Transaction $transaction,
        $rewardAmount,
        LevelAccount $levelAccount
    ) {
        $senderAccount = UserAccount::with('user')->where('id', $levelAccount->user_account_id)->first();
        $receiverAccount = UserAccount::with('user')->where('id', $transaction->receiver_id)->first();

        $senderBalanceAccount = AccountBalance::where('user_account_id', $senderAccount->id)
            ->where('currency_id', config('basic_settings.currency_id'))
            ->lockForUpdate()
            ->first();


        if ($senderBalanceAccount->balance < $rewardAmount) {
            Log::warning("No balance for Reward transaction");
            return;
        }

        $receiverBalanceAccount = AccountBalance::where('user_account_id', $receiverAccount->id)
            ->where('currency_id', config('basic_settings.currency_id'))
            ->lockForUpdate()
            ->first();

        try {
            DB::beginTransaction();

            $senderCurrentBalance = $senderBalanceAccount->balance - $rewardAmount;

            $receiverCurrentBalance = $receiverBalanceAccount->balance + $rewardAmount;

            $newTransaction = new Transaction();
            $newTransaction->tx_unique_id = $this->getUniqueTransactionId(
                date('Y-m-d H:i:s'),
                $senderAccount->user->mobile_no,
                $receiverAccount->user->mobile_no
            );
            $newTransaction->order_id = $transaction->tx_unique_id;
            $newTransaction->sender_id = $senderAccount->id;
            $newTransaction->receiver_id = $receiverAccount->id;
            $newTransaction->transaction_type_id = TransactionType::REWARD;
            $newTransaction->amount = $rewardAmount;
            $newTransaction->currency_id = config('basic_settings.currency_id');
            $newTransaction->transaction_status_id = TransactionStatus::SUCCESS;
            $newTransaction->save();

            Statement::create([
                'transaction_id' => $newTransaction->id,
                'transaction_type_id' => TransactionType::REWARD,
                'user_id' => $senderAccount->user->id,
                'description' => "Transferred " . config('basic_settings.currency_text') . " {$rewardAmount} to {$receiverAccount->account_no} .",
                'debit' => $rewardAmount,
                'credit' => 0,
                'current_balance' => $senderCurrentBalance,
                'created_at' => $rewardTransaction->created_at,
                'updated_at' => $rewardTransaction->created_at
            ]);
            $senderBalanceAccount->decrement('balance', $rewardAmount);


            Statement::create([
                'transaction_id' => $newTransaction->id,
                'transaction_type_id' => TransactionType::REWARD,
                'user_id' => $receiverAccount->user->id,
                'description' => "Received " . config('basic_settings.currency_text') . " {$rewardAmount} from {$senderAccount->account_no}.",
                'debit' => 0,
                'credit' => $rewardAmount,
                'current_balance' => $receiverCurrentBalance,
                'created_at' => $rewardTransaction->created_at,
                'updated_at' => $rewardTransaction->created_at
            ]);
            $receiverBalanceAccount->increment('balance', $rewardAmount);

            $rewardTransaction->new_trx_unq_id = $newTransaction->tx_unique_id;
            $rewardTransaction->update();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
        }
    }


    protected function calculateReward(
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

        $reward = Reward::where('sender_user_type_id', $sender->user_type_id)
            ->where('sender_account_type_id', $senderAccountTypeId)
            ->where('sender_level_id', $senderLevel)
            ->where('receiver_level_id', $receiverLevel)
            ->where('receiver_user_type_id', $receiver->user_type_id)
            ->where('receiver_account_type_id', $receiverAccountTypeId)
            ->where('currency_id', Currency::IQD)
            ->where('transaction_type_id', $transactionTypeId)
            ->where('amount', '>', 0)
            ->where('status', 1)
            ->where('from_amount', '<=', $amount)
            ->where('to_amount', '>=', $amount)
            ->where('starting_time', '<=', $date)
            ->where('ending_time', '>=', $date);

        $a = $reward->count();

        if ($reward->count() == 0) {
            return false;
        }

        $reward = $reward->first();

        return $reward;
    }

    protected function getRewardAmount(Reward $reward, $amount)
    {
        $rewardAmount = 0;

        if ($reward->slab_type == "F") {
            $rewardAmount = $reward->amount;
        } else {
            $rewardAmount = ($amount * $reward->amount) / 100;
        }
        return ceil($rewardAmount);
    }
}

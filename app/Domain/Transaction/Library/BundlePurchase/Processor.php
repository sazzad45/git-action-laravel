<?php


namespace App\Domain\Transaction\Library\BundlePurchase;

use App\Domain\Transaction\Library\TraitProcessor;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Utility\TransactionStatus;
use App\Traits\ApiResponseTrait;
use App\Traits\TransactionIdTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Processor
{
    use TransactionIdTrait;
    use ApiResponseTrait;
    use TraitProcessor;

    public $param;

    public function __construct(Param $param)
    {
        $this->param = $param;
    }

    public function process()
    {
        try{
            DB::beginTransaction();

            $senderAccount = $this->param->getSenderAccount();
            $receiverAccount = $this->param->getReceiverAccount();

            $transaction = new Transaction();
            $transaction->tx_unique_id = $this->getUniqueTransactionId(
                date('Y-m-d H:i:s'),
                $this->param->getSender()->mobile_no,
                $this->param->getReceiver()->mobile_no
            );

            $transaction->order_id = $this->param->card->cardNumber;

            $transaction->sender_id = $senderAccount->id;
            $transaction->receiver_id = $receiverAccount->id;
            $transaction->transaction_type_id = $this->param->getTransactionType()->id;
            $transaction->amount = $this->param->getAmount();
            $transaction->currency_id = $this->param->getCurrency()->id;
            $transaction->transaction_status_id = TransactionStatus::SUCCESS;
            $transaction->latitude = $this->param->request->header('latitude') ??  null;
            $transaction->longitude = $this->param->request->header('longitude') ??  null;
            if($this->param->getCommission() != null) {
                $transaction->commission_id = $this->param->getCommission()->id;
                $transaction->sender_commission = $this->param->getCommissionAmount();
                $transaction->commission_account_id = $this->param->getcommissionReceiverAccount()->id;
            }
            $transaction->save();

            $senderBalanceAccount   = $this->param->getSenderAccountBalance();

            $balance = $senderBalanceAccount->balance;
            $totalDeductable = 0;

            foreach($this->param->getDebitableAccountList() as $item) {
                $balance = $balance - $item->amount; // remove the money from sender account
                $this->saveOnStatement(
                    $transaction,
                    $item,
                    $balance
                );
                $totalDeductable += $item->amount;
            }

            $senderBalanceAccount->decrement('balance', $totalDeductable);


            foreach($this->param->getCreditableAccountList() as $item)
            {
                $receiverBalanceAccount   = $item->accountBalance;
                $this->saveOnStatementForCredit(
                    $transaction,
                    $item,
                    $receiverBalanceAccount->balance + $item->amount
                );
                $receiverBalanceAccount->increment('balance', $item->amount);
            }

            DB::commit();

            return (
                new Response(
                    true,
                    $transaction,
                    null,
                    [
                        'senderNotificationMessage'   => "{$this->param->getCurrency()->name} {$totalDeductable} has been transferred to {$senderAccount->user->mobile_no}",
                        'receiverNotificationMessage' => "You've received {$this->param->getCurrency()->name} {$this->param->getAmount()} from {$this->param->card->cardNumber}"
                    ]
                )
            );
        }catch (\Exception $e){
            DB::rollBack();
            Log::error($e);
            return (
                new Response(
                    false,
                    null,
                    $this->invalidResponse([ trans('messages.internal_server_error') ])
                )
            );
        }
    }
}

<?php

namespace App\Domain\Transaction\Library;

use App\Domain\Accounting\Models\AccountBalance;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Finance\Models\Commission;
use App\Domain\Finance\Models\LevelAccount;
use App\Domain\Independent\Models\Currency;
use App\Domain\UserRelation\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait TraitParam
{
    public Request $request;

    private Currency $currency;
    private float $amount;

    private User $sender;
    private UserAccount $senderAccount;
    private AccountBalance $senderAccountBalance;

    private User $receiver;
    private UserAccount $receiverAccount;
    private AccountBalance $receiverAccountBalance;

    private DebitableAccountList $debitableList;
    private CreditableAccountList $creditableList;

    protected float $commissionAmount;
    protected ?Commission $commission;
    protected ?User $commissionUser;
    protected ?UserAccount $commissionReceiverAccount;
    protected ?AccountBalance $commissionReceiverAccountBalance;


    public function getSender(): User
    {
        return $this->sender;
    }

    public function getReceiver(): User
    {
        return $this->receiver;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getDebitableAccountList()
    {
        return $this->debitableList->all();
    }

    public function getCreditableAccountList()
    {
        return $this->creditableList->all();
    }

    public function getReceiverAccountBalance(): AccountBalance
    {
        $this->receiverAccountBalance = AccountBalance::
        where('user_account_id', $this->getReceiverAccount()->id)
            ->where('currency_id', $this->currency->id)
            ->lockForUpdate()
            ->first();
        return $this->receiverAccountBalance;
    }

    public function getSenderAccountBalance(): AccountBalance
    {
        $this->senderAccountBalance = AccountBalance::
        where('user_account_id', $this->getSenderAccount()->id)
            ->where('currency_id', $this->currency->id)
            ->lockForUpdate()
            ->first();

        return $this->senderAccountBalance;
    }

    public function getCommissionAmount(): float
    {
        return $this->commissionAmount;
    }

    public function getCommission()
    {
        return $this->commission;
    }

    public function getcommissionReceiverAccount(): UserAccount
    {
        return $this->commissionReceiverAccount;
    }

    public function getCommissionReceiverAccountBalance(): AccountBalance
    {
        return $this->commissionReceiverAccountBalance;
//        $this->commissionReceiverAccountBalance = AccountBalance::
//        where('user_account_id', $this->getReceiverAccount()->id)
//            ->where('currency_id', $this->currency->id)
//            //  ->lockForUpdate()
//            ->first();
//        return $this->receiverAccountBalance;
    }

    private function commissionInitialization()
    {
        if ($this->commission != null) {
            if ($this->commission->sender_slab_type == "F") {
                $this->commissionAmount = ceil($this->commission->sender_charge);
            } else {
                $this->commissionAmount = ceil(($this->amount * $this->commission->sender_charge) / 100);
            }
        }

        $senderLevel = $this->sender
                ->levels()
                ->wherePivot('status', 1)
                ->orderBy('level_users.created_at', 'DESC')
                ->first()
                ->pivot
                ->level_id ?? 1;

        $levelAccount = LevelAccount::where('level_id', $senderLevel)
            ->where('status', 1)
            ->where('type', 'charge')
            ->orderBy('created_at', 'DESC')
            ->first();

        //Log::info($senderLevel);
        //Log::info($levelAccount);

        $this->commissionReceiverAccount = UserAccount::with('user')->where('id', $levelAccount->user_account_id)->first();
        $this->commissionUser = $this->commissionReceiverAccount->user;
        $this->commissionReceiverAccountBalance = AccountBalance::where('user_account_id', $this->commissionReceiverAccount->id)->lockForUpdate()->first();

    }

    private function applyCommission()
    {
        if ($this->commission != null) {
            $this->creditableList->addItem(new CreditableAccount(
                "Received " . $this->currency->name . " {$this->commissionAmount} from {$this->getSenderAccount()->account_no}.",
                $this->getCommissionReceiverAccountBalance(),
                $this->commissionAmount,
                $this->getCommissionReceiverAccount()->user->id
            ));

            $this->debitableList->addItem(new DebitableAccount(
                $this->sender->id,
                "Transferred " . $this->currency->name . " {$this->commissionAmount} to {$this->getcommissionReceiverAccount()->account_no}.",
                $this->commissionAmount
            ));

        }
    }
}

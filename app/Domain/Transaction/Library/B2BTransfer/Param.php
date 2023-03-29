<?php

namespace App\Domain\Transaction\Library\B2BTransfer;

use App\Constant\TransactionType as ConstTrxType;
use App\Domain\Accounting\Models\AccountBalance;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Finance\Models\Commission;
use App\Domain\Transaction\Library\CommissionAccount;
use App\Domain\UserRelation\Models\User;
use App\Traits\CommissionTrait;
use Illuminate\Http\Request;

class Param
{
    use CommissionTrait;

    private User $sender;
    private UserAccount $senderAccount;
    private AccountBalance $senderBalanceAccountWithLock;
    private User $receiver;
    private UserAccount $receiverAccount;
    private AccountBalance $receiverBalanceAccountWithLock;
    private ?Commission $commission;
    private CommissionAccount $commissionAccount;
    private Request $request;
    private int $currencyId;
    private string $currencyText;
    private float $amount;
    private float $chargeAmount;
    private int $transactionTypeId;

    public function __construct(
        User $sender,
        UserAccount $senderAccount,
        AccountBalance $senderBalanceAccountWithLock,
        User $receiver,
        UserAccount $receiverAccount,
        AccountBalance $receiverBalanceAccountWithLock,
        ?Commission $commission,
        Request $request,
        int $currencyId,
        string $currencyText,
        float $amount
    ) {
        $this->sender = $sender;
        $this->senderAccount = $senderAccount;
        $this->senderBalanceAccountWithLock = $senderBalanceAccountWithLock;
        $this->receiver = $receiver;
        $this->receiverAccount = $receiverAccount;
        $this->receiverBalanceAccountWithLock = $receiverBalanceAccountWithLock;
        $this->commission = $commission;
        $this->commissionAccount = new CommissionAccount($sender);
        $this->request = $request;
        $this->currencyId = $currencyId;
        $this->currencyText = $currencyText;
        $this->amount = $amount;
        $this->chargeAmount = $this->commission != null ? $this->getCommissionAmount($commission, $amount) : 0;
        $this->transactionTypeId = ConstTrxType::B2B_TRANSFER;
    }

    public function getSender()
    {
        return $this->sender;
    }

    public function getSenderAccount()
    {
        return $this->senderAccount;
    }

    public function getSenderBalanceAccountWithLock()
    {
        return $this->senderBalanceAccountWithLock;
    }

    public function getReceiver()
    {
        return $this->receiver;
    }

    public function getReceiverAccount()
    {
        return $this->receiverAccount;
    }

    public function getReceiverBalanceAccountWithLock()
    {
        return $this->receiverBalanceAccountWithLock;
    }

    public function getCommission()
    {
        return $this->commission;
    }

    public function getCommissionAccount()
    {
        return $this->commissionAccount;
    }

    public function getRequest()
    {
        return $this->request;
    }
    
    public function getCurrencyId()
    {
        return $this->currencyId;
    }

    public function getCurrencyText()
    {
        return $this->currencyText;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getChargeAmount()
    {
        return $this->chargeAmount;
    }

    public function getTransactionTypeId()
    {
        return $this->transactionTypeId;
    }

    public function getDebitDescription($receiverAccount, $amount) {
        return "Transferred " . $this->currencyText . " {$amount} to {$receiverAccount->account_no}.";
    }

    public function getCreditDescription($senderAccount, $amount) {
        return "Received " . $this->currencyText . " {$amount} from {$senderAccount->account_no}.";
    }
}
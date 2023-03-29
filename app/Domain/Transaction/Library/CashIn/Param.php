<?php


namespace App\Domain\Transaction\Library\CashIn;

use App\Domain\Accounting\Models\AccountBalance;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Finance\Models\Commission;
use App\Domain\Independent\Models\Currency;
use App\Domain\Transaction\Library\TraitParam;
use App\Domain\Transaction\Models\TransactionType;
use App\Domain\Transaction\Models\TransactionTypeText;
use App\Domain\Transaction\Utility\UserAccountType;
use App\Domain\UserRelation\Models\User;
use Illuminate\Http\Request;
use App\Domain\Transaction\Library\DebitableAccountList;
use App\Domain\Transaction\Library\CreditableAccountList;
use App\Domain\Transaction\Library\CreditableAccount;
use App\Domain\Transaction\Library\DebitableAccount;

class Param
{
    use TraitParam;

    public function __construct(
        User $sender,
        User $receiver,
        float $amount,
        Currency $currency,
        Request $request,
        Commission $commission = null
    ){
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->request = $request;

        $this->debitableList = new DebitableAccountList();
        $this->creditableList = new CreditableAccountList();

        $this->commission = $commission;

        $this->commissionInitialization();
        $this->prepareTransactions();
    }

    private function prepareTransactions()
    {
        $this->creditableList->addItem(new CreditableAccount(
            "Received ".$this->currency->name." {$this->amount} from {$this->getSenderAccount()->account_no}.",
            $this->getReceiverAccountBalance(),
            $this->amount,
            $this->getReceiverAccount()->user->id
        ));

        $this->debitableList->addItem(new DebitableAccount(
            $this->sender->id,
            "Transferred ".$this->currency->name." {$this->amount} to {$this->getReceiverAccount()->account_no}.",
            $this->amount
        ));
        $this->applyCommission();
        $this->applyCharges();
    }

    private function applyCharges()
    {

    }

    public function getSenderAccount(): UserAccount
    {
        $this->senderAccount = $this->sender
            ->accounts()
            ->where(
                'user_account_type_id',
                '=',
                UserAccountType::FASTPAY_SAVINGS_ACCOUNT
            )->first();

        return $this->senderAccount;
    }

    public function getReceiverAccount(): UserAccount
    {
        $this->receiverAccount = $this->receiver
            ->accounts()
            ->where(
                'user_account_type_id',
                '=',
                UserAccountType::FASTPAY_SAVINGS_ACCOUNT
            )
            ->first();
        return $this->receiverAccount;
    }

    public function getTransactionType(): TransactionType
    {
        return TransactionType::whereName(TransactionTypeText::CASH_IN)->first();
    }
}

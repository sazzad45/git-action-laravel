<?php


namespace App\Domain\Transaction\Library\B2BTransfer;

use App\Domain\Wallet\Library\Capacity\ReceiverAccountCapacityChecker;
use App\Domain\Wallet\Library\Capacity\ReceiverAccountCapacityCheckerParam;
use App\Domain\Wallet\Models\Limit\LimitChecker;
use App\Domain\Wallet\Models\Limit\LimitCheckerParam;
use App\Traits\ValidationHelperTrait;

class B2BTransferRequestValidation
{
    use ValidationHelperTrait;

    private $sender;
    private $receiver;
    private $amount;
    private $chargeAmount;
    private $currency;
    private $transactionTypeId;

    /**
     * SendMoneyRequestValidation constructor.
     * @param $sender
     * @param $receiver
     * @param int $amount
     * @param string $currency
     * @param int $transactionTypeId
     */
    public function __construct($sender, $receiver, float $amount, float $chargeAmount, string $currency, int $transactionTypeId)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->amount = $amount;
        $this->chargeAmount = $chargeAmount;
        $this->currency = $currency;
        $this->transactionTypeId = $transactionTypeId;
    }

    public function validate()
    {
        if ($error = $this->senderParentIsMainAgent($this->sender))
            return $error;

        if ($error = $this->isSenderAgent($this->sender))
            return $error;

        if ($error = $this->isReceiverAgent($this->receiver))
            return $error;

        if ($error = $this->senderIsNotReceiver($this->sender, $this->receiver))
            return $error;

        if ($error = $this->senderAndReceiverHaveSameParent($this->sender, $this->receiver))
            return $error;

        if ($error = $this->senderAndReceiverFromSameCity($this->sender, $this->receiver))
            return $error;

        if ($error = $this->isSenderAccountActive($this->sender))
            return $error;

        if ($error = $this->isReceiverAccountActive($this->receiver))
            return $error;

        if ($error = $this->isSenderKycVerified($this->sender))
            return $error;

        if ($error = $this->hasSufficientBalanceInSavingsAccount($this->sender, $this->receiver, $this->currency, $this->amount + $this->chargeAmount, $this->transactionTypeId))
            return $error;

        if ($error = (new LimitChecker(new LimitCheckerParam($this->sender, $this->amount, $this->transactionTypeId)))->check()->limitCrossed())
            return $error;

        if ($error = (new ReceiverAccountCapacityChecker(new ReceiverAccountCapacityCheckerParam($this->receiver, $this->amount)))->check()->limitCrossed())
            return $error;

        return false;
    }
}

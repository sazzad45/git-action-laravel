<?php


namespace App\Validation;


use App\Traits\ValidationHelperTrait;

class SendMoneyRequestValidation
{
    use ValidationHelperTrait;

    private $sender;
    private $receiver;
    private $amount;
    private $currency;

    /**
     * SendMoneyRequestValidation constructor.
     * @param $sender
     * @param $receiver
     * @param int $amount
     * @param string $currency
     */
    public function __construct($sender, $receiver, int $amount, string $currency)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function validate()
    {
        if ($error = $this->isSenderPersonal($this->sender))
            return $error;

        if ($error = $this->isReceiverPersonal($this->receiver))
            return $error;

        if ($error = $this->isSenderAccountActive($this->sender))
            return $error;

        if ($error = $this->isReceiverAccountActive($this->receiver))
            return $error;

        if ($error = $this->isSenderKycVerified($this->sender))
            return $error;

        if ($error = $this->isReceiverKycVerified($this->receiver))
            return $error;

        if ($error = $this->hasSufficientBalanceInSavingsAccount($this->sender, $this->currency, $this->amount))
            return $error;

        return false;

    }
}

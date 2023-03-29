<?php


namespace App\Domain\Transaction\Library\BundlePurchase;

use App\Domain\UserRelation\Models\User;
use App\Traits\ValidationHelperTrait;

class BundlePurchaseRequestValidation
{
    use ValidationHelperTrait;

    private User $receiver;
    private User $sender;
    private float $amount;
    private BundleCard $card;
    private string $currency;

    public function __construct(User $sender, User $receiver, int $amount, BundleCard $card, string $currency)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->amount = $amount;
        $this->card = $card;
        $this->currency = $currency;
    }

    public function validate()
    {
//        if($error = $this->isCardValid($this->card->status))
//            return $error;

        if ($error = $this->isSenderOfType($this->sender,"Agent"))
            return $error;

//        if ($error = $this->isSenderAccountActive($this->sender))
//            return $error;

//        if ($error = $this->isSenderKycVerified($this->sender))
//            return $error;

        if ($error = $this->hasSufficientBalanceInSavingsAccount($this->sender, $this->receiver, $this->currency, $this->amount))
            return $error;

        return false;

    }
}

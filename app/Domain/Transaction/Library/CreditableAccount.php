<?php


namespace App\Domain\Transaction\Library;

use App\Domain\Accounting\Models\AccountBalance;

class CreditableAccount
{
    public string $description;
    public AccountBalance $accountBalance;
    public float $amount;
    public int $user_id;

    public function __construct(string $description, AccountBalance $accountBalance, float $amount, int $user_id)
    {
        $this->description = $description;
        $this->accountBalance = $accountBalance;
        $this->amount = $amount;
        $this->user_id = $user_id;
    }
}
